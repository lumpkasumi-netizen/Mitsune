# AI / contributor instructions

## Scope

This repository manages the existing production WordPress site https://mitsune-ai.com/.
Read README.md, docs/EDITORIAL.md and docs/OPERATIONS.md before changes.
Public brand spelling is **Mitsune**. User-facing prose is Japanese.

## Canonical files

- Edit `site/posts`, `site/pages`, or `site/theme`. Historical files outside this allowlisted project are not source of truth and must not be committed.
- Preserve existing vocabulary, copy payloads, images, anchors, scripts, tracking and advertising unless the request calls for changing them. Never regenerate an entire article from an old batch script.
- Metadata JSON supports title/excerpt only. IDs, slugs, publish status, categories and images are outside the deployment tool's editable metadata. New posts/media need a separate reviewed workflow.
- `site/manifest.json` defines target mappings and the initial/manual baseline. Automatic deployment takes the current verified hashes from `production-state:production.json`. Do not edit hashes to silence drift checks or change that state branch by hand.
- No credentials, cookies, .env, OAuth JSON, unpublished content, analytics exports, database or private notes in Git. Never bypass ignore rules with `git add -f`.

## Workflow

1. Pull latest main; work on a branch. Inspect the relevant current source before edits.
2. Make scoped changes. Preserve unrelated work and existing IDs.
3. Run `python scripts/site_sync.py validate` and `python -m unittest discover -s tests_portable -v`. PHP edits require `php -l` on each edited file. Test copy/search/navigation when affected. Check PC and mobile UI for layout edits.
4. Explain what changed, why, and how verified in a PR. Do not claim SEO gains immediately after publication or generation verification for untested prompts.
5. Main pushes/merges automatically deploy production after validation. Do not merge or push main unless publication is authorized. Feature branches/PRs only validate. See docs/AUTO_DEPLOY.md. Never request passwords in chat or commit them.
6. The production workflow serializes writes and records verified state in production-state. Resolve live drift instead of forcing an overwrite. Avoid local deployment while automatic jobs are running. For exceptional manual work, first import the current durable baseline and follow docs/OPERATIONS.md.

Automatic deployment was explicitly authorized by the owner on 2026-10-02. Do not broaden deployment access, disable ads, replace analytics tags, or change DNS merely to complete routine content work.
