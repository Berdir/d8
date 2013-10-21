<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeSelect.
 */


namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\Query\SelectInterface;

class FakeSelect extends Select {

  /**
   * @var array
   */
  protected $databaseContents;

  /**
   * @var array
   */
  protected $conditions;

  public function __construct($table, $alias, array $database_contents) {
    $options['return'] = Database::RETURN_STATEMENT;
    $this->addJoin(NULL, $table, $alias);
    $this->databaseContents = $database_contents;
  }

  /**
   * {@#inheritdoc}
   */
  public function addJoin($type, $table, $alias = NULL, $condition = NULL, $arguments = array()) {
    if ($table instanceof SelectInterface) {
      // @todo implement this.
      throw new \Exception('Subqueries are not supported at this moment.');
    }
    if (isset($type) && $type != 'INNER' && $type != 'LEFT') {
      throw new \Exception('Only INNER and LEFT joins are supported.');
    }
    $alias = parent::addJoin($type, $table, $alias, $condition, $arguments);
    if (!preg_match('/(\w+)\.(\w+)\s*=\s*(\w+)\.(\w+)/', $condition, $matches)) {
      throw new \Exception('Only x.field1 = y.field2 conditions are supported.');
    }
    if ($matches[1] == $alias) {
      $this->tables[$alias] = array(
        'added_field' => $matches[2],
        'original_table_alias' => $matches[3],
        'original_field' => $matches[4],
      );
    }
    elseif ($matches[3] == $alias) {
      $this->tables[$alias] = array(
        'added_field' => $matches[4],
        'original_table_alias' => $matches[1],
        'original_field' => $matches[2],
      );
    }
    else {
      throw new \Exception('The added table is not joined.');
    }
    return $alias;
  }

  public function condition($field, $value = NULL, $operator = NULL) {
    $this->conditions[] = array(
      'field' => $field,
      'value' => $value,
      'operator' => $operator,
    );
    return $this;
  }

  /**
   * {@#inheritdoc}
   */
  public function &conditions() {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function where($snippet, $args = array()) {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function extend($extender_name) {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function &getExpressions() {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function &getGroupBy() {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function &getUnion() {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function distinct($distinct = TRUE) {
    // @todo: Implement distinct() method.
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function orderRandom() {
    // We could implement this but why bother.
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function union(SelectInterface $query, $type = '') {
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function groupBy($field) {
    // @todo: Implement groupBy() method.
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function countQuery() {
    // @todo: Implement countQuery() method.
    throw new \Exception('This method is not supported');
  }

  /**
   * {@#inheritdoc}
   */
  public function havingCondition($field, $value = NULL, $operator = NULL) {
    // @todo: Implement havingCondition() method.
    throw new \Exception('This method is not supported');
  }

  public function execute() {
    $results = array();
    $fields = array();
    $null_row = array();
    foreach ($this->fields as $field_info) {
      $table_alias = $field_info['table'];
      $fields[$table_alias][$field_info['field']] = TRUE;
      $null_row[$table_alias] = array_combine($field_info['field'], array_fill(0, count($field_info['field']), NULL));
    }

    foreach ($this->getTables() as $table_alias => $table_info) {
      if (isset($table_info['join type'])) {
        $new_rows = array();
        foreach ($results as $row) {
          foreach ($this->databaseContents[$table_alias] as $candidate_row) {
            if ($row[$table_info['original_field']] == $candidate_row[$table_info['added_field']]) {
              $new_rows[] = array_intersect_key($fields[$table_alias], $candidate_row);
            }
            elseif ($table_info['join type'] == 'LEFT') {
              $new_rows[] = $null_row[$table_alias];
            }
          }
        }
        $results = array_merge($results, $new_rows);
      }
      else {
        // @TODO add support for all_fields.
        foreach ($this->databaseContents[$table_alias] as $candidate_row) {
          $results[] = array_intersect_key($fields[$table_alias], $candidate_row);
        }
      }
    }

    
  }

}
