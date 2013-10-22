<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeSelect.
 */


namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\PlaceholderInterface;
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
  protected $conditions = array();

  protected $tables = array();

  protected $fields = array();
  protected $alterMetaData;
  protected $alterTags;

  public function __construct($table, $alias, array $database_contents) {
    $options['return'] = Database::RETURN_STATEMENT;
    $this->addJoin(NULL, $table, $alias);
    $this->where = new FakeCondition;
    $this->having = new FakeCondition;
    $this->databaseContents = $database_contents;
  }

  public function leftJoin($table, $alias = NULL, $condition = NULL, $arguments = array()) {
    return $this->addJoin('LEFT', $table, $alias, $condition, $arguments);
  }

  /**
   * {@#inheritdoc}
   */
  public function addJoin($type, $table, $alias = NULL, $condition = NULL, $arguments = array()) {
    if ($table instanceof SelectInterface) {
      // @todo implement this.
      throw new \Exception('Subqueries are not supported at this moment.');
    }
    $alias = parent::addJoin($type, $table, $alias, $condition, $arguments);

    $this->tables[$alias] = array(
      'join type' => $type,
      'table' => $table,
      'alias' => $alias,
      'condition' => $condition,
      'arguments' => $arguments,
    );
    if (isset($type)) {
      if ($type != 'INNER' && $type != 'LEFT') {
        throw new \Exception(sprintf('%s type not supported, only INNER and LEFT.',$type));
      }
      if (!preg_match('/(\w+)\.(\w+)\s*=\s*(\w+)\.(\w+)/', $condition, $matches)) {
        throw new \Exception('Only x.field1 = y.field2 conditions are supported.'. $condition);
      }
      if ($matches[1] == $alias) {
        $this->tables[$alias] += array(
          'added_field' => $matches[2],
          'original_table_alias' => $matches[3],
          'original_field' => $matches[4],
        );
      }
      elseif ($matches[3] == $alias) {
        $this->tables[$alias] += array(
          'added_field' => $matches[4],
          'original_table_alias' => $matches[1],
          'original_field' => $matches[2],
        );
      }
      else {
        throw new \Exception('The added table is not joined.');
      }
    }
    return $alias;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // @TODO add support for all_fields.
    // @TODO: Implement orderBy() processing.
    // @TODO: Implement range() processing.
    // @todo: Implement distinct() handling.

    $fields = array();
    foreach ($this->fields as $field_info) {
      $table_alias = $field_info['table'];
      $fields[$table_alias][$field_info['field']] = NULL;
    }

    $results = array();
    foreach ($this->tables as $table_alias => $table_info) {
      if (isset($table_info['join type'])) {
        $new_rows = array();
        foreach ($results as $row) {
          foreach ($this->databaseContents[$table_info['table']] as $candidate_row) {
            if ($row[$table_info['original_field']] == $candidate_row[$table_info['added_field']]) {
              $new_rows[] = $row + array_intersect_key($candidate_row, $fields[$table_alias]);
            }
            elseif ($table_info['join type'] == 'LEFT') {
              $new_rows[] = $row + $fields[$table_alias];
            }
          }
        }
        $results = $new_rows;
      }
      else {
        foreach ($this->databaseContents[$table_info['table']] as $candidate_row) {
          $results[] = array_intersect_key($candidate_row, $fields[$table_alias]);
        }
      }
    }

    foreach ($this->conditions as $condition) {
      foreach ($results as $k => $row) {
        if (!$this->match($row, $condition)) {
          unset($results[$k]);
        }
      }
    }
    return new FakeStatement($results);
  }

  protected function match($row, $condition) {
    switch ($condition['operator']) {
      case '=': return $row[$condition['field']] == $condition['value'];
      case '<=': return $row[$condition['field']] <= $condition['value'];
      case '>=': return $row[$condition['field']] >= $condition['value'];
      case '!=': return $row[$condition['field']] != $condition['value'];
      case '<>': return $row[$condition['field']] != $condition['value'];
      case '<': return $row[$condition['field']] < $condition['value'];
      case '>': return $row[$condition['field']] > $condition['value'];
      case 'IN': return in_array($row[$condition['field']], $condition['value']);
      default: throw new \Exception(sprintf('operator %s is not supported', $condition['operator']));
    }
  }

  public function conditionGroupFactory($conjunction = 'AND') {
    return new FakeCondition($conjunction);
  }

  // ================== we could support these.

  public function nextPlaceholder() {
    // TODO: Implement nextPlaceholder() method.
    throw new \Exception('This method is not supported');
  }

  public function groupBy($field) {
    // @todo: Implement groupBy() method.
    throw new \Exception('This method is not supported');
  }

  public function countQuery() {
    // @todo: Implement countQuery() method.
    throw new \Exception('This method is not supported');
  }

  public function havingCondition($field, $value = NULL, $operator = NULL) {
    // @todo: Implement havingCondition() method.
    throw new \Exception('This method is not supported');
  }

  public function uniqueIdentifier() {
    // TODO: Implement uniqueIdentifier() method.
    throw new \Exception('This method is not supported');
  }

  // ================== the rest won't be supported, ever.

  public function isPrepared() {
    throw new \Exception('This method is not supported');
  }

  public function preExecute(SelectInterface $query = NULL) {
    throw new \Exception('This method is not supported');
  }

  public function where($snippet, $args = array()) {
    throw new \Exception('This method is not supported');
  }

  public function extend($extender_name) {
    throw new \Exception('This method is not supported');
  }

  public function &getExpressions() {
    throw new \Exception('This method is not supported');
  }

  public function &getGroupBy() {
    throw new \Exception('This method is not supported');
  }

  public function &getUnion() {
    throw new \Exception('This method is not supported');
  }

  public function forUpdate($set = TRUE) {
    throw new \Exception('This method is not supported');
  }

  public function rightJoin($table, $alias = NULL, $condition = NULL, $arguments = array()) {
    throw new \Exception('This method is not supported');
  }

  public function &conditions() {
    throw new \Exception('This method is not supported');
  }

  public function orderRandom() {
    // We could implement this but why bother.
    throw new \Exception('This method is not supported');
  }

  public function union(SelectInterface $query, $type = '') {
    throw new \Exception('This method is not supported');
  }

  public function addExpression($expression, $alias = NULL, $arguments = array()) {
    throw new \Exception('This method is not supported');
  }

  public function &getTables() {
    throw new \Exception('This method is not supported');
  }

  public function getArguments(PlaceholderInterface $queryPlaceholder = NULL) {
    throw new \Exception('This method is not supported');
  }

  public function &getOrderBy() {
    throw new \Exception('This method is not supported');
  }

  public function &getFields() {
    throw new \Exception('This method is not supported');
  }

  public function exists(SelectInterface $select) {
    throw new \Exception('This method is not supported');
  }

  public function notExists(SelectInterface $select) {
    throw new \Exception('This method is not supported');
  }

  public function arguments() {
    throw new \Exception('This method is not supported');
  }

  public function compile(Connection $connection, PlaceholderInterface $queryPlaceholder) {
    throw new \Exception('This method is not supported');
  }

  public function compiled() {
    throw new \Exception('This method is not supported');
  }

}
