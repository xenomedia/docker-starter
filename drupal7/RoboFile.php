<?php

use Dflydev\DotAccessData\Data;
use Symfony\Component\Yaml\Yaml;

/**
 * Created by PhpStorm.
 *
 * User: michaelpporter
 * Date: 7/17/17
 * Time: 8:41 AM
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks {

  const COMPOSE_BIN = 'docker-compose';
  const DRUPAL_ROOT = __DIR__ . '/www';
  const DUMP_FILE = __DIR__ . '/dump.sql';
  const BEHAT_BIN = './vendor/bin/behat';
  const SITENAME = 'execadv';


  /**
   * Bring containers up, seed files as needed.
   */
  public function start() {
    if (!file_exists('www/.htaccess') ||
      !file_exists('www/sites/default/local.settings.php')
    ) {
      $this->say("Missing .htaccess or local.setting.php");
    }
    else {
      $this->_exec('/usr/bin/osascript DockerStart.scpt');
      $this->tfkSetup();
    }
  }

  /**
   * Backup Database, Stop containers and cleanup network.
   */
  public function stop() {
    $this->backupDb();
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
   * Move backup to mariadb-init for initial load.
   */
  public function dbSeed() {
    $this->taskFilesystemStack()
      ->remove('mariadb-init/dump.sql');
    $this->backupGet();
  }

  /**
   * Pull live database from last nights backup.
   */
  public function backupGet() {
    $this->_exec('getdb.sh ' . SITENAME);
    $this->_exec('mv ~/dbback/' . SITENAME . '.sql mariadb-init/' . DUMP_FILE);
  }

  /**
   * Run Behat tests. (not complete)
   */
  public function test() {
    $this->taskExec(self::COMPOSE_BIN)
      ->args(['exec', 'testphp', self::BEHAT_BIN])
      ->option('colors')
      ->option('format', 'progress')
      ->run();
  }

  /**
   * Backup database from docker site.
   */
  public function backupDb() {
    $this->_exec('docker-compose exec --user=82 mariadb /usr/bin/mysqldump -u drupal --password=drupal drupal > mariadb-init/dump.sql');
  }

  /**
   * Run drush commands.
   *
   * Sample commands:
   *  robo drush 'cc all'
   *  robo drush 'en module -y'
   *  robo drush 'cex -y'
   *
   * @param string $drush
   *   Drush command to run, in quotes.
   */
  public function drush($drush) {
    $this->_exec("docker-compose exec --user=82 php drush --root=/var/www/html/www " . $drush);
  }

  /**
   * Open Deploy Page.
   */
  public function jenkins() {
    $this->_exec('open https://jenkins4.xenostaging.com/job/drupal-7/job/execadv-multi-branch/job/master/');
  }

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

  /**
   * Add network from treafik and restart.
   */
  public function tfkSetup() {

    $yml = Yaml::parse(file_get_contents('../traefik.yml'));
    $data = new Data($yml);

    $exists = $data->get('services.traefik.networks');
    if ($exists) {
      if (!in_array('execadv', $exists)) {
        $exists[] = 'execadv';
      }
      $exists = array_values($exists);
      $data->set('services.traefik.networks', $exists);
    }
    $exists = $data->has('networks.execadv.external.name');
    if (!$exists) {
      $data->set('networks.execadv.external.name', 'execadv_default');
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
      if (($key = array_search('execadv', $exists)) !== FALSE) {
        unset($exists[$key]);
      }
      $exists = array_values($exists);
      $data->set('services.traefik.networks', $exists);
    }
    $exists = $data->has('networks.execadv.external.name');
    if ($exists) {
      $data->remove('networks.execadv');
    }
    $yaml = Yaml::dump($data->export(), 5);

    file_put_contents('../traefik.yml', $yaml);
    $this->_exec("docker-compose -f ../traefik.yml up -d");
  }

  /**
   * Watch files for change and clear cache.
   */
  public function watch() {
    $this->taskWatch()
      ->monitor('www/sites/all/modules/custom/digital_adoption_index/css/digital_adoption_index_reports.css', function () {
        $this->_exec('docker-compose exec --user=82 php drush --root=/var/www/html/www cc css-js');
      })->run();
  }

  public function dockerClean() {
    $name = $this->confirm("This will remove all containers and volumes. Are you sure?");
    if ($name) {
      $this->_exec('docker-sync-stack clean');
    }
  }
}
