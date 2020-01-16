#!/bin/bash

LOCAL_VENDOR_DIR="vendor-local"

if [ ! -d "./$LOCAL_VENDOR_DIR" ]; then
  echo "Creating local vendor directory"
  mkdir "./$LOCAL_VENDOR_DIR"
fi

for repo in opendialog-core opendialog-webchat
do
  LOCAL_DIR="vendor-local/$repo"
  if [ ! -d "./$LOCAL_DIR" ]; then
    subrepo="$( cut -d '-' -f 2- <<< "$repo" )";
    echo "$repo::: Cloning $repo to vendor-local"
    git clone git@github.com:opendialogai/$subrepo.git vendor-local/$repo
    cd $LOCAL_DIR
    echo "$repo::: Installing composer"
    composer install
    cd ../..
  fi
done

echo "OPENDIALOG::: Installing composer with symlink"
export COMPOSER=composer-dev.json
composer update
