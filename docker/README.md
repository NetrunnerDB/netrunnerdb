Docker & docker-compose based development environment for
[netrunnerdb](https://github.com/alsciende/netrunnerdb).

This is just designed for local development only and not for any production use cases.

You will need both docker and docker-compose installed.

The Docker setup is a bit clunky, but does result in no changes to the git
repos from the running server. The database and app volumes are real docker
volumes and will persist until you wipe them out.

All file changes should be picked up without restarting docker with the
exception of .htaccess and app_dev.php since those are copied into the image.

First, you will need to check out the
[netrunnerdb](https://github.com/alsciende/netrunnerdb) and
[netrunner-cards-json](https://github.com/Alsciende/netrunner-cards-json)
repositories somewhere on your system. It is easiest to check them out next to
nrdb-dev-env.

```sh
cd ..
git checkout https://github.com/alsciende/netrunnerdb
git checkout https://github.com/Alsciende/netrunner-cards-json
cd nrdb-dev-env
```

Next, add symlinks for the other repositories, build the containers and bring
them up:

```sh
./prepare-and-build.sh ../netrunnerdb ../netrunner-cards-json
docker-compose up -d
```

Now prepare the rest of the files for the images and set up the database:
```sh
./docker-first-run.sh
```

Once this has completed you can visit [localhost:8080](http://localhost:8080)
to see your new, empty, debug-and-dev-mode netrunnerdb instance.

**NOTE:** If you get database errors in the last step, for instance when you
rebuild the containers after some changes, it usually just means you haven't
waited long enough for the database server to start up. Wait thirty seconds and
try again.

There are  a few handy scripts to help you out as well.  local-bash.sh will bring up
a bash shell in your nrdb-dev instance. local-mysql.sh will prompt you for the
database password (default passwd) and connect you to the running mysql
instance.

To update the card data, run:

```sh
./import-cards.sh
```

while your docker image is running.
