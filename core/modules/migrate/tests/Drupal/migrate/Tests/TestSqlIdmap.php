<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\TestSqlIdmap.
 */

namespace Drupal\migrate\Tests;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\id_map\Sql;

class TestSqlIdmap extends Sql {

  function __construct($database, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration) {
    $this->database = $database;
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  public function getDatabase() {
    return parent::getDatabase();
  }
}
