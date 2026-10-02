"""Installed outside the checkout; poll the validated branch from private cron."""
import argparse
import json
import os
from pathlib import Path
import subprocess
import sys
from datetime import datetime, timezone


def atomic_json(path, data):
    path = Path(path)
    path.parent.mkdir(parents=True, exist_ok=True)
    temporary = path.with_suffix('.tmp')
    temporary.write_text(json.dumps(data, ensure_ascii=False) + '\n', encoding='utf-8')
    os.replace(temporary, path)


def finish(result, pending, state, status, revision):
    # Preserve verified writes even if the subsequent public HTTP check fails.
    if pending.exists():
        os.replace(pending, state)
    if result:
        raise RuntimeError('Deployment failed; inspect private deployment log')
    verified = json.loads(state.read_text(encoding='utf-8'))
    if verified['source_commit'] != revision:
        raise RuntimeError('Requested revision was superseded; no success report')
    atomic_json(status, {'source_commit': revision, 'status': 'verified',
                         'verified_at': datetime.now(timezone.utc).isoformat()})
    os.chmod(status, 0o644)


def main():
    import fcntl
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--home', required=True, type=Path)
    args = parser.parse_args()
    home = args.home.resolve()
    lock = (home / 'deploy.lock').open('a')
    try:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
    except BlockingIOError:
        return
    config = json.loads((home / 'config.json').read_text(encoding='utf-8'))
    checkout = home / 'checkout'
    def git(*arguments):
        return subprocess.check_output(['git', *arguments], cwd=checkout,
                                       text=True, timeout=90).strip()
    git('fetch', '--quiet', 'origin', '+refs/heads/main:refs/remotes/origin/main',
        '+refs/heads/production-ready:refs/remotes/origin/production-ready')
    revision = git('rev-parse', 'origin/production-ready')
    if revision != git('rev-parse', 'origin/main'):
        return
    state = home / 'production.json'
    status = Path(config['status_path'])
    if status.exists() and state.exists():
        report = json.loads(status.read_text(encoding='utf-8'))
        baseline = json.loads(state.read_text(encoding='utf-8'))
        if report.get('source_commit') == baseline.get('source_commit') == revision:
            return
    print(datetime.now(timezone.utc).isoformat(), 'Deploying', revision, flush=True)
    git('checkout', '--quiet', '--force', '--detach', revision)
    env = dict(os.environ, **config['wordpress'], GITHUB_REF='refs/heads/main')
    env.pop('WP_APP_PASSWORD', None)
    subprocess.run([sys.executable, 'scripts/site_sync.py', 'validate'], cwd=checkout,
                   env=env, check=True, timeout=120)
    sys.path.insert(0, str(checkout / 'scripts'))
    import site_sync as sync
    from auto_deploy import use_baseline
    manifest = use_baseline(sync.validate(), sync.read_json(state))
    sync.write_json(sync.MANIFEST, manifest)
    keys = [entry['key'] for entry, _ in sync.changes(manifest)]
    if keys:
        scoped = [argument for key in keys for argument in ('--only', key)]
        subprocess.run([sys.executable, 'scripts/site_sync.py', 'plan', *scoped],
                       cwd=checkout, env=env, check=True, timeout=600)
    pending = home / 'verified-pending.json'
    if pending.exists():
        raise RuntimeError('Unfinished verified state needs operator review')
    try:
        result = subprocess.run([sys.executable, 'scripts/auto_deploy.py',
                                 '--state', str(state), '--output', str(pending),
                                 '--commit', revision], cwd=checkout, env=env, timeout=1200)
    except subprocess.TimeoutExpired:
        finish(1, pending, state, status, revision)
        raise
    finish(result.returncode, pending, state, status, revision)
    print('Production verified:', revision, flush=True)


if __name__ == '__main__':
    main()
