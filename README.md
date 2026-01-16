# 勤怠管理アプリ

## 環境構築

### Docker ビルド

1. `git clone git@github.com:yoki229/KINTAI.git`
2. `cd KINTAI`
3. DockerDesktop アプリを立ち上げる

### Laravel 環境構築

1. `make init`
    「Command 'make' not found」となる場合は  
    - `sudo apt update`
    - `sudo apt install make`
    の後に「make init」してください。  
2. `code .`
3. .env ファイルの環境変数を変更

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

4. `docker-compose exec php php artisan migrate --seed`

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
5. 以上のセットアップで権限のエラーが発生する場合は
```
sudo chmod -R 777 src/*
```
入力、使用してください。  

## メール認証について

メール認証機能は mailHog を使用しています。  
メール送信後は URL localhost:8025 から確認できるようにしております。  

## 使用技術

- PHP 8.1
- Laravel 8.6
- MySQL 8.0.26

## テストアカウント
name: 管理者ユーザー  
email: hanako.s@example.com  
password: password  
-------------------------
name: 一般ユーザー  
email: reina.n@coachtech.com  
password: password  
-------------------------

## テーブル仕様

### usersテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | unsigned bigint | ◯ |  | ◯ |  |
| name | string |  |  | ◯ |  |
| email | string |  | ◯ | ◯ |  |
| email_verified_at | timestamp |  |  |  |  |
| password | string |  |  | ◯ |  |
| role | string |  |  | ◯ |  |
| remember_token | string(100) |  |  | ◯ |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### AttendanceRecordsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | unsigned bigint | ◯ |  | ◯ |  |
| user_id | unsigned bigint |  |  | ◯ | users(id) |
| work_date | date |  |  | ◯ |  |
| clock_in | time |  |  |  |  |
| clock_out | time |  |  |  |  |
| status | string |  |  | ◯ |  |
| note | text |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |
#### 制約
- user_id と work_date の組み合わせでユニーク制約あり（同一ユーザーの同日重複防止）

### AttendanceCorrectionsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | unsigned bigint | ◯ |  | ◯ |  |
| attendance_record_id | unsigned bigint |  |  | ◯ | attendance_records(id) |
| user_id | unsigned bigint |  |  | ◯ | users(id) |
| requested_changes | json |  |  | ◯ |  |
| reason | text |  |  |  |  |
| status | string |  |  | ◯ |  |
| processed_by | unsigned bigint |  |  |  | users(id) |
| processed_at | timestamp |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### BreakRecordsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | unsigned bigint | ◯ |  | ◯ |  |
| attendance_record_id | unsigned bigint |  |  | ◯ | attendance_records(id) |
| break_start | time |  |  | ◯ |  |
| break_end | time |  |  |  |  |
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

## ER 図

![ER図](readme-assets/table.drawio.png)

## 画面仕様について

アプリの〈申請一覧画面〉では  
承認済みタブから詳細を押下しページ推移した際、  
そこにまた押下できるボタンが表示されていることに違和感があったため  

- 管理者ユーザー ： 「承認」ボタン → 「承認済みです。」のテキスト
- 一般ユーザー ： 「修正」ボタン → 「承認済みです。」のテキスト

が表示されるように変更しております。  

## URL

- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/
