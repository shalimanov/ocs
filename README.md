# OCS

#### Code formatting:
* [Drupal coding standards](https://www.drupal.org/docs/develop/standards)
* [PHPStan (project configured on level 9)](https://phpstan.org/user-guide/rule-levels)
* [Conventional commits](https://www.conventionalcommits.org/)

## Local environment
### Requirements:
* [DDEV](https://ddev.readthedocs.io/en/stable/)

### Local environment setup:
* ```mkdir ocs```
* ```git clone *REPOSITORY* ocs```
* ```cd ocs```
* ```ddev composer install```
* ```ddev start```
* ```ddev composer site-install```

## How-to
### Use DDEV
* ```ddev start``` start application
* ```ddev stop``` stop application
* ```ddev restart``` restart application
* ```ddev ssh``` connect to web container
* ```ddev exec <command>``` execute command in web container
* ```ddev drush``` run Drush
* ```ddev composer``` run Composer

### PHPUnit
* ```ddev composer phpunit``` run PHPUnit tests from module/custom directory

### Composer scripts
* ```ddev composer site-install``` installs new site from existing configs
* ```ddev composer build``` sync database with code configurations

### Useful links
* Use ```ddev describe``` command to get completed list of link.
* Web ``` https://ocs.ddev.site/```.
* MailHog ```https://ocs.ddev.site:8026/```.
* PHPMyAdmin ```https://ocs.ddev.site:8037```.
* Solr ```http://ocs.ddev.site:8983```.

---
