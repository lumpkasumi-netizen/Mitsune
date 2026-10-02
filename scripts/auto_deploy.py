"""Serialized GitHub production deployment using a separately persisted baseline."""
import argparse
import copy
import json
import os
from pathlib import Path
import subprocess
from datetime import datetime, timezone
try:
    from . import site_sync as sync
except ImportError:
    import site_sync as sync

def git(*args):
    return subprocess.check_output(['git',*args],text=True,cwd=sync.ROOT).strip()

def use_baseline(manifest,state):
    baseline=state['manifest']
    for field in ('schema','site_url','theme'):
        if manifest[field]!=baseline[field]:raise ValueError('State identity mismatch')
    current={e['key']:e for e in manifest['entries']}
    previous={e['key']:e for e in baseline['entries']}
    if len(previous)!=len(baseline['entries']) or current.keys()!=previous.keys():
        raise ValueError('Registry changed; new/deleted targets need a reviewed migration')
    result=copy.deepcopy(manifest)
    for entry in result['entries']:
        old=previous[entry['key']]
        if {k:v for k,v in entry.items() if k!='sha256'}!={k:v for k,v in old.items() if k!='sha256'}:
            raise ValueError('Target mapping changed: '+entry['key'])
        entry['sha256']=old['sha256']
    return result

def verify_public(client,items):
    import requests
    urls={sync.BASE+'/'}
    for e,_ in items:
        if e['kind']!='theme':
            post=client.post(e['kind'],e['id'])
            urls.add(post['link'])
    for url in sorted(urls):
        # No authenticated cookies on public smoke checks.
        r=requests.get(url,timeout=60)
        r.raise_for_status()
        if not r.content or '<html' not in r.text.lower():raise RuntimeError('Public HTML missing')
        print('Public HTTP check:',url,r.status_code)

def purge_cache(client):
    import html
    import re
    urls=sorted(set(re.findall(r'https://mitsune-ai\.com/wp-admin/[^"\x27]*action=delcachepage[^"\x27]*',client.admin)))
    if urls:
        client.s.get(html.unescape(urls[0]),timeout=60).raise_for_status()
        print('WordPress page cache purge requested.')
    else:
        print('No supported cache purge link; public HTTP checks still run.')

def main():
    p=argparse.ArgumentParser(description=__doc__)
    p.add_argument('--state',type=Path,required=True)
    p.add_argument('--output',type=Path,required=True)
    p.add_argument('--commit',required=True)
    args=p.parse_args()
    if os.environ.get('GITHUB_REF')!='refs/heads/main':raise RuntimeError('Automatic deployment is main-only')
    if git('rev-parse','HEAD')!=args.commit:raise RuntimeError('Checkout does not match requested commit')
    latest=git('ls-remote','origin','refs/heads/main').split()[0]
    if latest!=args.commit:
        print('Superseded main revision; newer run will deploy.');return
    state=sync.read_json(args.state)
    subprocess.run(['git','merge-base','--is-ancestor',state['source_commit'],args.commit],cwd=sync.ROOT,check=True)
    manifest=use_baseline(sync.validate(),state)
    items=sync.changes(manifest)
    client=sync.Client()
    if not items:
        # A green initial run proves authentication and all baseline fingerprints.
        sync.preflight(client,[(e,sync.local_text(e)) for e in manifest['entries']])
        print('All production fingerprints match; no writes necessary.')
    else:
        print('Deploying:',', '.join(e['key'] for e,_ in items))
        sync.deploy(client,manifest,items)
    # Persist verified writes even if a public cache/HTTP smoke check fails later.
    sync.write_json(args.output,{'source_commit':args.commit,'verified_at':datetime.now(timezone.utc).isoformat(),'manifest':manifest})
    if items:purge_cache(client)
    verify_public(client,items)
    print('Production source read-back and public HTTP checks passed.')

if __name__=='__main__':main()
