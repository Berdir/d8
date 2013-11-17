<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeUpdate.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\Update;

class FakeUpdate extends Update {

  /**
   * @var string
   */
  protected $table;

  /**
   * @var array
   */
  protected $databaseContents;

  /**
   * Constructs a FakeUpdate object.
   * @param \Drupal\Core\Database\Connection $database_contents
   * @param string $table
   */
  public function __construct(&$database_contents, $table) {
    $this->table = $table;
    $this->condition = new Condition('AND');
    $this->databaseContents = &$database_contents;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $affected = 0;
    if (count($this->condition) && isset($this->databaseContents[$this->table])) {
      foreach ($this->databaseContents[$this->table] as $key  => $row_array) {
        $row = new DatabaseRow($row_array);
        if (ConditionResolver::matchGroup($row, $this->condition)) {
          $this->databaseContents[$this->table][$key] = $this->fields + $this->databaseContents[$this->table][$key];
          $affected++;
        }
      }
    }
    return $affected;
  }

  /**
   * {@inheritdoc}
   */
  public function exists(SelectInterface $select) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  /**
   * {@inheritdoc}
   */
  public function where($snippet, $args = array()) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  /**
   * {@inheritdoc}
   */
  public function expression($field, $expression, array $arguments = NULL) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }
}
