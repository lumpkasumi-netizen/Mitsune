import json, tempfile, unittest
from pathlib import Path
from unittest.mock import patch
from scripts import site_sync as sync

class FakeClient:
    def __init__(self,data,fail=None):self.data=dict(data);self.writes=[];self.fail=fail
    def current(self,e):return self.data[e['key']]
    def update(self,e,text):
        self.writes.append(e['key']);self.data[e['key']]=text
        if self.fail==e['key']:
            self.fail=None
            raise RuntimeError('Connection dropped after write')

class SyncTests(unittest.TestCase):
    def entries(self):return [{'key':'posts/'+str(i),'kind':'posts','sha256':sync.digest('before'+str(i))} for i in (1,2)]
    def test_normalized_newlines(self):self.assertEqual(sync.digest('a\r\nb'),sync.digest('a\nb'))
    def test_path_escape_rejected(self):
        with self.assertRaises(ValueError):sync.safe_path('../credentials.json')
    def test_all_drift_checked_before_writes(self):
        entries=self.entries();client=FakeClient({'posts/1':'before1','posts/2':'other editor'})
        with self.assertRaises(RuntimeError):sync.deploy(client,{},[(e,'new') for e in entries])
        self.assertEqual(client.writes,[])
    def test_partial_failure_rolls_back_even_ambiguous_write(self):
        entries=self.entries();client=FakeClient({'posts/1':'before1','posts/2':'before2'},fail='posts/2')
        with tempfile.TemporaryDirectory() as tmp,patch.object(sync,'ROOT',Path(tmp)):
            with self.assertRaises(RuntimeError):sync.deploy(client,{},[(e,'new') for e in entries])
            self.assertEqual(client.data,{'posts/1':'before1','posts/2':'before2'})
            receipt=json.loads(next(Path(tmp).rglob('receipt.json')).read_text())
            self.assertEqual(receipt['state'],'rolled_back')
    def test_success_updates_baseline_and_receipt(self):
        entries=self.entries();m={'entries':entries};client=FakeClient({'posts/1':'before1','posts/2':'before2'})
        with tempfile.TemporaryDirectory() as tmp,patch.object(sync,'ROOT',Path(tmp)),patch.object(sync,'MANIFEST',Path(tmp)/'manifest.json'):
            sync.deploy(client,m,[(e,'new') for e in entries])
            self.assertTrue(all(e['sha256']==sync.digest('new') for e in entries))
            receipt=json.loads(next(Path(tmp).rglob('receipt.json')).read_text())
            self.assertEqual(receipt['items'][0]['entry']['sha256'],sync.digest('before1'))
    def test_repository_sources_are_valid(self):self.assertGreater(len(sync.validate()['entries']),0)

    def test_concurrent_edit_is_not_rolled_back(self):
        entries=self.entries()
        class OtherEditor(FakeClient):
            def update(self,e,text):
                super().update(e,text)
                if e['key']=='posts/2':
                    self.data[e['key']]='other editor'
                    raise RuntimeError('Someone else changed the post')
        client=OtherEditor({'posts/1':'before1','posts/2':'before2'})
        with tempfile.TemporaryDirectory() as tmp,patch.object(sync,'ROOT',Path(tmp)):
            with self.assertRaises(RuntimeError):sync.deploy(client,{},[(e,'new') for e in entries])
            self.assertEqual(client.data['posts/2'],'other editor')
            self.assertEqual(client.data['posts/1'],'before1')
            receipt=json.loads(next(Path(tmp).rglob('receipt.json')).read_text())
            self.assertEqual(receipt['state'],'rollback_needs_review')

if __name__=='__main__':unittest.main()
