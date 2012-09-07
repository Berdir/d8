<?php

/**
 * @file
 * Definition of Drupal\entity_test\EntityTestStorageController.
 */

namespace Drupal\entity_test;

use PDO;

use Drupal\entity\EntityInterface;
use Drupal\entity\DatabaseStorageControllerNG;

/**
 * Defines the controller class for the test entity.
 *
 * This extends the Drupal\entity\DatabaseStorageController class, adding
 * required special handling for test entities.
 */
class EntityTestStorageController extends DatabaseStorageControllerNG {

  /**
   * Overrides Drupal\entity\DatabaseStorageController::loadByProperties().
   */
  public function loadByProperties(array $values) {
    $query = db_select($this->entityInfo['base table'], 'base');
    $query->addTag($this->entityType . '_load_multiple');
    if ($values) {
      // Conditions need to be applied the property data table.
      $query->addJoin('inner', 'entity_test_property_data', 'data', "base.{$this->idKey} = data.{$this->idKey}");
      $query->distinct(TRUE);

      // @todo We should not be using a condition to specify whether conditions
      // apply to the default language or not. We need to move this to a
      // separate parameter during the following API refactoring.
      // Default to the original entity language if not explicitly specified
      // otherwise.
      if (!array_key_exists('default_langcode', $values)) {
        $values['default_langcode'] = 1;
      }
      // If the 'default_langcode' flag is explicitly not set, we do not care
      // whether the queried values are in the original entity language or not.
      elseif ($values['default_langcode'] === NULL) {
        unset($values['default_langcode']);
      }

      $data_schema = drupal_get_schema('entity_test_property_data');
      $query->addField('data', $this->idKey);
      foreach ($values as $field => $value) {
        // Check on which table the condition needs to be added.
        $table = isset($data_schema['fields'][$field]) ? 'data' : 'base';
        $query->condition($table . '.' . $field, $value);
      }
    }
    $ids = $query->execute()->fetchCol();
    return $ids ? $this->load($ids) : array();
  }

  /**
   * Maps from storage records to entity objects.
   *
   * @return array
   *   An array of entity objects implementing the EntityInterface.
   */
  protected function mapFromStorageRecords(array $records) {
    $records = parent::mapFromStorageRecords($records);

    // Load data of translatable properties.
    $this->attachPropertyData($records);
    return $records;
  }

  /**
   * Attaches property data in all languages for translatable properties.
   */
  protected function attachPropertyData(&$queried_entities) {
    $data = db_select('entity_test_property_data', 'data', array('fetch' => PDO::FETCH_ASSOC))
      ->fields('data')
      ->condition('id', array_keys($queried_entities))
      ->orderBy('data.id')
      ->execute();

    foreach ($data as $values) {
      $id = $values['id'];
      // Property values in default language are stored with
      // LANGUAGE_NOT_SPECIFIED as key.
      $langcode = empty($values['default_langcode']) ? $values['langcode'] : LANGUAGE_NOT_SPECIFIED;

      $queried_entities[$id]->name[$langcode][0]['value'] = $values['name'];
      $queried_entities[$id]->user_id[$langcode][0]['value'] = $values['user_id'];
    }
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::postSave().
   *
   * Stores values of translatable properties.
   */
  protected function postSave(EntityInterface $entity, $update) {
    $default_langcode = $entity->language()->langcode;

    foreach ($entity->getTranslationLanguages() as $langcode => $language) {
      $translation = $entity->getTranslation($langcode);

      $values = array(
        'id' => $entity->id(),
        'langcode' => $langcode,
        'default_langcode' => intval($default_langcode == $langcode),
        'name' => $translation->name->value,
        'user_id' => $translation->user_id->value,
      );

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

  /**
   * Implements \Drupal\entity\DataBaseStorageControllerNG::basePropertyDefinitions().
   */
  public function basePropertyDefinitions() {
    $properties['id'] = array(
      'label' => t('ID'),
      'description' => ('The ID of the test entity.'),
      'type' => 'integer_item',
      'list' => TRUE,
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => ('The UUID of the test entity.'),
      'type' => 'string_item',
      'list' => TRUE,
    );
    $properties['langcode'] = array(
      'label' => t('Language code'),
      'description' => ('The language code of the test entity.'),
      'type' => 'language_item',
      'list' => TRUE,
    );
    $properties['name'] = array(
      'label' => t('Name'),
      'description' => ('The name of the test entity.'),
      'type' => 'string_item',
      'list' => TRUE,
      'translatable' => TRUE,
    );
    $properties['user_id'] = array(
      'label' => t('User ID'),
      'description' => t('The ID of the associated user.'),
      'type' => 'entityreference_item',
      'settings' => array('entity type' => 'user'),
      'list' => TRUE,
      'translatable' => TRUE,
    );
    return $properties;
  }
}
