#! /bin/bash
git stash
git pull https://github.com/dmhurley/course-site master

curl -s http://getcomposer.org/installer | php
php composer.phar update

app/console bio:update

rm -rf app/cache/prod app/cache/dev
sudo chmod -R 777 app/cache app/logs web/files web/bundles