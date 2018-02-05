# Wordpress Setup

## New Project
Copy the contents of this folder to a new folder i.e. ~/Sites/new_project

```bash
mkdir web
sed -i -e 's/SITENAME/new_project/g' .env
sed -i -e 's/SITENAME/new_project/g' docker-compose.yml
sed -i -e 's/SITENAME/new_project/g' docker-sync.yml
sed -i -e 's/SITENAME/new_project/g' default.wp-config.php
# Edit default.wp-config.php to setup the $table_prefix = 'wp_';
mv default.wp-config.php web/sites/default/default.wp-config.php
mv .htaccess.default web/.htaccess.default
```

## Existing Project
Copy the contents of this folder to a new folder i.e. ~/Sites/existing_project

```bash
# git mv commands
sed -i -e 's/SITENAME/existing_project/g' .env
sed -i -e 's/SITENAME/existing_project/g' docker-compose.yml
sed -i -e 's/SITENAME/existing_project/g' docker-sync.yml
sed -i -e 's/SITENAME/existing_project/g' default.wp-config.php
# Edit default.wp-config.php to setup the $table_prefix = 'wp_';
mv default.wp-config.php web/sites/default/default.wp-config.php
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

```bash
# If you do not have xeno_robo
# If you have robo already you will need to remove it 
# `cgr consolidation/robo remove`
# `composer global require consolidation/robo remove`
# Install cgr if neeeded
# `composer global require consolidation/cgr`
cgr xenomedia/xeno_robo
ls -l `which robo`
# should return ~/.composer/vendor/bin/robo@ -> ../../global/xenomedia/xeno_robo/vendor/consolidation/robo/robo
robo setup
```
