install:
	composer install
	php bin/console assets:install --symlink web
