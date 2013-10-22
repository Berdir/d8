<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeCondition.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Query\Condition;

class FakeCondition extends Condition {

  protected $conjuction;

  public function __construct($conjuction = 'AND') {
    $this->conjuction = $conjuction;
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
  public function conditionGroupFactory($conjunction = 'AND') {
    return new FakeCondition($conjunction);
  }

  public function resolve(array &$all_rows) {
    foreach ($this->conditions as $condition) {
      foreach ($all_rows as $k => $row) {
        if (!$this->match($row, $condition)) {
          unset($all_rows[$k]);
        }
      }
    }
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
}
