# 日報アプリ

Laravel 13 で作成した日報管理アプリです。日報の作成・一覧表示・編集・削除ができ、作成者を記録して一覧に報告者名を表示します。
Laravel Fortify によるメールアドレス認証に対応しています。ログイン画面から新規登録またはログインできます。

## 動作環境

- PHP 8.3 以上
- Docker Compose（Laravel Sail）
- Node.js / npm（フロントエンドのアセットをローカルでビルドする場合）

## セットアップ

```bash
cp .env.example .env
```

`.env` のデータベース接続設定を確認し、Sail コンテナ内から接続する場合は `DB_HOST=mysql` に設定してください。その後、以下を実行します。

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

アプリケーションは通常 `http://localhost` で利用できます。

アカウントを作成する場合は、ログイン画面でメールアドレス、パスワード、パスワード確認を入力して「新規登録」を選択してください。登録後は日報一覧へ移動します。日報一覧ではログインユーザー名と各日報の報告者名を確認できます。

## テスト

```bash
./vendor/bin/sail php vendor/bin/phpunit
```
