# 勤怠管理アプリ

## 環境構築

### Docker ビルド

1. `git clone git@github.com:yoki229/KINTAI.git`
2. `cd KINTAI`
3. DockerDesktop アプリを立ち上げる

### Laravel 環境構築

1. `make init`
2. .env ファイルの環境変数を変更

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=hello_db
DB_USERNAME=hello_user
DB_PASSWORD=hello_pass

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=info@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

3. `docker-compose exec php php artisan migrate --seed`

### PHPUnit を利用したテスト用 DB を作成

1. `cp src/.env src/.env.testing`
   (.env ファイルから.env.testing を作成)
2. .env.testing ファイルの環境変数を変更

```
APP_ENV=test
APP_KEY=base64:******
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql_test
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=test_db
DB_USERNAME=root
DB_PASSWORD=root
```

3. MySQL コンテナに入ってテスト用 DB を作成

```
docker-compose exec mysql mysql -u root -p
# パスワードは root
CREATE DATABASE test_db;
EXIT;
```

4. `make test-migrate`

## メール認証について

メール認証機能は mailHog を使用しています。
メール送信後は URL localhost:8025 から確認できるようにしております。

## 使用技術

- PHP 8.1
- Laravel 8.6
- MySQL 8.0.26

## テーブル仕様

![テーブル1](readme-assets/table_1.png)
![テーブル2](readme-assets/table_2.png)
![テーブル3](readme-assets/table_3.png)

## ER 図

![ER図](readme-assets/table.drawio.png)

## URL

- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/
