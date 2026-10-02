# GitHubからの自動公開

所有者の依頼により2026-10-02導入。`main` へのpush/PRマージで `Deploy production` が実行されます。mainを選んだ手動のworkflow_dispatchでも再実行できます。記事・固定ページの本文/タイトル/抜粋と、登録済みテーマファイルが対象です。

## 流れ

1. ソース検証・テスト・PHP構文・外部JS構文・追跡ファイル検査。
2. production環境のWP_USER / WP_PASSWORDで認証。秘密情報はこのジョブだけへ渡します。
3. `production-state:production.json` の直近成功ハッシュを使用。記事ID、slug、ファイル対応が勝手に変わった場合は停止。
4. 更新対象すべての本番値を照合。競合がなければ変更分だけ反映し、保存内容を再取得して検証。
5. 検証済み状態をproduction-stateへ記録。ホームと更新記事の公開HTTP応答を確認。

このstateブランチは本番反映記録専用で、mainへマージしません。mainのmanifestは対象一覧として使い、古いハッシュで自動公開を判断しません。レジストリへの新規記事追加等は別途移行手順が必要です。

同時実行は1件。進行中の公開は新しいpushで中断しません。待機中の古い実行が置き換わっても最新main全体と公開基準の差分を反映します。実行開始時にmainが進んでいれば旧実行は書込みをスキップします。公開中にさらにpushされた場合は次の実行が追従します。

## 秘密情報・権限

- GitHub Environment `production` のデプロイブランチルールはbranch `main` のみ。PR/任意ブランチからの認証情報使用は許可しません。
- Secrets: `WP_USER`、`WP_PASSWORD`。GitHubの公開鍵で暗号化して登録。ソース・ログ・成果物には含めません。
- 検証ジョブはcontents:read。公開ジョブのみstate記録のためcontents:writeを使います。GitHubの個人トークンをActionsへ登録しません。
- mainを書き換えられる人/AIは本番も変更できます。PRレビュー運用を守ってください。ブランチ保護の必須レビューはこの導入では追加していません。

## 成功・失敗の確認

Actionsの `Deploy production` とproduction環境の履歴を確認してください。公開データの変更前/変更後と復旧結果は `production-receipts-<run>-<attempt>` 成果物に30日保存します。秘密情報を含まない公開ソースだけが対象です。

途中失敗時は更新内容が自分の書込みのまま残っている対象のみ自動復旧します。競合や通信障害で復旧できない場合は失敗として停止。管理画面が使えないPHP障害はホスティング/SFTPから復旧します。

本番書込み成功後に公開HTTP確認が失敗しても、検証済みハッシュはstateへ記録し、ジョブは失敗のまま残します。stateのpush失敗は自動で無視しません。成果物と本番値を照合し、state記録を復旧するまで次の本番更新を停止してください。

公開HTTPチェックはキャッシュの完全一致や全画面UIテストではありません。レイアウト・コピー・検索の変更はPR検証時に実操作し、公開後も該当箇所を確認してください。

## 取り消しと停止

- 内容を戻す：mainの対象ソース変更をrevertしたPRをマージ。stateのハッシュは現在の本番のままなので、戻す内容との差分が自動反映されます。
- 自動公開の停止：GitHub Actionsで `Deploy production` workflowを無効化。WordPressやSecretsを削除する必要はありません。
- ローカル公開は自動ジョブ停止後に限る。stateのmanifestをローカルへ取り込み、plan/deployを実施し、確認結果をstateにも同期してから自動公開を再開する。

参考：[GitHub Environments](https://docs.github.com/en/actions/how-tos/deploy/configure-and-manage-deployments/manage-environments)、[同時実行の制御](https://docs.github.com/en/actions/how-tos/write-workflows/choose-when-workflows-run/control-workflow-concurrency)。
