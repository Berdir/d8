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
    // @todo: Move to the general entity class once all entity types are
    // converted.
    $this->entityType = $entity_type;

    // @todo: Use dependency injection.
    $this->dataType = drupal_get_property_type_plugin('entity');

    // @todo: Should we unset defined properties or initialize all entity
    // property objects here, so we have the magic getter working with
    // properties defined in the entity class.
    unset($this->name);
    unset($this->user);

    foreach ($values as $name => $value) {
      $this->set($name, $value);
    }
  }

  public function __get($name) {
    return $this->get($name);
  }

  public function __set($name, $value) {
    $this->set($name, $value);
  }
}
