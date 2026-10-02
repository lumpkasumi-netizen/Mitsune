"""Check tracked path boundaries and common credential signatures without printing values."""
from pathlib import Path
import re, subprocess
ROOT=Path(__file__).resolve().parents[1]
def check(paths):
    bad=[]
    for name in paths:
        p=Path(name)
        if name not in ('.gitignore','.gitattributes','README.md','AGENTS.md') and p.parts[0] not in ('site','scripts','docs','.github','tests_portable'):
            bad.append(name);continue
        if any(x in name.lower() for x in ('.env','token.json','secret.json','.sqlite','.deploy','__pycache__')):bad.append(name);continue
        data=(ROOT/name).read_text(encoding='utf-8')
        signatures=[r'-----BEGIN [A-Z ]*PRIVATE KEY-----',r'gh[pousr]_[A-Za-z0-9]{30,}',r'github_pat_[A-Za-z0-9_]{40,}',r'AIza[0-9A-Za-z_-]{30,}']
        if any(re.search(pattern,data) for pattern in signatures):bad.append(name)
    if bad:raise ValueError('Review prohibited files or credential patterns: '+', '.join(bad))
if __name__=='__main__':
    names=subprocess.check_output(['git','-c','safe.directory='+str(ROOT).replace('\\','/'),'ls-files','-z'],cwd=ROOT).decode().split('\0')
    check([n for n in names if n]);print('Tracked file checks passed')
