import json
from pathlib import Path
import tempfile
import unittest
from scripts.server_poll import atomic_json, finish


class ServerStateTests(unittest.TestCase):
    def test_failed_public_check_preserves_verified_baseline_without_success(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            state, pending, status = [root / n for n in ('state.json', 'pending.json', 'status.json')]
            atomic_json(state, {'source_commit': 'old'})
            atomic_json(pending, {'source_commit': 'new'})
            with self.assertRaises(RuntimeError):
                finish(1, pending, state, status, 'new')
            self.assertEqual(json.loads(state.read_text())['source_commit'], 'new')
            self.assertFalse(status.exists())

    def test_superseded_run_cannot_report_success(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            atomic_json(root / 'state', {'source_commit': 'old'})
            with self.assertRaises(RuntimeError):
                finish(0, root / 'pending', root / 'state', root / 'status', 'new')
            self.assertFalse((root / 'status').exists())

    def test_success_report_contains_no_manifest_or_credentials(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            atomic_json(root / 'pending', {'source_commit': 'new', 'manifest': {'private': 'state'}})
            finish(0, root / 'pending', root / 'state', root / 'status', 'new')
            report = json.loads((root / 'status').read_text())
            self.assertEqual(set(report), {'source_commit', 'status', 'verified_at'})
            self.assertEqual(report['status'], 'verified')
