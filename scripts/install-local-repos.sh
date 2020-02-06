#!/usr/bin/env bash

LOCAL_VENDOR_DIR="vendor-local"

# Creates a local vendor directory if it doesn't already exist
create_vendor_dir () {
    if [ ! -d "./$LOCAL_VENDOR_DIR" ]; then
      echo "Creating local vendor directory"
      mkdir "./$LOCAL_VENDOR_DIR"
    fi
}

# Tries to install the given repo via ssh first, then ssl if that fails
install_repo () {
    if [[ ! -d "./$LOCAL_VENDOR_DIR/opendialog-$1" ]]; then
        echo "Cloning $1 using ssh"
        if git clone git@github.com:opendialogai/$1.git ${LOCAL_VENDOR_DIR}/opendialog-${1} ; then
           echo "Cloned"
        else
            echo "Failed. Cloning $1 using ssl"
            if git clone https://github.com/opendialogai/$1.git ${LOCAL_VENDOR_DIR}/opendialog-${1} ; then
                echo "Cloned"
            else
                echo "Cannot clone $1 repo - exiting script"
                return 1
            fi
        fi
    else
        echo "$1 is already cloned locally"
    fi
}

# Deletes the given local vendor directory
delete_vendor() {
    if [[ -d "./vendor/opendialogai/$1" ]]; then
        rm -rf ./vendor/opendialogai/$1
    fi
}

delete_local_vendor() {
    if [[ -d "./vendor-local/opendialog-$1" ]]; then
        echo "Deleting local $1 vendor"
        rm -rf ./vendor-local/opendialog-$1
    fi
}

set -e

if ${INSTALL_CORE} == 'true' ; then
    create_vendor_dir;
    install_repo 'core'
    delete_vendor 'core'
else
    delete_vendor 'core'
    delete_local_vendor 'core'
fi

if ${INSTALL_WEBCHAT} == 'true' ; then
    create_vendor_dir;
    install_repo 'webchat'
    delete_vendor 'webchat'
else
    delete_vendor 'webchat'
    delete_local_vendor 'webchat'
fi
