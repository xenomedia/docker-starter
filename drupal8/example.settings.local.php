<?php
 /**
 * @file
 * settings.local.php (Drupal 8.x)
 *
 * This settings file is intended to contain settings specific to this local
 * environment, by overriding options set in settings.php.
 *
 * Include this file from your regular settings.php by including this at the
 * bottom:
 *
 *   @include('settings.local.php');
 *
 * Placing this at the very end of settings.php will allow you override all
 * options that are set there. Prefixing it with the @ suppresses warnings if
 * the settings.local.php file is missing, so you can commit this to your repo.
 */

/**
 * Trusted host configuration.
 */
$settings['trusted_host_patterns'] = array(
  '^SITENAME\.test$',
);

/**
 * Database configuration.
 */
$databases['default']['default'] = array(
  'database' => 'drupal',
  'username' => 'drupal',
  'password' => 'drupal',
  'prefix' => '',
  'host' => 'mariadb',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

/**
 * Location of the site configuration files.
 */
$config_directories[CONFIG_SYNC_DIRECTORY] = '../config/sync';

/**
 * Private file path:
 *
 * A local file system path where private files will be stored. This directory
 * must be absolute, outside of the Drupal installation directory and not
 * accessible over the web.
 *
 * Note: Caches need to be cleared when this value is changed to make the
 * private:// stream wrapper available to the system.
 *
 * See https://www.drupal.org/documentation/modules/file for more information
 * about securing private files.
 */
# $settings['file_private_path'] = '';

/**
 * Uncomment the environment you are using.
 */
@include('settings.dev.php');
# @include('settings.stage.php');
# @include('settings.prod.php');

