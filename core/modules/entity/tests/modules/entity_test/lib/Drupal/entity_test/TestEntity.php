<?php

/**
 * @file
 * Entity class for the 'entity_test' type.
 */

namespace Drupal\TestEntity;
use Drupal\entity\Entity;

/**
 * Defines the node entity class.
 */
class TestEntity extends Entity {

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
}
