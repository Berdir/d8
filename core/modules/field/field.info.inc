<?php

/**
 * @file
 * Field Info API, providing information about available fields and field types.
 */

/**
 * Clears the field info cache without clearing the field data cache.
 *
 * This is useful when deleted fields or instances are purged. We need to remove
 * the purged records, but no actual field data items are affected.
 */
function field_info_cache_clear() {
  drupal_static_reset('field_view_mode_settings');
  drupal_static_reset('field_form_mode_settings');

  // @todo: Remove this when field_attach_*_bundle() bundle management
  // functions are moved to the entity API.
  entity_info_cache_clear();

  // Clear typed data definitions.
  \Drupal::typedDataManager()->clearCachedDefinitions();
  \Drupal::service('plugin.manager.field.field_type')->clearCachedDefinitions();
  \Drupal::service('config.factory')->reset();
}
