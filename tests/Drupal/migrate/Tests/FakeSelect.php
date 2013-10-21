<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeSelect.
 */


namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\PlaceholderInterface;
use Drupal\Core\Database\Query\SelectInterface;

class FakeSelect implements SelectInterface {

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
    $this->databaseContents = $database_contents;
  }

  public function join($table, $alias = NULL, $condition = NULL, $arguments = array()) {
    return $this->addJoin('INNER', $table, $alias, $condition, $arguments);
  }

  public function innerJoin($table, $alias = NULL, $condition = NULL, $arguments = array()) {
    return $this->addJoin('INNER', $table, $alias, $condition, $arguments);
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
    if (empty($alias)) {
      $alias = $table;
    }
    $alias_candidate = $alias;
    $count = 2;
    while (!empty($this->tables[$alias_candidate])) {
      $alias_candidate = $alias . '_' . $count++;
    }
    $alias = $alias_candidate;

    if (is_string($condition)) {
      $condition = str_replace('%alias', $alias, $condition);
    }

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

  public function condition($field, $value = NULL, $operator = NULL) {
    if (!isset($operator)) {
      $operator = is_array($value) ? 'IN' : '=';
    }
    $this->conditions[] = array(
      'field' => $field,
      'value' => $value,
      'operator' => $operator,
    );
    return $this;
  }

  public function execute() {
    $fields = array();
    foreach ($this->fields as $field_info) {
      $table_alias = $field_info['table'];
      $fields[$table_alias][$field_info['field']] = NULL;
    }

    $results = array();
    foreach ($this->getTables() as $table_alias => $table_info) {
      if (isset($table_info['join type'])) {
        $new_rows = array();
        foreach ($results as $row) {
          foreach ($this->databaseContents[$table_info['table']] as $candidate_row) {
            if ($row[$table_info['original_field']] == $candidate_row[$table_info['added_field']]) {
              $new_rows[] = array_intersect_key($fields[$table_alias], $candidate_row);
            }
            elseif ($table_info['join type'] == 'LEFT') {
              $new_rows[] = $fields[$table_alias];
            }
          }
        }
        $results = array_merge($results, $new_rows);
      }
      else {
        foreach ($this->databaseContents[$table_info['table']] as $candidate_row) {
          $results[] = array_intersect_key($fields[$table_alias], $candidate_row);
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

  public function __clone() {
    // Nothing to do here.
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


  public function addTag($tag) {
    $this->alterTags[$tag] = 1;
    return $this;
  }

  public function hasTag($tag) {
    return isset($this->alterTags[$tag]);
  }

  public function hasAllTags() {
    return !(boolean)array_diff(func_get_args(), array_keys($this->alterTags));
  }

  public function hasAnyTag() {
    return (boolean)array_intersect(func_get_args(), array_keys($this->alterTags));
  }

  public function addMetaData($key, $object) {
    $this->alterMetaData[$key] = $object;
    return $this;
  }

  public function getMetaData($key) {
    return isset($this->alterMetaData[$key]) ? $this->alterMetaData[$key] : NULL;
  }

  public function addField($table_alias, $field, $alias = NULL) {
    if (empty($alias)) {
      $alias = $field;
    }

    // If that's already in use, try the table name and field name.
    if (!empty($this->fields[$alias])) {
      $alias = $table_alias . '_' . $field;
    }

    // If that is already used, just add a counter until we find an unused alias.
    $alias_candidate = $alias;
    $count = 2;
    while (!empty($this->fields[$alias_candidate])) {
      $alias_candidate = $alias . '_' . $count++;
    }
    $alias = $alias_candidate;

    $this->fields[$alias] = array(
      'field' => $field,
      'table' => $table_alias,
      'alias' => $alias,
    );

    return $alias;
  }

  public function fields($table_alias, array $fields = array()) {
    if ($fields) {
      foreach ($fields as $field) {
        $this->addField($table_alias, $field);
      }
    }
    else {
      // @TODO add support for all_fields.
      throw new \Exception('All fields are not supported');
      $this->tables[$table_alias]['all_fields'] = TRUE;
    }

    return $this;
  }

  // ================== we should support these.

  public function orderBy($field, $direction = 'ASC') {
    // TODO: Implement orderBy() processing.
    throw new \Exception('This method is not supported');
    $this->order[$field] = $direction;
    return $this;
  }

  public function range($start = NULL, $length = NULL) {
    // TODO: Implement range() method.
    throw new \Exception('This method is not supported');
  }

  public function isNull($field) {
    // TODO: Implement isNull() method.
  }

  public function isNotNull($field) {
    // TODO: Implement isNotNull() method.
  }

  public function notExists(SelectInterface $select) {
    // TODO: Implement notExists() method.
  }

  public function distinct($distinct = TRUE) {
    // @todo: Implement distinct() method.
    throw new \Exception('This method is not supported');
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

  public function conditionGroupFactory($conjunction = 'AND') {
    // TODO: Implement conditionGroupFactory() method.
    throw new \Exception('This method is not supported');
  }

  public function andConditionGroup() {
    // TODO: Implement andConditionGroup() method.
    throw new \Exception('This method is not supported');
  }

  public function orConditionGroup() {
    // TODO: Implement orConditionGroup() method.
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
