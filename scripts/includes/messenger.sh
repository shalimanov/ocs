#!/bin/bash

GREEN='\033[0;32m'
RED='\033[0;31m'
DEFAULT='\033[0m'

function message {
  local TEXT=$1
  local TYPE=$2

  if [ "$TYPE" == "success" ]; then
    echo -e "${GREEN}Success: ${TEXT}${DEFAULT}"
  elif [ "$TYPE" == "error" ]; then
    echo -e "${RED}Error: ${TEXT}${DEFAULT}"
  else
    echo $TEXT
  fi
}
