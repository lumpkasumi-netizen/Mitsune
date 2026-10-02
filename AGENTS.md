# AI / contributor instructions

## Scope

This repository manages the existing production WordPress site https://mitsune-ai.com/.
Read README.md, docs/EDITORIAL.md and docs/OPERATIONS.md before changes.
Public brand spelling is **Mitsune**. User-facing prose is Japanese.

## Canonical files

- Edit `site/posts`, `site/pages`, or `site/theme`. Historical files outside this allowlisted project are not source of truth and must not be committed.
- Preserve existing vocabulary, copy payloads, images, anchors, scripts, tracking and advertising unless the request calls for changing them. Never regenerate an entire article from an old batch script.
- Metadata JSON supports title/excerpt only. IDs, slugs, publish status, categories and images are outside the deployment tool's editable metadata. New posts/media need a separate reviewed workflow.
- `site/manifest.json` hashes represent the last verified production state. Do not edit them to silence drift checks. Deployment updates them after successful read-back.
- No credentials, cookies, .env, OAuth JSON, unpublished content, analytics exports, database or private notes in Git. Never bypass ignore rules with `git add -f`.

## Workflow

1. Pull latest main; work on a branch. Inspect the relevant current source before edits.
2. Make scoped changes. Preserve unrelated work and existing IDs.
3. Run `python scripts/site_sync.py validate` and `python -m unittest discover -s tests_portable -v`. PHP edits require `php -l` on each edited file. Test copy/search/navigation when affected. Check PC and mobile UI for layout edits.
4. Explain what changed, why, and how verified in a PR. Do not claim SEO gains immediately after publication or generation verification for untested prompts.
5. Pushing or merging is not deployment. Only deploy when authorized and credentials are supplied in the deployment environment. Do not request passwords in chat or commit them.
6. Use `plan --only KEY`, then `deploy --only KEY --apply`. Resolve live drift instead of forcing an overwrite. After publication verify public pages, clear the site's cache if needed, and commit the updated manifest.

Do not enable automatic deployment, change account permissions, disable ads, replace analytics tags, or change DNS merely to complete routine content work.
