"""Portable Mitsune sync: offline validation, read-only plan, explicit deployment."""
import argparse, hashlib, html, json, os, re, shutil, subprocess
from pathlib import Path
from datetime import datetime, timezone

ROOT=Path(__file__).resolve().parents[1]
SITE=ROOT/'site'
BASE='https://mitsune-ai.com'
THEME='mitsune'
MANIFEST=SITE/'manifest.json'

def normalize(s): return s.replace('\r\n','\n').replace('\r','\n')
def digest(s): return hashlib.sha256(normalize(s).encode('utf-8')).hexdigest()
def write_json(path,data):
    path.parent.mkdir(parents=True,exist_ok=True)
    path.write_text(json.dumps(data,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
def read_json(path): return json.loads(path.read_text(encoding='utf-8'))
def safe_path(relative):
    p=(ROOT/relative).resolve()
    if not p.is_relative_to(SITE.resolve()) or p==SITE.resolve(): raise ValueError('Path outside site/')
    return p
def source_post(p): return {k:p[k]['raw'] for k in ('title','content','excerpt')}
def serialize_post(p): return json.dumps(source_post(p),ensure_ascii=False,sort_keys=True)

class Client:
    def __init__(self):
        import requests
        self.s=requests.Session();self.admin=''
        user=os.environ['WP_USER']
        if os.environ.get('WP_APP_PASSWORD'):
            self.s.auth=(user,os.environ['WP_APP_PASSWORD'])
        else:
            self.s.get(BASE+'/wp-login.php',timeout=40).raise_for_status()
            self.s.post(BASE+'/wp-login.php',data={'log':user,'pwd':os.environ['WP_PASSWORD'],'wp-submit':'Log In','redirect_to':BASE+'/wp-admin/','testcookie':'1'},timeout=40).raise_for_status()
            r=self.s.get(BASE+'/wp-admin/',timeout=40);r.raise_for_status()
            nonce=re.search(r'"nonce":"([^"\s]+)"',r.text)
            if 'wp-login.php' in r.url or not nonce: raise RuntimeError('WordPress authentication failed')
            self.admin=r.text;self.s.headers['X-WP-Nonce']=nonce[1]
    def post(self,kind,pid):
        r=self.s.get(f'{BASE}/wp-json/wp/v2/{kind}/{pid}',params={'context':'edit'},timeout=60);r.raise_for_status();return r.json()
    def theme(self,name):
        if not self.admin: raise RuntimeError('Theme editor needs WP_USER / WP_PASSWORD session')
        r=self.s.get(BASE+'/wp-admin/theme-editor.php',params={'file':name,'theme':THEME},timeout=60);r.raise_for_status()
        nonce=re.search(r'name="nonce" value="([^"]+)"',r.text)
        body=re.search(r'<textarea[^>]*name="newcontent"[^>]*>(.*?)</textarea>',r.text,re.S)
        if not nonce or not body: raise RuntimeError('Theme editor unavailable: '+name)
        return nonce[1],normalize(html.unescape(body[1]))
    def current(self,e):
        if e['kind']=='theme': return self.theme(e['name'])[1]
        p=self.post(e['kind'],e['id'])
        if p['status']!='publish' or p['slug']!=e['slug']: raise RuntimeError('Publication status/slug changed')
        return serialize_post(p)
    def update(self,e,text):
        if e['kind']=='theme':
            nonce,_=self.theme(e['name'])
            r=self.s.post(BASE+'/wp-admin/theme-editor.php',data={'nonce':nonce,'_wp_http_referer':'/wp-admin/theme-editor.php?file='+e['name']+'&theme='+THEME,'newcontent':text,'action':'update','file':e['name'],'theme':THEME,'submit':'ファイルを更新'},timeout=60)
        else:
            r=self.s.post(f'{BASE}/wp-json/wp/v2/{e["kind"]}/{e["id"]}',json=json.loads(text),timeout=60)
        r.raise_for_status()
        if digest(self.current(e))!=digest(text): raise RuntimeError('Write verification failed')

def local_text(e):
    if e['kind']=='theme': return safe_path(e['path']).read_text(encoding='utf-8')
    p=read_json(safe_path(e['path']))
    if set(p)!={'title','excerpt'}: raise ValueError('Only title/excerpt belong in metadata')
    p['content']=safe_path(e['body']).read_text(encoding='utf-8')
    return json.dumps(p,ensure_ascii=False,sort_keys=True)

def validate():
    m=read_json(MANIFEST)
    if m['site_url']!=BASE or m['theme']!=THEME: raise ValueError('Unexpected site or theme')
    keys=set()
    for e in m['entries']:
        if e['key'] in keys: raise ValueError('Duplicate key')
        keys.add(e['key'])
        if e['kind'] not in ('theme','posts','pages'): raise ValueError('Unknown kind')
        if e['kind']=='theme':
            name=e['name']
            if not re.fullmatch(r'[\w./-]+\.(php|css|js)',name) or '..' in name.split('/'): raise ValueError('Invalid theme name')
            if e['path']!='site/theme/'+name: raise ValueError('Theme path mismatch')
        text=local_text(e)
        if not text.strip(): raise ValueError('Empty source')
        if e['kind']!='theme':
            p=json.loads(text)
            if any(not isinstance(p[k],str) for k in ('title','content','excerpt')): raise ValueError('Non-text field')
            for block in re.findall(r'<script[^>]*type=[\"\x27]application/ld\+json[\"\x27][^>]*>(.*?)</script>',p['content'],re.S): json.loads(block)
    return m

def changes(m,selected=None):
    if selected and not set(selected)<={e['key'] for e in m['entries']}: raise ValueError('Unknown --only key')
    return [(e,local_text(e)) for e in m['entries'] if (not selected or e['key'] in selected) and digest(local_text(e))!=e['sha256']]

def preflight(client,items):
    before={}
    for e,text in items:
        current=client.current(e)
        if digest(current)!=e['sha256']: raise RuntimeError('Live drift: '+e['key']+'; export elsewhere and reconcile')
        before[e['key']]=current
    return before

def deploy(client,m,items):
    if not items: return
    for e,_ in items:
        if e['kind']=='theme' and e['name'].endswith('.php'):
            if not shutil.which('php'): raise RuntimeError('PHP CLI required to deploy PHP changes')
            subprocess.run(['php','-l',str(safe_path(e['path']))],check=True)
    before=preflight(client,items)
    run=ROOT/'.deploy'/datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%S%fZ')
    record={'site_url':BASE,'state':'prepared','items':[{'entry':dict(e),'before':before[e['key']],'after':t} for e,t in items]}
    write_json(run/'receipt.json',record)
    attempted=[]
    try:
        for e,text in items:
            if digest(client.current(e))!=digest(before[e['key']]): raise RuntimeError('Concurrent edit: '+e['key'])
            attempted.append((e,text));client.update(e,text)
        record['state']='applied'
    except BaseException:
        conflicts=[]
        for e,text in reversed(attempted):
            try:
                current=client.current(e)
                if digest(current)==digest(text): client.update(e,before[e['key']])
                elif digest(current)!=digest(before[e['key']]): conflicts.append(e['key'])
            except Exception: conflicts.append(e['key'])
        record['state']='rollback_needs_review' if conflicts else 'rolled_back';record['conflicts']=conflicts
        write_json(run/'receipt.json',record);raise
    write_json(run/'receipt.json',record)
    for e,text in items: e['sha256']=digest(text)
    m['exported_at']=datetime.now(timezone.utc).isoformat();write_json(MANIFEST,m)
    print('Verified writes. Receipt:',run/'receipt.json')
    print('Check public pages/cache; commit site/manifest.json as deployed baseline.')

def export(client,destination):
    destination=Path(destination)
    if destination.exists(): raise ValueError('Export destination must not exist; preserve local edits')
    destination.mkdir(parents=True)
    m={'schema':1,'site_url':BASE,'theme':THEME,'exported_at':datetime.now(timezone.utc).isoformat(),'entries':[]}
    for kind in ('posts','pages'):
        page=1
        while True:
            r=client.s.get(BASE+'/wp-json/wp/v2/'+kind,params={'context':'edit','status':'publish','per_page':100,'page':page},timeout=60);r.raise_for_status()
            for p in r.json():
                if p.get('password'): continue
                stem=f'{kind}/{p["id"]}-{p["slug"]}'
                write_json(destination/(stem+'.json'),{k:p[k]['raw'] for k in ('title','excerpt')})
                (destination/(stem+'.html')).write_text(normalize(p['content']['raw']),encoding='utf-8')
                m['entries'].append({'key':f'{kind}/{p["id"]}','kind':kind,'id':p['id'],'slug':p['slug'],'path':'site/'+stem+'.json','body':'site/'+stem+'.html','sha256':digest(serialize_post(p))})
            if page>=int(r.headers.get('X-WP-TotalPages',1)): break
            page+=1
    from html.parser import HTMLParser
    from urllib.parse import urlparse,parse_qs
    names=set()
    class Links(HTMLParser):
        def handle_starttag(self,tag,attrs):
            href=dict(attrs).get('href','')
            if tag=='a' and 'theme-editor.php?' in href:
                q=parse_qs(urlparse(html.unescape(href)).query)
                if 'file' in q: names.add(q['file'][0])
    r=client.s.get(BASE+'/wp-admin/theme-editor.php',params={'theme':THEME},timeout=60);r.raise_for_status();Links().feed(r.text)
    if not {'style.css','functions.php','index.php'}<=names: raise RuntimeError('Incomplete theme listing')
    for name in sorted(names):
        if '..' in name.split('/') or name.startswith('/') or not re.fullmatch(r'[\w./-]+\.(php|css|js)',name): raise ValueError('Unexpected theme path')
        _,text=client.theme(name);path=destination/'theme'/name;path.parent.mkdir(parents=True,exist_ok=True);path.write_text(text,encoding='utf-8')
        m['entries'].append({'key':'theme/'+name,'kind':'theme','name':name,'path':'site/theme/'+name,'sha256':digest(text)})
    write_json(destination/'manifest.json',m)
    print('Exported',len(m['entries']),'public-source entries to',destination)

def main():
    p=argparse.ArgumentParser(description=__doc__)
    p.add_argument('command',choices=['validate','status','export','plan','deploy'])
    p.add_argument('--destination',default=str(SITE));p.add_argument('--only',action='append');p.add_argument('--apply',action='store_true')
    args=p.parse_args()
    if args.command=='export': export(Client(),args.destination);return
    m=validate();items=changes(m,args.only)
    print('Valid entries:',len(m['entries']));print('Changed:',', '.join(e['key'] for e,_ in items) or 'none')
    if args.command in ('validate','status') or not items: return
    client=Client()
    if args.command=='plan' or not args.apply:
        before=preflight(client,items)
        import difflib
        for e,text in items: print(''.join(difflib.unified_diff(normalize(before[e['key']]).splitlines(True),normalize(text).splitlines(True),fromfile='live/'+e['key'],tofile='local/'+e['key'])))
        print('Read-only plan. No writes made.');return
    deploy(client,m,items)
if __name__=='__main__': main()
