<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeTruncate.
 */

namespace Drupal\migrate\Tests;

class FakeTruncate {

  public function __construct(&$database_contents, $table) {
    $this->databaseContents = &$database_contents;
    $this->table = $table;
  }

  public function execute() {
    $this->databaseContents[$this->table] = array();
  }
}
