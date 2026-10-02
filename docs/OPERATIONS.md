# 更新・公開・復旧

**2026-10-02からmainへの変更は自動公開です。** 通常は [自動公開の運用](AUTO_DEPLOY.md) に従ってください。以下のローカル公開は例外時の手順です。現在の公開基準は サーバーの `~/.mitsune-deploy/production.json` にあるため、ローカルmanifestの古い基準だけでデプロイしないでください。

## 認証

GitHub権限だけで編集・PR作成できます。本番更新には別途WordPress権限が必要です。
運用環境の秘密情報ストアまたはセッション環境変数で `WP_USER` と `WP_PASSWORD` を供給します。スクリプトは.envを自動読込せず、値を出力しません。パスワードをコマンド引数・README・PR・チャットへ貼らないでください。

記事/固定ページのみの更新は `WP_USER` と `WP_APP_PASSWORD`（WordPressアプリケーションパスワード）でも対応します。テーマ編集と全体exportには管理画面のCookie認証が必要なのでWP_PASSWORDを使用し、WP_APP_PASSWORDは設定しません。新しい資格情報の作成やアクセス付与は利用者が管理します。

初期導入時のexportは既存の管理者接続で確認済み。新しいPCやAIの接続、アプリケーションパスワード方式、本番書き込みは別環境での動作保証ではなく、planで到達・権限を確認してください。

## 編集 → 公開

```sh
python -m pip install -r scripts/requirements.txt
python scripts/site_sync.py validate
python scripts/site_sync.py status
python -m unittest discover -s tests_portable -v
python scripts/site_sync.py plan --only posts/42
python scripts/site_sync.py deploy --only posts/42 --apply
```

`--only` は複数指定できます。省略すると基準ハッシュから変わった全対象を選択します。`deploy` も `--apply` がなければ読み取り専用です。通常は対象を明示してください。`plan` は本文差分を標準出力するので、公開原稿だけで運用します。

テーマ例：`--only theme/style.css`。PHP変更時は公開前にPHP CLIの構文検査を必須とし、PHPがない環境では停止します。テーマのインストール、新規テーマファイル作成、プラグイン/DB更新には対応しません。既存テーマエディタが利用できることが前提です。

公開前に全対象の現在値をmanifestと照合します。対象ごとに書込み直前にも照合し、更新後に取得して一致を確認します。WordPressに原子的なcompare-and-swapはないため、公開中は管理画面や別AIから同じ対象を編集しないでください。

バックアップと実行結果はローカル `.deploy/<UTC時刻>/receipt.json` に保存されます（Git対象外）。成功後は `site/manifest.json` のハッシュが更新されます。公開URLの本文・description・canonical・リンク・コピー・検索を検証し、必要なら管理画面からキャッシュを削除して再確認。自動公開の最新基準はサーバーのproduction.jsonです。例外の手動公開後もこの基準との整合を確認してからcronを再開します。

## 本番との競合

`Live drift` は本番が基準から変わっている状態です。ハッシュを手で変更したり、forceで上書きしないでください。

```sh
python scripts/site_sync.py export --destination .deploy/live-review
```

出力先は存在しないディレクトリを指定します。取得した現行ソースとGitの差分を比較し、本番変更を先にブランチへ取り込んでから自分の修正を再適用します。manifestは同じexportで得た内容と対にして扱い、取り込み内容をレビューします。

## 復旧

書込み途中で失敗した場合、ツールは書き込んだ内容がまだそのまま残っている対象だけを変更前へ戻します。別の変更を検出した場合やネットワーク障害時は復元せず、receiptの `rollback_needs_review` と `conflicts` を確認します。失敗時はmanifestを更新しません。全変更の原子的適用を保証するものではありません。

正常公開後の取り消しはGitで該当する本文/テーマ変更をrevertし、**最新の公開基準manifestは維持して** plan → deployを実行します。manifestまで過去へ戻すと競合検査に失敗します。

テーマの致命的エラーで管理画面に入れない場合は、ホスティング管理画面やSFTP等でreceiptの変更前テーマを復元します。サーバー全体のバックアップは別途必要です。

## 範囲外

新規記事作成、削除、非公開化、slug変更、分類変更、画像アップロード、メディア本体の同期、プラグイン設定、広告管理には対応しません。必要なら別の変更として実装・レビューします。本番認証情報はサーバーの非公開領域で管理します。
