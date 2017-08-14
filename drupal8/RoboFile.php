<?php

use Dflydev\DotAccessData\Data;
use Symfony\Component\Yaml\Yaml;
    define("TFK_NETWORK", "xenomedia");
    define("GRUNT_PATH", "web/themes/custom/x/bootstrap");
    define("DUMP_FILE", "dump.sql");
    define("SITENAME", "xeno-media");

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
class RoboFile extends \Robo\Tasks
{
    const COMPOSE_BIN = 'docker-compose';
    const DRUPAL_ROOT = __DIR__ . '/web';
    const BEHAT_BIN = './vendor/bin/behat';
    const TERMINUS_BIN = 'terminus';


  /**
   * Bring containers up, seed files as needed.
   */
  public function start() {
    if (!file_exists('web/sites/default/settings.local.php')
    ) {
      $this->setup();
      $this->say("Missing settings.local.php site now setup, try start again.");
    }
    else {
      $this->_exec('/usr/bin/osascript DockerStart.scpt ' . GRUNT_PATH);
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
      ->remove('mariadb-init/' . DUMP_FILE);
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
     * Run Behat tests.
     */
    public function test()
    {
        $this->taskExec(self::COMPOSE_BIN)
            ->args(['run', 'testphp', self::BEHAT_BIN])
            ->option('colors')
            ->option('format', 'progress')
            ->run();
    }

  /**
   * Backup database from docker site.
   */
  public function backupDb() {
    $this->_exec('docker-compose exec --user=82 mariadb /usr/bin/mysqldump -u drupal --password=drupal drupal > mariadb-init/' . DUMP_FILE);
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
    $this->_exec("docker-compose exec --user=82 php drush --root=/var/www/html/web " . $drush);
  }

    /**
     * Bring containers up, seed files as needed.
     */
    public function up()
    {
        if (!file_exists('mariadb-init/' . DUMP_FILE) ||
            !file_exists('web/sites/default/settings.local.php')
        ) {
            $this->setup();
        }

        $this->_exec(self::COMPOSE_BIN . ' up -d');
    }

    /**
     * Seed database, shim in settings.local.php
     */
    public function setup()
    {
      $this->_exec('cp web/sites/default/default.settings.local.php web/sites/default/settings.local.php');
      $this->npmInstall();
      $this->composerInstall();
      $this->dbSeed();
    }

  /**
   * Build Drush tasks with common arguments.
   * @return $this
   */
  private function npmInstall()
  {
    return $this->taskNpmInstall()
      ->dir(GRUNT_PATH)
      ->run();
  }

  /**
   * Build Drush tasks with common arguments.
   * @return $this
   */
  private function composerInstall()
  {
    $this->taskComposerInstall()->run();
  }

    /**
     * Build Drush tasks with common arguments.
     * @return $this
     */
    private function buildDrushTask()
    {
        return $this->taskDrushStack(self::DRUSH_BIN)
            ->drupalRootDirectory(self::DRUPAL_ROOT);
    }

    /**
     * Build Terminus Command.
     *
     * @param string $command
     * @param array $args
     * @param array $opts
     * @return \Robo\Result
     */
    private function terminusExec($command = '', array $args = [], array $opt = [])
    {
        return $this->taskExec(self::TERMINUS_BIN)
            ->arg($command)
            ->args($args)
            ->option($opt[0], $opt[1])
            ->run();

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
      if (!in_array(TFK_NETWORK, $exists)) {
        $exists[] = TFK_NETWORK;
      }
      $exists = array_values($exists);
      $data->set('services.traefik.networks', $exists);
    }
    $exists = $data->has('networks.'.TFK_NETWORK.'.external.name');
    if (!$exists) {
      $data->set('networks.'.TFK_NETWORK.'.external.name', 'execadv_default');
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
    $exists = $data->has('networks.'.TFK_NETWORK.'.external.name');
    if ($exists) {
      $data->remove('networks.' . TFK_NETWORK);
    }
    $yaml = Yaml::dump($data->export(), 5);

    file_put_contents('../traefik.yml', $yaml);
    $this->_exec("docker-compose -f ../traefik.yml up -d");
  }

  public function dockerClean() {
    $name = $this->confirm("This will remove all containers and volumes. Are you sure?");
    if ($name) {
      $this->_exec('docker-sync-stack clean');
    }
  }

}
