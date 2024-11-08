#!/bin/bash

php -d memory_limit=-1 ./bin/phpunit -c ./phpunit.xml.dist --testsuite=ocs --testdox $@
