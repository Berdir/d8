<?php

/**
 * @file
 * Definition of Drupal\entity_test\EntityTestStorageController.
 */

namespace Drupal\entity_test;

use PDO;

use Drupal\entity\EntityInterface;
use Drupal\entity\DatabaseStorageController;

/**
 * Defines the controller class for the test entity.
 *
 * This extends the Drupal\entity\DatabaseStorageController class, adding
 * required special handling for test entities.
 */
class EntityTestStorageController extends DatabaseStorageController {

  /**
   * Overrides Drupal\entity\DatabaseStorageController::attachLoad().
   */
  protected function attachLoad(&$queried_entities, $load_revision = FALSE) {
    $data = db_select('entity_test_property_data', 'data', array('fetch' => PDO::FETCH_ASSOC))
      ->fields('data')
      ->condition('id', array_keys($queried_entities))
      ->orderBy('data.id')
      ->execute();

    foreach ($data as $values) {
      $entity = $queried_entities[$values['id']];
      $langcode = $values['langcode'];
      if (!empty($values['default_langcode'])) {
        $entity->setLangcode($langcode);
      }
      // Make sure only real properties are stored.
      unset($values['id'], $values['default_langcode']);
      $entity->setProperties($values, $langcode);
    }

    parent::attachLoad($queried_entities, $load_revision);
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::postSave().
   */
  protected function postSave(EntityInterface $entity, $update) {
    $default_langcode = ($language = $entity->language()) ? $language->langcode : LANGUAGE_NOT_SPECIFIED;
    $langcodes = array_keys($entity->translations());
    $langcodes[] = $default_langcode;

    foreach ($langcodes as $langcode) {
      $properties = $entity->getProperties($langcode);

      $values = array(
        'id' => $entity->id(),
        'langcode' => $langcode,
        'default_langcode' => intval($default_langcode == $langcode),
      ) + $properties;

      db_merge('entity_test_property_data')
        ->fields($values)
        ->condition('id', $values['id'])
        ->condition('langcode', $values['langcode'])
        ->execute();
    }
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::postDelete().
   */
  protected function postDelete($entities) {
    db_delete('entity_test_property_data')
      ->condition('id', array_keys($entities))
      ->execute();
  }
}
