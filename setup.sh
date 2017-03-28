php bin/console doctrine:database:drop --force --env=dev
php bin/console doctrine:database:create --env=dev
php bin/console doctrine:schema:update --force --env=dev
php bin/console doctrine:fixtures:load --env=dev --no-interaction
php bin/console nrdb:import:std ../netrunner-cards-json/ --force
