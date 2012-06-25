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
      'description' => ('The name of the test entity.'),
      'type' => 'text_item',
    );
//    $properties['user'] = array(
//      'type' => 'user',
//      'storage field' => 'uid',
//      'description' => t('The associated user.'),
//    );
    return $properties;
  }
}
