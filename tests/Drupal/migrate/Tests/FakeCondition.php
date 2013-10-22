<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeCondition.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Query\PlaceholderInterface;

class FakeCondition extends Condition {

  protected $conjunction;

  public function __construct($conjunction = 'AND') {
    $this->conjuction = $conjunction;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function compile(Connection $connection, PlaceholderInterface $queryPlaceholder) {
    throw new \Exception('Fake conditions can not be compiled.');
  }

  /**
   * {@inheritdoc}
   */
  public function conditionGroupFactory($conjunction = 'AND') {
    return new FakeCondition($conjunction);
  }

  /**
   * Resolves conditions by removing non-matching rows.
   *
   * @param array $rows
   */
  public function resolve(array &$rows) {
    foreach ($rows as $k => $row) {
      if (!$this->matchRow($row, $this)) {
        unset($rows[$k]);
      }
    }
  }

  /**
   * Match a row against a group of conditions.
   *
   * @param array $row
   * @param FakeCondition $condition_group
   * @return bool
   */
  protected function matchRow(array $row, FakeCondition $condition_group) {
    $and = $condition_group->conjuction == 'AND';
    $match = TRUE;
    foreach ($condition_group->conditions as $condition) {
      $match = $this->match($row, $condition);
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
   * @param array|FakeCondition $condition
   *   Either a condition group or an array representing a condition.
   * @return bool
   *   TRUE if the condition matches.
   */
  protected function match(array $row, $condition) {
    if ($condition instanceof FakeCondition) {
      return $this->matchRow($row, $condition);
    }
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
}
