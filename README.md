# Mitsune

[Mitsune](https://mitsune-ai.com/) の公開記事とWordPressテーマの編集用リポジトリです。どのAI・エディタでも、同じソースから変更を提案できます。最初に [AGENTS.md](AGENTS.md) を読んでください。

## 最初の作業

```sh
git clone https://github.com/lumpkasumi-netizen/Mitsune.git
cd Mitsune
git switch -c edit/describe-your-change
python scripts/site_sync.py validate
python -m unittest discover -s tests_portable -v
```

Python 3.11以上を使用します。編集と検証にはWordPressの認証情報は不要です。サイトとの通信を行う環境では `python -m pip install -r scripts/requirements.txt` を実行してください。

| 場所 | 編集内容 |
| --- | --- |
| `site/posts/<ID>-<slug>.html` | 記事本文。WordPressの保存HTMLそのもの |
| `site/posts/<ID>-<slug>.json` | 記事タイトル・抜粋 |
| `site/pages/` | ホーム・お問い合わせ・プライバシーポリシー |
| `site/theme/` | 現行テーマのPHP・CSS・JavaScript |
| `site/manifest.json` | 記事ID・ファイル対応・公開済みの基準ハッシュ。手で更新しない |
| `scripts/site_sync.py` | 検証・差分確認・取得・公開 |
| `docs/` | 編集ルール、運用、復旧手順 |

2026-10-02に、公開56記事・固定3ページ・テーマの編集可能な11ファイルを取り込みました。服装・ポーズ・飲食・和風の同日改修を含みます。画像は既存WordPressのメディアURLを参照します。メディア本体、WordPress本体、プラグイン、DB、サーバー設定、非公開記事を含むサーバー全体のバックアップではありません。

## GitHubからサイトを更新する流れ

1. AIにこのリポジトリを渡し、ブランチで本文やテーマを編集させます。
2. ローカル検証とGitHub Actionsのチェックを通し、差分をPRでレビューします。
3. マージ後、認証情報を持つ運用環境で `plan` を実行し、本番との差分を確認します。
4. `deploy --apply` で指定した変更だけを公開します。公開確認後、更新されたmanifestもコミットします。

**GitHubへのpush・マージだけでは本番は変わりません。** 現状は明示的な手動デプロイです。AIへのリポジトリアクセスと、本番WordPressへのアクセスは別に管理します。詳しくは [更新・復旧手順](docs/OPERATIONS.md)。

AIへの依頼例：

> AGENTS.mdとdocs/EDITORIAL.mdを読み、服装記事の種類別リンクを改善してください。site/posts/42-*.htmlを編集し、検証結果と差分をPRにしてください。manifestの基準ハッシュは書き換えず、本番デプロイは行わないでください。

参考仕様：[WordPress認証](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/)、[GitHub Actions](https://docs.github.com/en/actions/get-started/quickstart)。
