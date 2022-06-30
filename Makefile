composer-install:
	docker-compose run php sh -c "composer install"

composer-update:
	docker-compose run php sh -c "composer update"

generate-schema:
	docker-compose run php sh -c "php bin/xsd-schema-make"

run-tests:
	docker-compose run php sh -c "php vendor/bin/phpunit ./tests"
