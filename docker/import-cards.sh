#!/bin/bash

set -e
set -u

echo "Updating the card data."
docker exec -it nrdb-dev bash -c "php bin/console doctrine:schema:update --force; php bin/console app:import:std -f cards"

echo "All done!"
