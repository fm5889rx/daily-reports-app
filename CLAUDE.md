# CLAUDE.md

## プロジェクト概要

- Laravel 10 を使った PHP Web アプリケーションです。
- PHP の要件は `^8.1` です。
- 画面は Blade、フロントエンドのアセットは JavaScript / CSS を Vite でビルドします。
- データベースは MySQL を使用します。Docker Compose では MySQL 8.4 と Laravel Sail を利用します。

## よく使うコマンド

### 開発

```bash
# Sail のアプリケーションコンテナを起動
./vendor/bin/sail up -d

# JavaScript の依存パッケージをインストール（初回、または依存更新後）
./vendor/bin/sail npm install

# Tailwind CSS の初期設定（新規導入時のみ）
./vendor/bin/sail npm install --save-dev tailwindcss@^3.4.17 postcss autoprefixer
./vendor/bin/sail npx tailwindcss init -p

# Vite 開発サーバーを起動（ターミナルを開いたままにする）
./vendor/bin/sail npm run dev -- --host 0.0.0.0
```

初回は依存パッケージをインストールしてから開発サーバーを起動してください。Vite の開発サーバーはホストの `5173` 番ポートで利用します。フォーム画面を確認するときは、開発サーバーを起動したまま Laravel アプリの URL（通常は `http://localhost`）をブラウザーで開いてください。
Tailwind CSS の初期導入時は、`tailwind.config.js` の `content` に Blade と JavaScript のパス（`./resources/**/*.blade.php`、`./resources/**/*.js`）を設定し、`resources/css/app.css` に Tailwind の `@tailwind` ディレクティブを記述してください。各 Blade レイアウトでは `@vite(['resources/css/app.css', 'resources/js/app.js'])` を読み込みます。

```bash
# Sail 経由で Artisan コマンドを実行
./vendor/bin/sail artisan <command>
```

### ビルド

```bash
# 本番向けアセットをビルド
./vendor/bin/sail npm run build
```

### テスト・コード整形

```bash
# Laravel / PHPUnit のテストを実行
./vendor/bin/sail artisan test

# PHPUnit を直接実行
./vendor/bin/sail php vendor/bin/phpunit

# Laravel Pint で PHP コードのスタイルをチェック
./vendor/bin/sail php vendor/bin/pint --test

# Laravel Pint で PHP コードを整形
./vendor/bin/sail php vendor/bin/pint
```

テストは `tests/Feature` と `tests/Unit` にあります。テスト設定は `phpunit.xml` を参照してください。

### データベース

```bash
# データベースを再構築して、すべてのマイグレーションを実行
./vendor/bin/sail artisan migrate:fresh

# マイグレーションを取り消す
./vendor/bin/sail artisan migrate:rollback
```

マイグレーションを依頼された場合は、適用済みかどうかにかかわらず `migrate:fresh` でデータベースを再構築してください。このコマンドは全テーブルと保存データを削除してから、すべてのマイグレーションを実行します。開発用データベースに限って実行し、保持が必要なデータがある環境では実行しないでください。

データベース接続情報は `.env` で設定します。Docker Compose を使う場合は `.env.example` と `compose.yaml` の設定を確認してください。

### phpMyAdmin

phpMyAdmin は Sail の `phpmyadmin` サービスとして起動し、ホストの `8080` 番ポートで利用できます。MySQL サービスには `mysql:3306` で接続します。

```bash
# phpMyAdmin を起動
./vendor/bin/sail up -d phpmyadmin
```

ブラウザーで `http://localhost:8080` を開き、サーバー名に `mysql`、ユーザー名とパスワードに `.env` の `DB_USERNAME` と `DB_PASSWORD` を指定してログインしてください。

## ディレクトリ構成

- `app/` — アプリケーションの PHP コード（コントローラー、モデル、ミドルウェア、プロバイダーなど）
- `routes/` — Web / API ルート定義
- `resources/views/` — Blade テンプレート
- `resources/js/`, `resources/css/` — フロントエンドの JavaScript と CSS
- `database/migrations/` — データベーススキーマの変更
- `database/factories/`, `database/seeders/` — テストデータや初期データ
- `tests/Feature/`, `tests/Unit/` — 機能テスト、ユニットテスト
- `public/` — Web 公開ディレクトリ

## 開発ルール

- 開発環境のコマンドは Laravel Sail 経由で実行してください（例: `./vendor/bin/sail artisan test`）。
- コミットメッセージは日本語で書いてください。
- コード内のコメントは日本語で書いてください。
- テストは PHPUnit で書き、Pest は使用しないでください。
- PHP コードは PSR-12 に準拠してください。
- 変更前に関連するルート、コントローラー、モデル、Blade、マイグレーション、テストを確認してください。
- 既存の Laravel の構成と書き方に合わせ、依頼に必要な範囲だけを変更してください。
- Web 画面のルートは `routes/web.php`、API ルートは `routes/api.php` に定義してください。ルートには処理を詰め込まず、必要に応じてコントローラーへ分離してください。
- スキーマ変更は既存マイグレーションを書き換えず、新しいマイグレーションを追加してください。
- マイグレーションの実行を依頼された場合は、`./vendor/bin/sail artisan migrate:fresh` でデータベースを必ず再構築してください。全テーブルとデータが削除されるため、開発用データベース以外では実行しないでください。
- 新しい振る舞いには対応するテストを追加し、変更に関連するテストを実行してください。
- PHP の変更後は可能な範囲で `./vendor/bin/sail php vendor/bin/pint --test` を実行してください。フロントエンド変更後は `./vendor/bin/sail npm run build` を実行してください。
- `package.json` に JavaScript の lint コマンドは定義されていません。存在しない lint コマンドを前提にしないでください。
- `.env` や認証情報などの秘密情報をソース管理に含めないでください。