# Prerequisite

- you need a recent apache/php/mysql stack
- your php module must be configured with `mbstring.internal_encoding = UTF-8`

# How to install

- Go into the directory where your server will reside
- Fork the repo and clone it: `git clone https://yourname@bitbucket.org/yourname/nrdb.git`
- This creates a directory named nrdb. This has to be your apache DocumentRoot. 
- Go into it.
- Install Composer: `curl -s http://getcomposer.org/installer | php`
- Install the vendor libs: `php composer.phar install`
- Install the assets:  `php app/console assets:install --symlink`
- Create the database: `php app/console doctrine:database:create`
- If the above command fails, edit app/config/parameters.yml and try again
- Create the tables: `php app/console doctrine:schema:update --force`
- Import the data: `mysql -u root -p netrunnerdb < netrunnerdb-cards.sql`
- [Configure your web server with the correct DocumentRoot](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html). Alternatively, [use PHP's built-in Web Server](http://symfony.com/doc/current/cookbook/web_server/built_in.html).
- Point your browser to `/web/app_dev.php`

# How to add card images

- Put the card images in `src/Netrunnerdb/CardsBundle/Resources/public/images/cards`
- Set the `cardimages_dir` value in `app/config/parameters.yml` to the absolute path of the `cards` directory

# How to update

When you update your repository, run the following commands:

- `php composer.phar self-update`
- `php composer.phar update`
- `php app/console doctrine:schema:update --force`
- `php app/console cache:clear --env=dev`

## Deck of the Week

To update the deck of the week on the front page:

- `php app/console highlight` 

## Setup an admin account

- register
- make sure your account is enabled
- run `php app/console fos:user:promote --super <username>`

## Add cards

- login with admin-level account
- go to `/admin/card`, `/admin/pack`, `/admin/cycle`, etc.

## Add cards with Excel on existing pack

- note the code of the pack (wla for What Lies Ahead, etc.). let's say it's xxx
- login with admin-level account
- go to /api/set/xxx.xlsx
- open the downloaded file and add your cards
- go to /admin/excel/form and upload your file, click 'Validate' on confirmation screen
- actually the excel file can be the one from another pack, just replace the 2nd column