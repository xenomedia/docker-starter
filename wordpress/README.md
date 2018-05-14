# WordPress Setup

[![Build Status](https://jenkins4.xenostaging.com/buildStatus/icon?job=xenomedia/SITENAME/master)](https://jenkins4.xenostaging.com/job/xenomedia/job/SITENAME/job/master/)

## New Project

Copy the contents of this folder to a new folder i.e. ~/Sites/new_project

In the following commands, replace `new_project` with your new project's repository name.

```bash
mkdir web
sed -i -e 's/SITENAME/new_project/g' .env
sed -i -e 's/SITENAME/new_project/g' docker-compose.yml
sed -i -e 's/SITENAME/new_project/g' default.wp-config.php
sed -i -e 's/SITENAME/new_project/g' Jenkinsfile
# Edit default.wp-config.php to setup the $table_prefix = 'wp_';
mv default.wp-config.php web/default.wp-config.php
mv .htaccess.default web/.htaccess.default

# Remove the un-needed -e files.
rm .env-e default.wp-config.php-e docker-compose.yml-e
```

## Existing Project

Copy the contents of this folder to a new folder i.e. ~/Sites/project_name

In the following commands, replace `project_name` with your new project's repository name.

```bash
# git mv commands
sed -i -e 's/SITENAME/project_name/g' .env
sed -i -e 's/SITENAME/project_name/g' docker-compose.yml
sed -i -e 's/SITENAME/project_name/g' default.wp-config.php
sed -i -e 's/SITENAME/project_name/g' Jenkinsfile
# Edit default.wp-config.php to setup the $table_prefix = 'wp_';
mv default.wp-config.php web/default.wp-config.php
mv .htaccess.default web/.htaccess.default
```

## Edit the Robo file

Edit robo.yml.dist

```yml
site:
  grunt_path: web/path/to/grunt/ # Leave blank if no grunt.
  root_path: web # Leave blank if the site root is the repo base.
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

## Install your WordPress with [wp-composer-starter](https://github.com/xenomedia/wp-composer-starter)

* `cd ~/Sites`
* `git clone https://github.com/xenomedia/wp-composer-starter`
* Switch from master to `pantheon` or `not-pantheon` branch
* Copy the composer.json file to your new project's repo
* Update words in the Readme.md and composer.json files
* Run `composer install`

## Workflow

Work locally and follow a normal git workflow using a new branch cloned from master.  Merging code into the master branch can only be done with Pull requests.  To test and deploy your changes, follow these steps.

Keep your branch names 11 characters or less.  Pantheon has this limit on multidev environment names.

* Push your branch to Github `git push origin <branch-name>`
  * This will trigger a Job on Jeknins that will build a MulitDev on Pantheon
  * There will be a slack alert in #SITENAME-deploys with a link to the site when it is ready
* Test at [https://`<branch-name>`-SITENAME.pantheonsite.io/](https://`<branch-name>`-SITENAME.pantheonsite.io/)
* Visit repository on github `hub browse`
* Open a Pull Request `hub pull-request`
  * This will trigger a Job on Jeknins that will sync code back to Pantheon
  * The status can be seen in the PR on GitHub or under the PR numer at [https://jenkins4.xenostaging.com/job/xenomedia/job/pan-flow/view/change-requests/](https://jenkins4.xenostaging.com/job/xenomedia/job/pan-flow/view/change-requests/)
* Once complete, go back to Github and Squash and merge the Pull request
  * This will trigger a Job on Jeknins that will merge the MulitDev to live and flush cache Pantheon
  * There will be a slack alert in #SITENAME-deploys at the start of this process, you will not receive one when it is done.
  * When the merge is complete an alert is sent to #jenkins-ci `ZZ Global Builds » pr_deploy - [buildnumber]-php7a-SITENAME-[branch]-deploy.txt Success after 2 min 6 sec`
* Once the Pull request has been successfully merged and closed, delete the branch from Github and your local
* Test on the live site.

## Referenced Tools

[hub](https://hub.github.com/) is a command-line wrapper for git that makes you better at GitHub.
