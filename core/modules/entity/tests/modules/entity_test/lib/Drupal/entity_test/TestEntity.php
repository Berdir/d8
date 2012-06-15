<?php

/**
 * @file
 * Entity class for the 'entity_test' type.
 */

namespace Drupal\entity_test;

use Drupal\entity\Entity;
use Drupal\entity\EntityProperty;
use Drupal\entity\EntityPropertyItem;

/**
 * Defines the node entity class.
 */
class TestEntity extends Entity {

  protected $name;
  protected $uid;

  protected $properties = array();

  public function getPropertyDefinitions() {
    $properties['id'] = array(
      'type' => 'integer',
      'not null' => TRUE,
      'description' => t('Unique entity-test item ID.'),
    );
    $properties['name'] = array(
      'description' => ('The name of the test entity.'),
      'type' => 'string',
    );
    $properties['user'] = array(
      'type' => 'user',
      'storage field' => 'uid',
      'description' => t('The associated user.'),
    );
    $properties['langcode'] = array(
      'description' => t('The langcode of the test entity.'),
      'type' => 'string',
    );
    return $properties + parent::getPropertyDefinitions();
  }

  public function __get($name) {

    if (isset($this->properties[$name])) {
      return $this->properties[$name];
    }

    $property_definitions = $this->getPropertyDefinitions();
    if (isset($property_definitions[$name])) {
      $storage_field = isset($property_definitions[$name]['storage field']) ? $property_definitions[$name]['storage field'] : $name;
      $values = array('value' => &$this->$storage_field);
      $item_class = 'Drupal\entity\EntityPropertyItem';
      if ($name == 'user') {
        $item_class = 'Drupal\entity\EntityPropertyItemUser';
      }

      $property_item = new $item_class($values);
      $property = new EntityProperty(array($property_item));
      $this->properties[$name] = $property;
      return $property;
    }
  }
}
