init:
	docker-compose up -d --build
	docker-compose exec php composer install
	cp src/.env.example src/.env
	docker-compose exec php php artisan key:generate
	docker-compose exec php php artisan storage:link

test-migrate:
	docker-compose exec php php artisan migrate --env=testing