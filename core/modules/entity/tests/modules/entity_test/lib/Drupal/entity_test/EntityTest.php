<?php

/**
 * @file
 * Definition of Drupal\entity_test\EntityTest.
 */

namespace Drupal\entity_test;

use Drupal\entity\EntityNG;

/**
 * Defines the test entity class.
 */
class EntityTest extends EntityNG {

  /**
   * The entity ID.
   *
   * @var \Drupal\entity\Property\ItemListInterface
   */
  public $id;

  /**
   * The entity UUID.
   *
   * @var \Drupal\entity\Property\ItemListInterface
   */
  public $uuid;

  /**
   * The name of the test entity.
   *
   * @var \Drupal\entity\Property\ItemListInterface
   */
  public $name;

  /**
   * The associated user.
   *
   * @var \Drupal\entity\Property\ItemListInterface
   */
  public $user_id;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

    // We unset all defined properties, so magic getters apply.
    unset($this->id);
    unset($this->langcode);
    unset($this->uuid);
    unset($this->name);
    unset($this->user_id);
  }
}
