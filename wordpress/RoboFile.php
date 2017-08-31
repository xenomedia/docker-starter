<?php

use Dflydev\DotAccessData\Data;
use Symfony\Component\Yaml\Yaml;

// Folder name without dashes, xeno-dashboard becomes xenodashboard.
define("TFK_NETWORK", "foldernamenodashes");
// Leave blank if not using grunt.
define("GRUNT_PATH", "");
// Leave as dump.sql.
define("DUMP_FILE", "dump.sql");
// Path to the site root; i.e. '/', '/web' or '/www'.
define("WP_ROOT", __DIR__ . '/');
// Name of the dbbackup file.
define("SITENAME", "obchamber");
define("COMPOSE_BIN", "docker-compose");
// Path to behat, within the project.
define("BEHAT_BIN", "./vendor/bin/behat");
define("TERMINUS_BIN", "terminus");

/**
 * Created by PhpStorm.
 *
 * User: michaelpporter
 * Date: 8/14/17
 * Time: 7:41 AM
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks {

  /**
   * Bring containers up, seed files as needed.
   */
  public function start() {
    if (!file_exists(WP_ROOT . '/wp-config.php')
    ) {
      $this->setup();
      $this->say("Missing wp-config.php site now setup, try start again.");
    }
    else {
      $this->_exec('/usr/bin/osascript DockerStart.scpt ' . GRUNT_PATH);
      $this->dockerNetwork();
    }
  }

  /**
   * Backup Database, Stop containers and cleanup network.
   */
  public function stop() {
    $this->dbBackup();
    $this->halt();
  }

  /**
   * Halt containers and cleanup network.
   */
  public function halt() {
    $this->tfkClean();
    $this->_exec('docker-compose stop');
    $this->_exec('docker-sync stop');
  }

  /**
   * Copy default wp-config.php and get the database from stage.
   */
  public function setup() {
    $this->_exec('cp ' . WP_ROOT . '/default.wp-config.php ' . WP_ROOT . '/wp-config.php');
    $this->dbSeed();
  }

  /**
   * Run Behat tests.
   */
  public function test() {
    $this->taskExec('docker')
      ->args(['run', 'testphp', self::BEHAT_BIN])
      ->option('colors')
      ->option('format', 'progress')
      ->run();
  }

  /**
   * Run wp commands.
   *
   * Sample commands:
   *  robo wp 'wp cache flush'
   *  robo wp 'wp cron test'
   *
   * @param string $wp
   *   WP command to run, in quotes.
   */
  public function wp($wp) {
    $this->_exec("docker exec --user=82 php wp --path=/var/www/html/www " . $wp);
  }

  /* **********************************************************************
   * Database section.
   ********************************************************************** */

  /**
   * Backup database from docker site.
   */
  public function dbBackup() {
    $this->_exec('docker exec --user=82 mariadb /usr/bin/mysqldump -u wordpress --password=wordpress wordpress > mariadb-init/' . DUMP_FILE);
  }

  /**
   * Pull live database from last nights backup.
   */
  public function dbGet() {
    $this->_exec('getdb.sh ' . SITENAME);
    $this->_exec('mv ~/dbback/' . SITENAME . '.sql mariadb-init/' . DUMP_FILE);
  }

  /**
   * Move backup to mariadb-init for initial load.
   */
  public function dbSeed() {
    $this->taskFilesystemStack()
      ->remove('mariadb-init/' . DUMP_FILE);
    $this->dbGet();
  }

  /* **********************************************************************
   * Git section.
   ********************************************************************** */

  /**
   * Cherry pick current branch to master.
   *
   * @return mixed
   *   Value of the collection.
   */
  public function gitCp() {
    $current_branch = exec('git rev-parse --abbrev-ref HEAD');

    $collection = $this->collectionBuilder();
    $collection->taskGitStack()
      ->checkout('master')
      ->exec('git cherry-pick ' . $current_branch)
      ->completion($this->taskGitStack()->push('origin', 'master'))
      ->completion($this->taskGitStack()->checkout($current_branch));

    return $collection;
  }

  /**
   * Publish current branch to master.
   *
   * @return mixed
   *   Value of the collection.
   */
  public function gitPublish() {
    $current_branch = exec('git rev-parse --abbrev-ref HEAD');

    $collection = $this->collectionBuilder();
    $collection->taskGitStack()
      ->checkout('master')
      ->merge($current_branch)
      ->completion($this->taskGitStack()->push('origin', 'master'))
      ->completion($this->taskGitStack()->checkout($current_branch));

    return $collection;
  }

  /**
   * Merge Master with current branch.
   *
   * @return mixed
   *   Value of the collection.
   */
  public function gitMaster() {
    $current_branch = exec('git rev-parse --abbrev-ref HEAD');

    $collection = $this->collectionBuilder();
    $collection->taskGitStack()
      ->checkout('master')
      ->pull('origin', 'master')
      ->checkout($current_branch)
      ->merge('master')
      ->completion($this->taskGitStack()->push('origin', $current_branch));

    return $collection;
  }

  /* **********************************************************************
   * Docker section.
   ********************************************************************** */

  /**
   * Check for Docker network, create if not there.
   */
  public function dockerNetwork() {
    $result = $this->taskExec('docker network ls | grep ' . TFK_NETWORK)->run();
    if ($result->wasSuccessful()) {
      $result->provideOutputdata();
      $this->say($result->wasSuccessful());
      $this->tfkSetup();
    }
    else {
      $this->_exec('docker network create -d bridge ' . TFK_NETWORK . '_default');
      $this->dockerNetwork();
    }
  }

  /**
   * Remove containers and volumes, only when you ar done with the project.
   */
  public function dockerClean() {
    $name = $this->confirm("This will remove all containers and volumes. Are you sure?");
    if ($name) {
      $this->_exec('docker-sync-stack clean');
    }
  }

  /* **********************************************************************
   * Traefik section.
   ********************************************************************** */

  /**
   * Add network from treafik and restart.
   */
  public function tfkSetup() {

    $yml = Yaml::parse(file_get_contents('../traefik.yml'));
    $data = new Data($yml);

    $exists = $data->get('services.traefik.networks');
    if ($exists) {
      if (!in_array(TFK_NETWORK, $exists)) {
        $exists[] = TFK_NETWORK;
      }
      $exists = array_values($exists);
      $data->set('services.traefik.networks', $exists);
    }
    $exists = $data->has('networks.' . TFK_NETWORK . '.external.name');
    if (!$exists) {
      $data->set('networks.' . TFK_NETWORK . '.external.name', TFK_NETWORK . '_default');
    }

    $yaml = Yaml::dump($data->export(), 5);

    file_put_contents('../traefik.yml', $yaml);
    $this->_exec("docker-compose -f ../traefik.yml up -d");
  }

  /**
   * Remove network from treafik and restart.
   */
  public function tfkClean() {
    $yml = Yaml::parse(file_get_contents('../traefik.yml'));
    $data = new Data($yml);
    $exists = $data->get('services.traefik.networks');
    if ($exists) {
      if (($key = array_search(TFK_NETWORK, $exists)) !== FALSE) {
        unset($exists[$key]);
      }
      $exists = array_values($exists);
      $data->set('services.traefik.networks', $exists);
    }
    $exists = $data->has('networks.' . TFK_NETWORK . '.external.name');
    if ($exists) {
      $data->remove('networks.' . TFK_NETWORK . '');
    }
    $yaml = Yaml::dump($data->export(), 5);

    file_put_contents('../traefik.yml', $yaml);
    $this->_exec("docker-compose -f ../traefik.yml up -d");
  }

}
