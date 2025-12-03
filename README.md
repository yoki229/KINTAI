# 勤怠管理アプリ

## 環境構築

### Docker ビルド

1. `git clone git@github.com:yoki229/KINTAI.git`
2. `cd KINTAI`
3. DockerDesktop アプリを立ち上げる
4. `docker-compose up -d --build`

> Mac の M1・M2 チップの PC の場合、no matching manifest for linux/arm64/v8 in the manifest list entries のメッセージが表示されビルドができないことがあります。 エラーが発生する場合は、docker-compose.yml ファイルの「mysql」内に「platform」の項目を追加で記載してください

```
mysql:
    platform: linux/x86_64(この文追加)
    image: mysql:8.0.26
    environment:
```

### Laravel 環境構築

1. `docker-compose exec php bash`(phpコンテナ名が違う場合は適宜変更)
2. `composer install`
    `composer require --dev phpunit/phpunit`
    `exit`
3. `cp src/.env.example src/.env`
   (.env.example ファイルから.env を作成)
4. .envファイルの環境変数を変更

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

Stripe決済用のAPIキーはhttps://stripe.com/jp（Stripe公式HP）
のダッシュボードより取得し、.envの一番下に下記の形で記入すること。
```
STRIPE_PUBLIC_KEY=pk_test_*****
STRIPE_SECRET_KEY=sk_test_*****
```

5. アプリケーションキーの作成

```
docker-compose exec php bash
php artisan key:generate
exit
```

6. `cp src/.env src/.env.testing`
   (.env ファイルから.env.testing を作成)
7. .env.testingファイルの環境変数を変更
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

8. MySQLコンテナに入ってテスト用DBを作成
```
docker-compose exec mysql mysql -u root -p
# パスワードは root
CREATE DATABASE test_db;
SHOW TABLES;
EXIT;
docker-compose exec php bash
php artisan migrate --env=testing
```

9. マイグレーションの実行

```
php artisan migrate
```

10. シーディングの実行

```
php artisan db:seed
```

11. シンボリックリンクの作成

```
php artisan storage:link
exit
```

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
