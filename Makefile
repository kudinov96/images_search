up:
	docker-compose up -d
down:
	docker-compose down
fpm:
	docker-compose exec php-fpm /bin/sh