<?php
 /**
 * @file
 * local.settings.php (Drupal 7.x)
 *
 * This settings file is intended to contain settings specific to this local
 * environment, by overriding options set in settings.php.
 *
 * Include this file from your regular settings.php by including this at the
 * bottom:
 *
 *   @include('local.settings.php');
 *
 * Placing this at the very end of settings.php will allow you override all
 * options that are set there. Prefixing it with the @ suppresses warnings if
 * the local.settings.php file is missing, so you can commit this to your repo.
 */


// The name of the database. This will also be used as the memcache prefix.

// Database configuration.
$databases = array (
  'default' =>
  array (
    'default' =>
    array (
      'database' => 'drupal',
      'username' => 'drupal',
      'password' => 'drupal',
      'host' => 'mariadb',
      'port' => '',
      'driver' => 'mysql',
      'prefix' => '',
    ),
  ),
);

// Toggle the use of memcache.
$_use_memcache = FALSE;

// Toggle the output of devel debugging/logging.
$_use_devel = FALSE;

// Path where all contrib modules can be found.
$_contrib_path = 'sites/all/modules/contrib';

// Uncomment the line for the environment this is
@include('dev.settings.inc');
# @include('stage.settings.inc');
# @include('prod.settings.inc');

// Custom settings
# $conf['maintenance_theme'] = 'bartik';
$base_url = 'https://SITENAME.test';
$_SERVER['HTTPS'] = 'on';