init-test:
	@php bin/console --env=test doctrine:database:drop -f -q --if-exists
	@php bin/console --env=test doctrine:database:create -q
	@php bin/console --env=test doctrine:schema:create -q
	@#php bin/console --env=test doctine:fixtures:load -n -q

test: init-test
	@php bin/phpunit
