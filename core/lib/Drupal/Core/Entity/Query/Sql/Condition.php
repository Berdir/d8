<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Query\Sql\Condition.
 */

namespace Drupal\Core\Entity\Query\Sql;

use Drupal\Core\Entity\Query\ConditionBase;
use Drupal\Core\Entity\Query\ConditionInterface;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\Condition as SqlCondition;

/**
 * Implements entity query conditions for SQL databases.
 */
class Condition extends ConditionBase {

  /**
   * The SQL entity query object this condition belongs to.
   *
   * @var \Drupal\Core\Entity\Query\Sql\Query
   */
  protected $query;

  /**
   * {@inheritdoc}
   */
  public function compile($conditionContainer) {
    // If this is not the top level condition group then the sql query is
    // added to the $conditionContainer object by this function itself. The
    // SQL query object is only necessary to pass to Query::addField() so it
    // can join tables as necessary. On the other hand, conditions need to be
    // added to the $conditionContainer object to keep grouping.
    $sql_query = $conditionContainer instanceof SelectInterface ? $conditionContainer : $conditionContainer->sqlQuery;
    $tables = $this->query->getTables($sql_query);
    foreach ($this->conditions as $condition) {
      if ($condition['field'] instanceOf ConditionInterface) {
        $sql_condition = new SqlCondition($condition['field']->getConjunction());
        // Add the SQL query to the object before calling this method again.
        $sql_condition->sqlQuery = $sql_query;
        $condition['field']->compile($sql_condition);
        $sql_query->condition($sql_condition);
      }
      else {
        $type = strtoupper($this->conjunction) == 'OR' || $condition['operator'] == 'IS NULL' ? 'LEFT' : 'INNER';
        $field = $tables->addField($condition, $type);
        $this->translateCondition($condition, $sql_query);
        $conditionContainer->condition($field, $condition['value'], $condition['operator']);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function exists($field, $langcode = NULL) {
    return $this->condition($field, NULL, 'IS NOT NULL', $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function notExists($field, $langcode = NULL) {
    return $this->condition($field, NULL, 'IS NULL', $langcode);
  }

  /**
   * Translates the string operators to SQL equivalents.
   *
   * @param array $condition
   */
  protected function translateCondition(&$condition, SelectInterface $sql_query) {
    // There is nothing we can do for IN ().
    if (is_array($condition['value'])) {
      return;
    }
    switch ($condition['operator']) {
      case '=':
        if (empty($condition['case sensitive'])) {
          $condition['value'] = $sql_query->escapeLike($condition['value']);
          $condition['operator'] = 'LIKE';
        }
        break;
      case '<>':
        if (empty($condition['case sensitive'])) {
          $condition['value'] = $sql_query->escapeLike($condition['value']);
          $condition['operator'] = 'NOT LIKE';
        }
        break;
      case 'STARTS_WITH':
        $condition['value'] = $sql_query->escapeLike($condition['value']) . '%';
        $condition['operator'] = 'LIKE';
        break;

      case 'CONTAINS':
        $condition['value'] = '%' . $sql_query->escapeLike($condition['value']) . '%';
        $condition['operator'] = 'LIKE';
        break;

      case 'ENDS_WITH':
        $condition['value'] = '%' . $sql_query->escapeLike($condition['value']);
        $condition['operator'] = 'LIKE';
        break;
    }
  }

}
