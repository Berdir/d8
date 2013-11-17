<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeMerge.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Query\InvalidMergeQueryException;
use Drupal\Core\Database\Query\Merge;

class FakeMerge extends Merge {


  function __construct(&$database_contents, $table) {
    $this->databaseContents = &$database_contents;
    $this->table = $table;
    $this->condition = new Condition('AND');
  }

  public function execute() {
    if (!count($this->condition)) {
      throw new InvalidMergeQueryException(t('Invalid merge query: no conditions'));
    }
    if (!empty($this->databaseContents[$this->table])) {
      $first_row = reset($this->databaseContents[$this->table]);
      list($field, ) = each($first_row);
      $select = new Fakeselect($this->conditionTable, 'c', $this->databaseContents);
      $select
        ->fields('c', array($field))
        ->condition($this->condition)
        ->countQuery();
      if ($select->execute()->fetchField()) {
        $update = new FakeUpdate($this->databaseContents, $this->table);
        $update
          ->fields($this->updateFields)
          ->condition($this->condition)
          ->execute();
        return self::STATUS_UPDATE;
      }
    }
    $insert = new FakeInsert($this->databaseContents, $this->table);
    $insert->fields($this->insertFields)->execute();
    return self::STATUS_INSERT;
  }
}
