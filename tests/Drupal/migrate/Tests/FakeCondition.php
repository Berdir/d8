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

}
