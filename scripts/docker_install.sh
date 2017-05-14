#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password password travis'
debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password_again password travis'

cp app/config/parameters.yml.dist app/config/parameters.yml

# Install git (the php image doesn't have it) which is required by composer
apt-get update -yqq
apt-get install git mysql-server unzip -yqq
service mysql start

# Install phpunit, the tool that we will use for testing
curl --location --output /usr/local/bin/phpunit https://phar.phpunit.de/phpunit.phar
chmod +x /usr/local/bin/phpunit

# Install mysql driver
# Here you can install any other extension that you need
docker-php-ext-install pdo_mysql
#docker-php-ext-install pdo_sqlite

echo "date.timezone = \"Europe/Madrid\"" >> /usr/local/etc/php/conf.d/timezone.ini
