<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\source\d6\TestTerm.
 */


namespace Drupal\migrate\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\migrate\Plugin\migrate\source\d6\Term;

class TestTerm extends Term {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
}
