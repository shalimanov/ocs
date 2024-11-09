#!/bin/bash

SCRIPTS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

source $SCRIPTS_DIR"/includes/messenger.sh"

NO_DEV=''
for arg in "$@"
do
  if [ "$arg" == "--no-dev" ]; then
    NO_DEV="--no-dev"
  fi
done

composer install --no-interaction $NO_DEV
composer dump-autoload --optimize
drush si --existing-config -y
drush cr
drush cim -y
drush cr
drush upwd admin admin

if [ $? -ne 0 ]; then
  message "installation failed." 'error'
else
  message "installation finished" 'success'
fi
