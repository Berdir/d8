<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Upgrade\MigrateTestBase.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Database;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Row;
use Drupal\simpletest\WebTestBase;

class MigrateTestBase extends WebTestBase implements MigrateMessageInterface {

  /**
   * The file path(s) to the dumped database(s) to load into the child site.
   *
   * @var array
   */
  public $databaseDumpFiles = array();

  public static $modules = array('migrate');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $connection_info = Database::getConnectionInfo('default');
    foreach ($connection_info as $target => $value) {
      $connection_info[$target]['prefix'] = array(
        // Simpletest uses 7 character prefixes at most so this can't cause
        // collisions.
        'default' => $value['prefix']['default'] .'0',
      );
    }
    Database::addConnectionInfo('migrate', 'default', $connection_info['default']);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    Database::removeConnection('migrate');
    parent::tearDown();
  }

  /**
   * @param MigrationInterface $migration
   * @param array $files
   *
   * @return \Drupal\Core\Database\Connection
   */
  protected function prepare(MigrationInterface $migration, array $files = array()) {
    $this->loadDumps($files);
  }

  protected function loadDumps($files) {
    // Load the database from the portable PHP dump.
    // The files may be gzipped.
    foreach ($files as $file) {
      if (substr($file, -3) == '.gz') {
        $file = "compress.zlib://$file";
        require $file;
      }
      preg_match('/^namespace (.*);$/m', file_get_contents($file), $matches);
      $class = $matches[1] . '\\' . basename($file, '.php');
      $class::load(Database::getConnection('default', 'migrate'));
    }
  }

  /**
   * @param array $id_mappings
   *   A list of id mappings keyed by migration ids. Each id mapping is a list
   *   of two arrays, the first are source ids and the second are destination
   *   ids.
   */
  protected function prepareIdMappings(array $id_mappings) {
    /** @var \Drupal\migrate\Entity\MigrationInterface[] $migrations */
    $migrations = entity_load_multiple('migration', array_keys($id_mappings));
    foreach ($id_mappings as $migration_id => $data) {
      $migration = $migrations[$migration_id];
      $id_map = $migration->getIdMap();
      $source_ids = $migration->getSourcePlugin()->getIds();
      foreach ($data as $id_mapping) {
        $row = new Row(array_combine(array_keys($source_ids), $id_mapping[0]), $source_ids);
        $id_map->saveIdMapping($row, $id_mapping[1]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function display($message, $type = 'status') {
    if ($type == 'status') {
      $this->pass($message);
    }
    else {
      $this->fail($message);
    }
  }
}
