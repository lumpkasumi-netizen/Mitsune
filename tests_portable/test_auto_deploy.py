import copy
import unittest
from scripts.auto_deploy import use_baseline

class BaselineTests(unittest.TestCase):
    def setUp(self):
        self.manifest={'schema':1,'site_url':'https://mitsune-ai.com','theme':'mitsune','entries':[{'key':'posts/42','kind':'posts','id':42,'path':'site/posts/a.json','body':'site/posts/a.html','sha256':'old'}]}
        self.state={'source_commit':'abc','manifest':copy.deepcopy(self.manifest)}
        self.state['manifest']['entries'][0]['sha256']='deployed'
    def test_uses_durable_deployed_hash_without_mutating_source(self):
        actual=use_baseline(self.manifest,self.state)
        self.assertEqual(actual['entries'][0]['sha256'],'deployed')
        self.assertEqual(self.manifest['entries'][0]['sha256'],'old')
    def test_rejects_changed_target(self):
        self.manifest['entries'][0]['id']=99
        with self.assertRaises(ValueError):use_baseline(self.manifest,self.state)
    def test_rejects_missing_target(self):
        self.manifest['entries']=[]
        with self.assertRaises(ValueError):use_baseline(self.manifest,self.state)
    def test_rejects_different_site(self):
        self.manifest['site_url']='https://example.com'
        with self.assertRaises(ValueError):use_baseline(self.manifest,self.state)

if __name__=='__main__':unittest.main()
