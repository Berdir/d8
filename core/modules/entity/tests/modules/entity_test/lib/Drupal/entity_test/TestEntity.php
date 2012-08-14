<?php

/**
 * @file
 * Definition of Drupal\entity_test\TestEntity.
 */

namespace Drupal\entity_test;

use Drupal\entity\EntityNG;

/**
 * Defines the test entity class.
 */
class TestEntity extends EntityNG {

  /**
   * The entity ID.
   *
   * @var \Drupal\entity\EntityPropertyInterface
   */
  public $uuid;

  /**
   * The name of the test entity.
   *
   * @var \Drupal\entity\EntityPropertyInterface
   */
  public $name;

  /**
   * The associated user.
   *
   * @var \Drupal\entity\EntityPropertyInterface
   */
  public $user;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

    // We unset all defined properties, so magic getters apply.
    unset($this->id);
    unset($this->uuid);
    unset($this->name);
    unset($this->user);
  }
}
