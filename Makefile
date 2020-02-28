.PHONY: list
list:
	@$(MAKE) -pRrq -f $(lastword $(MAKEFILE_LIST)) : 2>/dev/null | awk -v RS= -F: '/^# File/,/^# Finished Make data base/ {if ($$1 !~ "^[#.]") {print $$1}}' | sort | egrep -v -e '^[^[:alnum:]]' -e '^$@$$'

D=docker-compose -f docker-compose.yml
DT=docker-compose -f docker-compose-test.yml
WD=$$(pwd)
COMPOSER=docker run -it --volume $(WD):/app/ composer

clean-install:
	rm -rf vendor/
	make composer-install

composer-install:
	$(COMPOSER) install --prefer-dist --no-progress --no-suggest

composer-update:
	$(COMPOSER) update

composer-remove:
	$(COMPOSER) remove $(P)

composer-dump:
	$(COMPOSER) dump-autoload

composer-require:
	$(COMPOSER) require

composer-require-dev:
	$(COMPOSER) require --dev

test: phpunit behat

phpunit:
	bin/phpunit $T

behat: start-test
	bin/behat --stop-on-failure -vv

## DEV
ps:
	$(D) ps

start:
	$(D) up --detach --remove-orphans

stop:
	$(D) rm -s -f -v

## TEST
ps-test:
	$(DT) ps

log-test:
	$(DT) logs -f

start-test:
	$(DT) up --detach --remove-orphans

stop-test:
	$(DT) rm -s -f -v
