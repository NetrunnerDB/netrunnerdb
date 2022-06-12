#!/bin/bash

NRDB_PATH=$1
CARDS_PATH=$2

read -r -d '' USAGE << EOM
Please run this script with paths to both the nrdb and netrunner-cards-json repositories you have checked out.
For example:
  ./prepare-and-build.sh ../netrunnerdb/ ../netrunner-cards-json/
EOM

if [ -z "${NRDB_PATH}" ] || [ -z "${CARDS_PATH}" ]
then
  echo "${USAGE}" 
  exit 1
fi

if [ ! -d "${NRDB_PATH}" ]
then
  echo "nrdb directory ${NRDB_PATH} does not exist."
  exit 1
fi
if [ ! -d "${CARDS_PATH}" ]
then
  echo "netrunner-cards-json directory ${CARDS_PATH} does not exist."
  exit 1
fi

set -e
set -u

echo "Linking netrunnerdb to ${NRDB_PATH} ..."
if [ -L netrunnerdb ]
then
  echo "Link already exists. Skipping."
else
  ln -s "${NRDB_PATH}" netrunnerdb
fi

echo "Linking netrunner-cards-json to ${CARDS_PATH} ..."
if [ -L netrunner-cards-json ]
then
  echo "Link already exists. Skipping."
else
  ln -s "${CARDS_PATH}" netrunner-cards-json
fi

docker-compose build
