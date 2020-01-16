#!/bin/bash

LOCAL_VENDOR_DIR="vendor-local"
LOCAL_CORE_DIR="vendor-local/opendialog-core"
if [ ! -d "./$LOCAL_VENDOR_DIR" ]; then
  echo "Creating local vendor directory"
  mkdir "./$LOCAL_VENDOR_DIR"
fi
if [ ! -d "./$LOCAL_CORE_DIR" ]; then
  echo "CORE::: Cloning opendialog-core to vendor-local"
  git clone git@github.com:opendialogai/core.git vendor-local/opendialog-core
  cd $LOCAL_CORE_DIR
  echo "CORE::: Installing composer"
  composer install
  cd ../..
fi
echo "OPENDIALOG::: Installing composer with symlink"
export COMPOSER=composer-dev.json
composer update
