# 日報アプリ

Laravel 13 で作成した日報管理アプリです。日報の作成・一覧表示・編集・削除ができ、作成者を記録して一覧に報告者名を表示します。
Laravel Fortify によるメールアドレス認証に対応しています。ログイン画面から新規登録またはログインできます。

## 動作環境

- PHP 8.5（ロック済み依存パッケージの要件）
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

## 本番環境へのデプロイ（Ver3 公開版）

本番構成は Docker Compose を使用し、PHP-FPM、Nginx、MySQL を分離しています。Sail の `compose.yaml` は開発用として維持し、本番では `compose.production.yaml` を使用してください。Docker Engine と Docker Compose v2 が必要です。

1. 本番サーバーで設定ファイルを作成し、アプリ URL、十分に長いランダムな DB パスワード、SMTP 設定を環境に合わせて設定します。`.env.production` は秘密情報を含むため、リポジトリへ登録しないでください。

    ```bash
    cp .env.production.example .env.production
    chmod 600 .env.production
    ```

2. 本番用イメージをビルドし、アプリケーションキーを生成します。表示されたキーを `.env.production` の `APP_KEY` に設定し、以後のデプロイでも同じ値を維持してください。

    ```bash
    docker compose --env-file .env.production -f compose.production.yaml build
    docker compose --env-file .env.production -f compose.production.yaml run --rm --no-deps --entrypoint php app artisan key:generate --show
    ```

3. データベースを起動してから、通常のマイグレーションを適用し、アプリを起動します。

    ```bash
    docker compose --env-file .env.production -f compose.production.yaml up -d mysql
    docker compose --env-file .env.production -f compose.production.yaml run --rm app php artisan migrate --force
    docker compose --env-file .env.production -f compose.production.yaml up -d
    ```

    本番データを消去する `migrate:fresh` は実行しないでください。更新時もイメージを再ビルドし、`migrate --force` を実行してからサービスを起動します。

4. `APP_URL` には公開 URL を設定してください。Web コンテナは HTTP のみで待ち受けるため、インターネットへ直接公開せず、HTTPS を終端するリバースプロキシの背後で利用してください。`SESSION_SECURE_COOKIE=true` は HTTPS 環境向けです。

MySQL のデータと Laravel のストレージは Docker named volume に保存されます。コンテナイメージを更新しても保持されますが、ボリュームはバックアップの代わりにはなりません。運用前に DB と必要なファイルのバックアップ／復元手順を用意してください。この Compose 構成のセッションとキャッシュはファイルベースで、単一アプリインスタンスを前提とします。複数台構成にする場合は共有可能なセッション／キャッシュストアと外部 DB を別途設定してください。
