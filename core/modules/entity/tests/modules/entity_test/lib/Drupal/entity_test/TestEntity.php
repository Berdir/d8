<?php

/**
 * @file
 * Entity class for the 'entity_test' type.
 */

namespace Drupal\entity_test;

use Drupal\entity\Entity;

/**
 * Defines the test entity class.
 */
class TestEntity extends Entity {

  /**
   * The name of the test entity.
   *
   * @var string
   */
  public $name;


  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    // Let the magic work. See parent implementation @todo.
    unset($this->name);
  }

  public function __get($name) {
    return $this->get($name);
  }

  public function __set($name, $value) {
    $this->set($name, $value);
  }
}
