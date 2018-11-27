This repository holds the source code of [NetrunnerDB](https://netrunnerdb.com).

# This is not where the cards data is

The data used by NetrunnerDB is at https://github.com/Alsciende/netrunner-cards-json. If you want to fix a mistake in some card data, or add the data of a new card, you can submit a PR [there](https://github.com/Alsciende/netrunner-cards-json/pulls). Also, that's where the localized data is.

# Installing a local copy of NetrunnerDB

## Prerequisite

- you need a recent apache/php/mysql stack
- your php module must be configured with `mbstring.internal_encoding = UTF-8`

## How to install

- Go into the directory where your server will reside
- Clone the repository (or your own fork)
- This creates a directory named `netrunnerdb`
- Also, clone the data repository (or your own fork) at https://github.com/zaroth/netrunner-cards-json
- Go into it the directory `netrunnerdb`
- Install Composer (see https://getcomposer.org/download/)
- Install the vendor libs: `composer install`. You'll be asked to input your database connection parameter.
- Create the database: `php bin/console doctrine:database:create`
- If the above command fails, edit app/config/parameters.yml and try again
- Create the tables: `php bin/console doctrine:schema:update --force`
- Import all the data from the data repository: `php bin/console app:import:std -f path_to_json_repository`
- [Configure your web server with the correct DocumentRoot](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html). Alternatively, [use PHP's built-in Web Server](http://symfony.com/doc/current/cookbook/web_server/built_in.html). Set your DocumentRoot to `netrunnerdb/web`
- Point your browser to `/app_dev.php`

## How to add card images

- Put the card images in `web/card_image/` (`web/card_image/01001.png`, etc.)

## How to update

When you update your repository (`git pull`), run the following commands:

- `composer self-update`
- `composer install` (do *not* run `composer update`)
- `php bin/console doctrine:schema:update --force`
- `php bin/console app:import:std path_to_json_repository`

## Deck of the Week

To update the deck of the week on the front page:

- `php bin/console app:highlight decklist_id` 

where `decklist_id` is the numeric id of the deck you want to highlight.

## Setup an admin account

- register
- make sure your account is enabled (or run `php bin/console fos:user:activate <username>`)
- run `php bin/console fos:user:promote --super <username>`

## Add or edit cards

- update the json data
- run `php bin/console app:import:json path_to_json_repository`
