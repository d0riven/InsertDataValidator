PHP:=$(shell which php)
CODECEPT:=vendor/bin/codecept
DOCKER_COMPOSE:=$(shell which docker-compose)

.PHONY: setup
setup: composer.phar composer/install tests/_output

.PHONY: composer/install composer/update
composer/install: composer.phar
	$(PHP) composer.phar $(@F)
composer/update: composer.phar
	$(PHP) composer.phar $(@F)

composer.phar:
	$(PHP) -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	$(PHP) -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
	$(PHP) composer-setup.php
	$(PHP) -r "unlink('composer-setup.php');"

.PHONY: test unit_test
test: unit_test unit_test_with_db
unit_test: vendor/bin/codecept
	$(CODECEPT) run -vvv unit

.PHONY: unit_test_with_db
unit_test_with_db: docker/start
	$(CODECEPT) run -vvv unit_with_db

.PHONY: docker/start docker/clean
docker/start: docker-compose.yml
	$(DOCKER_COMPOSE) up -d
docker/clean: docker-compose.yml
	$(DOCKER_COMPOSE) stop
	$(DOCKER_COMPOSE) rm -f

vendor/bin/codecept:
	$(MAKE) setup

tests/_output:
	mkdir $@
