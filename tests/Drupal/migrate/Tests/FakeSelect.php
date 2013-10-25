<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeSelect.
 */


namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Query\PlaceholderInterface;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\Query\SelectInterface;

class FakeSelect extends Select {

  /**
   * Contents of the pseudo-database.
   *
   * Keys are table names and values are arrays of rows in the table.
   * Every row there contains all table fields keyed by field name.
   *
   * @code
   * array(
   *   'user' => array(
   *     array(
   *       'uid' => 1,
   *       'name' => 'admin',
   *     ),
   *     array(
   *       'uid' => 2,
   *       'name' => 'alice',
   *     ),
   *   ),
   *   'node' => array(
   *     array(
   *       'nid' => 1,
   *     )
   *   )
   * )
   * @endcode
   *
   * @var array
   */
  protected $databaseContents;

  /**
   * @var array
   */
  protected $conditions = array();

  protected $tables = array();

  protected $fields = array();
  protected $countQuery = FALSE;

  public function __construct($table, $alias, array $database_contents, $conjunction = 'AND') {
    $this->addJoin(NULL, $table, $alias);
    $this->where = new Condition($conjunction);
    $this->having = new Condition($conjunction);
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
        throw new \Exception('The JOIN condition does not contain the alias of the joined table.');
      }
    }
    return $alias;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // @todo: Implement distinct() handling.

    $results = $this->executeJoins();
    $this->resolveConditions($this->where, $results);
    if (!empty($this->order)) {
      usort($results, array($this, 'sortCallback'));
    }
    if (!empty($this->range)) {
      $results = array_slice($results, $this->range['start'], $this->range['length']);
    }
    if ($this->countQuery) {
      $results = array(array(count($results)));
    }
    return new FakeStatement($results);
  }

  /**
   * Create an initial result set by executing the joins.
   *
   * @return array
   */
  protected function executeJoins() {
    // @TODO add support for all_fields.
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
          $joined = FALSE;
          foreach ($this->databaseContents[$table_info['table']] as $candidate_row) {
            if ($row[$table_info['original_field']] == $candidate_row[$table_info['added_field']]) {
              $joined = TRUE;
              $new_rows[] = array_intersect_key($candidate_row, $fields[$table_alias]) + $row;
            }
          }
          if (!$joined && $table_info['join type'] == 'LEFT') {
            $new_rows[] = $fields[$table_alias] + $row;
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
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function countQuery() {
    $query = clone $this;
    return $query->setCountQuery();
  }

  /**
   * Set this query to be a count query.
   */
  protected function setCountQuery() {
    $this->countQuery = TRUE;
    return $this;
  }

  /**
   * usort callback to order the results.
   */
  protected function sortCallback($a, $b) {
    foreach ($this->order as $field => $direction) {
      if ($a[$field] != $b[$field]) {
        return (($a[$field] < $b[$field]) == ($direction == 'ASC')) ? -1 : 1;
      }
    }
    return 0;
  }

  /**
   * Resolves conditions by removing non-matching rows.
   *
   * @param array $rows
   */
  protected function resolveConditions(Condition $condition_group, array &$rows) {
    foreach ($rows as $k => $row) {
      if (!$this->matchGroup($row, $condition_group)) {
        unset($rows[$k]);
      }
    }
  }

  /**
   * Match a row against a group of conditions.
   *
   * @param array $row
   * @param \Drupal\Core\Database\Query\Condition $condition_group
   * @return bool
   */
  protected function matchGroup(array $row, Condition $condition_group) {
    $conditions = $condition_group->conditions();
    $and = $conditions['#conjunction'] == 'AND';
    unset($conditions['#conjunction']);
    $match = TRUE;
    foreach ($conditions as $condition) {
      $match = $condition['field'] instanceof Condition ? $this->matchGroup($row, $condition['field']) : $this->matchSingle($row, $condition);
      // For AND, finish matching on the first fail. For OR, finish on first
      // success.
      if ($and != $match) {
        break;
      }
    }
    return $match;
  }

  /**
   * @param array $row
   *   The row to match.
   * @param array $condition
   *   An array representing a single condition.
   * @return bool
   *   TRUE if the condition matches.
   */
  protected function matchSingle(array $row, array $condition) {
    switch ($condition['operator']) {
      case '=': return $row[$condition['field']] == $condition['value'];
      case '<=': return $row[$condition['field']] <= $condition['value'];
      case '>=': return $row[$condition['field']] >= $condition['value'];
      case '!=': return $row[$condition['field']] != $condition['value'];
      case '<>': return $row[$condition['field']] != $condition['value'];
      case '<': return $row[$condition['field']] < $condition['value'];
      case '>': return $row[$condition['field']] > $condition['value'];
      case 'IN': return in_array($row[$condition['field']], $condition['value']);
      case 'IS NULL': return !isset($row[$condition['field']]);
      case 'IS NOT NULL': return isset($row[$condition['field']]);
      default: throw new \Exception(sprintf('operator %s is not supported', $condition['operator']));
    }
  }

  public function orderBy($field, $direction = 'ASC') {
    $this->order[$field] = strtoupper($direction);
    return $this;
  }

  // ================== we could support these.

  public function groupBy($field) {
    // @todo: Implement groupBy() method.
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function havingCondition($field, $value = NULL, $operator = NULL) {
    // @todo: Implement havingCondition() method.
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function uniqueIdentifier() {
    // TODO: Implement uniqueIdentifier() method.
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  // ================== the rest won't be supported, ever.

  public function nextPlaceholder() {
    // TODO: Implement nextPlaceholder() method.
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function isPrepared() {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function preExecute(SelectInterface $query = NULL) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function where($snippet, $args = array()) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function extend($extender_name) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function &getExpressions() {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function &getGroupBy() {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function &getUnion() {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function forUpdate($set = TRUE) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function rightJoin($table, $alias = NULL, $condition = NULL, $arguments = array()) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function &conditions() {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function orderRandom() {
    // We could implement this but why bother.
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function union(SelectInterface $query, $type = '') {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function addExpression($expression, $alias = NULL, $arguments = array()) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function &getTables() {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function getArguments(PlaceholderInterface $queryPlaceholder = NULL) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function &getOrderBy() {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function &getFields() {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function exists(SelectInterface $select) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function notExists(SelectInterface $select) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function arguments() {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function compile(Connection $connection, PlaceholderInterface $queryPlaceholder) {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

  public function compiled() {
    throw new \Exception(sprintf('Method "%s" is not supported', __METHOD__));
  }

}
