<?php

/**
 * @file
 * Contains \Drupal\entity_test\Entity\EntityTestMul.
 */

namespace Drupal\entity_test\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\entity_test\Entity\EntityTest;

/**
 * Defines the test entity class.
 *
 * @ContentEntityType(
 *   id = "entity_test_mul",
 *   label = @Translation("Test entity - data table"),
 *   controllers = {
 *     "view_builder" = "Drupal\entity_test\EntityTestViewBuilder",
 *     "access" = "Drupal\entity_test\EntityTestAccessController",
 *     "form" = {
 *       "default" = "Drupal\entity_test\EntityTestForm",
 *       "delete" = "Drupal\entity_test\EntityTestDeleteForm"
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "entity_test_mul",
 *   data_table = "entity_test_mul_property_data",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "bundle" = "type",
 *     "label" = "name",
 *   },
 *   links = {
 *     "canonical" = "entity_test.edit_entity_test_mul",
 *     "edit-form" = "entity_test.edit_entity_test_mul",
 *     "delete-form" = "entity_test.delete_entity_test_mul",
 *     "admin-form" = "entity_test.admin_entity_test_mul"
 *   }
 * )
 */
class EntityTestMul extends EntityTest {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['default_langcode'] = FieldDefinition::create('boolean')
      ->setLabel(t('Default language'))
      ->setDescription(t('Flag to indicate whether this is the default language.'));

    return $fields;
  }

}
