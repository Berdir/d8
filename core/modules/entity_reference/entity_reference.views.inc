<?php

/**
 * @file
 * Provides views data for the entity_reference module.
 */

/**
 * Implements hook_field_views_data().
 */

use Drupal\Core\Entity\FieldableDatabaseStorageController;

function entity_reference_field_views_data($field) {

  $data = field_views_field_default_views_data($field);
  $entity_manager = Drupal::entityManager();
  foreach ($data as $table_name => $table_data) {
    // Add a relationship to the target entity type.
    $target_type = $field->settings['target_type'];
    $target_entity_info = $entity_manager->getDefinition($target_type);
    $target_base_table = $target_entity_info->getBaseTable();

    // Provide a relationship from field entity type to the referenced entity
    // type.
    $args = array(
      '@label' => $target_entity_info->getLabel(),
      '@field_name' => $field->name
    );
    $data[$table_name][$field->name]['relationship'] = array(
      'id' => 'standard',
      'base' => $target_base_table,
      'entity type' => $target_type,
      'base field' => $target_entity_info->getKey('id'),
      'relationship field' => $field->name . '_target_id',
      'title' => t('@label referenced from @field_name', $args),
      'label' => t('@field_name: @label', $args),
    );

    // Provide a relationship from the referenced entity type to the field
    // entity type.
    $pseudo_field_name = 'reverse_' . $field->name;
    $data[$target_base_table][$pseudo_field_name]['relationship'] = array(
      'title' => t('@label using @field_name', array(
        '@label' => $target_entity_info->getLabel(),
        '@field_name' => $field->name
      )),
      'help' => t('Relate each @label with a @field_name.', array(
        '@label' => $target_entity_info->getLabel(),
        '@field_name' => $field->name
      )),
      'id' => 'entity_reverse',
      'field_name' => $field->name,
      'field table' => FieldableDatabaseStorageController::_fieldTableName($field),
      'field field' => $field->name . '_target_id',
      'base' => $target_entity_info->getBaseTable(),
      'base field' => $target_entity_info->getKey('id'),
      'label' => t('@field_name', array('@field_name' => $field->name)),
    );
  }

  return $data;
}
