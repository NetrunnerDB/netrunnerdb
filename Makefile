install:
	composer install
	php bin/console assets:install --symlink web

test:
	vendor/bin/phpstan analyze src --level 7
