<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeInsert.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Query\Insert;
use Drupal\Core\Database\Query\SelectInterface;

class FakeInsert extends Insert {

  /**
   * @var array
   */
  protected $databaseContents;

  /**
   * @var string
   */
  protected $table;

  /**
   * Constructs a fake insert object.
   * @param \Drupal\Core\Database\Connection $database_contents
   * @param string $table
   * @param array $options
   */
  public function __construct(&$database_contents, $table, array $options = array()) {
    $this->databaseContents = &$database_contents;
    $this->table = $table;
  }

  /**
   * {@inheritdoc}
   */
  public function useDefaults(array $fields) {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@inheritdoc}
   */
  public function from(SelectInterface $query) {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    foreach ($this->insertValues as $values) {
      $this->databaseContents[$this->table][] = array_combine($this->insertFields, $values);
    }
  }


}
