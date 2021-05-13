all: phpstan phpunit

phpstan: vendor/.sentinel
	./vendor/bin/phpstan

phpunit: vendor/.sentinel
	./vendor/bin/phpunit

vendor/.sentinel: composer.json composer.lock
	composer install
	touch $@

clean:
	rm -rf vendor/.sentinel
