# Prerequisite

- you need a recent apache/php/mysql stack
- your php module must be configured with `mbstring.internal_encoding = UTF-8`

# How to install

- Go into the directory where your server will reside
- Clone the repository (or your own fork)
- This creates a directory named `netrunnerdb`. This has to be your apache DocumentRoot.
- Also, clone  the data repository (or your own fork) at https://github.com/zaroth/netrunner-cards-json
- Go into it the directory `netrunnerdb`.
- Install Composer (see https://getcomposer.org/download/)
- Install the vendor libs: `composer install`
- Create the database: `php app/console doctrine:database:create`
- If the above command fails, edit app/config/parameters.yml and try again
- Create the tables: `php app/console doctrine:schema:update --force`
- Import all the data from the data repository: `php app/console nrdb:import:json ../netrunner-cards-json`
- [Configure your web server with the correct DocumentRoot](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html). Alternatively, [use PHP's built-in Web Server](http://symfony.com/doc/current/cookbook/web_server/built_in.html).
- Point your browser to `/web/app_dev.php`

# How to add card images

- Put the card images in `src/Netrunnerdb/CardsBundle/Resources/public/images/cards`
- Set the `cardimages_dir` value in `app/config/parameters.yml` to the absolute path of the `cards` directory

# How to update

When you update your repository, run the following commands:

- `composer self-update`
- `composer update`
- `php app/console doctrine:schema:update --force`
- `php app/console cache:clear --env=dev`

## Deck of the Week

To update the deck of the week on the front page:

- `php app/console highlight` 

## Setup an admin account

- register
- make sure your account is enabled
- run `php app/console fos:user:promote --super <username>`

## Add or edit cards

- update the json data
- run `php app/console nrdb:import:json ../netrunner-cards-json`
