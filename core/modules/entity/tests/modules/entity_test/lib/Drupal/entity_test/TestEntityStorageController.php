<?php
/**
 * @file
 * Definition of Drupal\entity_test\TestEntityStorageController.
 */

namespace Drupal\entity_test;

/**
 * Storage controller for test entities.
 */
class TestEntityStorageController extends \Drupal\entity\DatabaseStorageController {

  /**
   * Implements \Drupal\entity\EntityStorageControllerInterface.
   */
  public function basePropertyDefinitions() {
    $properties['name'] = array(
      'label' => t('Name'),
      'description' => ('The name of the test entity.'),
      'type' => 'text_item',
    );
    $properties['user'] = array(
      'type' => 'entityreference_item',
      'entity type' => 'user',
      'label' => t('User'),
      'description' => t('The associated user.'),
    );
    return $properties;
  }
}
