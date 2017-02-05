## Synopsis

Crawl jobs, filter and match people to the job


## Installation

php 5.5.28
mysql 5.6

/app/config/parameters.yml

parameters:
    database_host: 127.0.0.1
    database_port: '3306'
    database_name: symfony
    database_user: root
    database_password: ''
    mailer_transport: smtp
    mailer_host: 127.0.0.1
    mailer_user: root
    mailer_password: ''
    secret: ''

Run in console
php bin/console doctrine:database:create
and then
php bin/console doctrine:schema:update --force