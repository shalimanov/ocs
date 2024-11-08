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
drush cr
drush updatedb -y
drush cim -y
drush cr

# shellcheck disable=SC2181
if [ $? -ne 0 ]; then
  message "build failed." 'error'
else
  message "build finished" 'success'
fi
