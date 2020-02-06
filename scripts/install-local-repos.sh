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
    CLONED=false

    if [[ ! -d "./$LOCAL_VENDOR_DIR/opendialog-$1" ]]; then
        echo "Cloning $1 using ssh"
        if git clone git@github.com:opendialogai/$1.git ${LOCAL_VENDOR_DIR}/opendialog-${1} ; then
           CLONED=true
        else
            echo "Failed. Cloning $1 using ssl"
            if git clone https://github.com/opendialogai/$1.git ${LOCAL_VENDOR_DIR}/opendialog-${1} ; then
                CLONED=true
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

if $INSTALL_CORE == 'true' ; then
    create_vendor_dir;
    install_repo 'core'
    delete_vendor 'core'
fi

if $INSTALL_WEBCHAT == 'true' ; then
    create_vendor_dir;
    install_repo 'webchat'
    delete_vendor 'webchat'
fi
