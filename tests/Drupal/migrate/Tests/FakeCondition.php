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
      if (!$this->matchGroup($row, $this)) {
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
  protected function matchGroup(array $row, FakeCondition $condition_group) {
    $and = $condition_group->conjuction == 'AND';
    $match = TRUE;
    foreach ($condition_group->conditions as $condition) {
      $match = $condition['field'] instanceof FakeCondition ? $this->matchGroup($row, $condition['field']) : $this->matchSingle($row, $condition);
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
}
