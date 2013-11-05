<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\source\d6\TestComment.
 */


namespace Drupal\migrate\Tests\source\d6;

use Drupal\Core\Database\Connection;
use Drupal\migrate\Plugin\migrate\source\d6\Comment;

class TestComment extends Comment {
  function setDatabase(Connection $database) {
    $this->database = $database;
  }
}
