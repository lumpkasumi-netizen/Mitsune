# GitHubからの自動公開

mainへのpush・マージを本番公開として扱います。作業ブランチとPRでは検証だけを実行します。

## 動作

1. GitHub Actionsの`Deploy production`が本文検証・単体テスト・PHP/JS構文検査・秘密情報混入チェックを実行。
2. 合格したmainのコミットを`production-ready`ブランチへ進めます。直接このブランチを変更しないでください。
3. ConoHaサーバーのcronが1分ごとに取得。最新mainと一致したコミットのみを処理します。
4. ファイルロックで同時実行を防ぎ、対象別のplan、本番の競合確認、変更分の公開、読み戻し、公開HTTP確認を実施。
5. サーバーが公開する最小限の完了情報をActionsが確認して成功します。通常は検証後数分以内。10分以内に完了確認がなければActionsは失敗します。

WordPressとSSHの国外IP制限は維持します。PCを起動しておく必要はありません。GitHubから本番へSSH接続しません。

## 状態・認証情報

サーバーのホームにある非公開ディレクトリ`~/.mitsune-deploy/`を使用します。

- `config.json`: 既存WordPress認証情報。所有者のみ読み書き可能。Git管理禁止。
- `production.json`: 最後に本番読み戻しで確認した70対象の基準ハッシュとコミット。
- `checkout/.deploy/*/receipt.json`: 変更前後の復旧記録。
- `deploy.log`: 直近の実行記録。大きくなった場合は`deploy.log.previous`へローテーション。
- `server_poll.py`: サーバーに固定配置した起動スクリプト。リポジトリで変更しても自動更新しません。運用者が差分確認後にSSHで更新します。

公開URL`/.well-known/mitsune-deployment.json`にはコミットID・確認時刻・成功状態だけを出力し、認証情報や本文は含めません。成功表示はその時点の検証を示し、その後の手動改変まで保証しません。

旧`production-state`ブランチは移行前の基準を保管するもので、今後は更新されません。最新基準はサーバーの`production.json`です。mainのmanifestのハッシュだけを使って手動公開しないでください。

GitHub上にWordPressパスワードやSSH秘密鍵は不要です。GitHub Actionsは同じリポジトリの`production-ready`を更新する権限だけを使います。mainの編集権限を持つ人はサイトを更新できるため、PRのレビューで意図を確認してください。

## 停止・復旧

自動公開を止めるときはConoHaのジョブスケジューラー、または`crontab -e`で`# mitsune-github-deploy`の行を停止します。GitHub Actionsの停止だけでは、既に渡したコミットのサーバー処理は止まりません。

障害時はサーバーの`deploy.log`とreceiptを確認します。競合は強制上書きせず現行ソースを別フォルダへ取得して解消します。途中失敗では自分が書いた値のままの対象だけを戻し、他者の変更は保護します。公開読み戻し後にHTTP確認が失敗した場合は基準を保存し、次回はその基準で再確認します。

通常の取り消しは本文・テーマの変更をrevertするPRを作りmainへマージします。基準ハッシュを過去へ戻さないでください。手動公開はcronを停止し、最新`production.json`を使い、`plan --only KEY`から実施してください。

初回接続用SSH鍵`mitsune-deploy-setup`は運用者のPCに保管しGit管理しません。cronはこの鍵に依存せず、GitHubの公開リポジトリを読み取ります。
