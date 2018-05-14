# Drupal 7 Setup

[![Build Status](https://jenkins4.xenostaging.com/buildStatus/icon?job=xenomedia/SITENAME/master)](https://jenkins4.xenostaging.com/job/xenomedia/job/SITENAME/job/master/)

## New Project

Copy the contents of this folder to a new folder i.e. ~/Sites/new_project

```bash
mkdir web
cd web
drush dl drupal-7 --select
cd ..
sed -i -e 's/SITENAME/new_project/g' .env
sed -i -e 's/SITENAME/new_project/g' docker-compose.yml
sed -i -e 's/SITENAME/new_project/g' default.local.settings.php
mv default.local.settings.php web/sites/default/default.local.settings.php
mv .htaccess.default web/.htaccess.default
```

## Existing Project

Copy the contents of this folder to a new folder i.e. ~/Sites/existing_project

If your site root is not in the web folder you should move it using `git mv`

```bash
mkdir web
# git mv commands
sed -i -e 's/SITENAME/existing_project/g' .env
sed -i -e 's/SITENAME/existing_project/g' docker-compose.yml
sed -i -e 's/SITENAME/existing_project/g' default.local.settings.php
mv default.local.settings.php web/sites/default/default.local.settings.php
mv .htaccess.default web/.htaccess.default
```

## Common Commands

Edit robo.yml.dist

```yml
site:
  grunt_path: web/path/to/grunt/ # Leave blank if no grunt.
  root_path: web
# Only if the site is hosted on pantheon.
pantheon:
  site_name: example
  env: prod
# Only if the site is on our staging server.
stage:
  site_name: site_name # Usually will be the folder name which the site is on staging.
  user: myuser # ssh user for database backup.
  host: myserver.com # ssh host for database backup.
  backup_location: /path/to/backups # backup location; a file named [site_name].sql.gz should exist at this location.
database:
  database: drupal
  user: drupal
  password: drupal

```

## Install Xeno Robo

```bash
cd project/root
composer global require xenomedia/xeno_robo
robo setup
```
