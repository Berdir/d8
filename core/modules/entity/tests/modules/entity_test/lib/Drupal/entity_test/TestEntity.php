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
   * The entity ID.
   *
   * @var integer
   */
  public $id;

  /**
   * The entity UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The name of the test entity.
   *
   * @var EntityPropertyInterface
   */
  public $name;

  /**
   * The associated user.
   *
   * @var EntityPropertyInterface
   */
  public $user;


  public function __construct(array $values, $entity_type) {
    // @todo: Move to the general entity class once all entity types are
    // converted.
    parent::__construct($values, $entity_type);

    // @todo: Should we unset defined properties or initialize all entity
    // property objects here, so we have the magic getter working with
    // properties defined in the entity class.
    unset($this->id);
    unset($this->uuid);
    unset($this->name);
    unset($this->user);
  }

  /**
   * Overrides Entity::id().
   */
  public function id() {
    return $this->get('id')->value;
  }

  /**
   * Magic getter: Gets the property in default language.
   *
   * @todo: Move to Entity class and interface.
   */
  public function __get($name) {
    if ($this->getPropertyDefinition($name)) {
      return $this->get($name);
    }
    return isset($this->$name) ? $this->$name : NULL;
  }

  /**
   * Magic getter: Sets the property in default language.
   *
   * @todo: Move to Entity class and interface.
   */
  public function __set($name, $value) {
    if ($this->getPropertyDefinition($name)) {
      $this->set($name, $value);
    }
    else {
      $this->$name = $value;
    }
  }
}
