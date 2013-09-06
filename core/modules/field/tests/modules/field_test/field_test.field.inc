<?php

/**
 * @file
 * Defines a field type and its formatters and widgets.
 */

use Drupal\Core\Entity\EntityInterface;
use Drupal\field\FieldException;
use Drupal\field\FieldInterface;


/**
 * Implements hook_field_widget_info_alter().
 */
function field_test_field_widget_info_alter(&$info) {
  $info['test_field_widget_multiple']['field_types'][] = 'test_field';
}

/**
 * Implements hook_field_update_forbid().
 */
function field_test_field_update_forbid($field, $prior_field) {
  if ($field['type'] == 'test_field' && $field['settings']['unchangeable'] != $prior_field['settings']['unchangeable']) {
    throw new FieldException("field_test 'unchangeable' setting cannot be changed'");
  }
}

/**
 * Implements hook_field_load().
 */
function field_test_field_load($entity_type, $entities, $field, $instances, $langcode, &$items, $age) {
  $args = func_get_args();
  field_test_memorize(__FUNCTION__, $args);

  foreach ($items as $id => $item) {
    // To keep the test non-intrusive, only act for instances with the
    // test_hook_field_load setting explicitly set to TRUE.
    if (!empty($instances[$id]['settings']['test_hook_field_load'])) {
      foreach ($item as $delta => $value) {
        // Don't add anything on empty values.
        if ($value) {
          $items[$id][$delta]['additional_key'] = 'additional_value';
        }
      }
    }
  }
}


/**
 * Implements hook_field_validate().
 *
 * Possible error codes:
 * - 'field_test_invalid': The value is invalid.
 */
function field_test_field_validate(EntityInterface $entity = NULL, $field, $instance, $langcode, $items, &$errors) {
  $args = func_get_args();
  field_test_memorize(__FUNCTION__, $args);

  foreach ($items as $delta => $item) {
    if ($item['value'] == -1) {
      $errors[$field['field_name']][$langcode][$delta][] = array(
        'error' => 'field_test_invalid',
        'message' => t('%name does not accept the value -1.', array('%name' => $instance['label'])),
      );
    }
  }
}

/**
 * Implements hook_field_is_empty().
 */
function field_test_field_is_empty($item, $field_type) {
  return empty($item['shape']) && empty($item['color']);
}

/**
 * Form element validation handler for 'test_field_widget_multiple' widget.
 */
function field_test_widget_multiple_validate($element, &$form_state) {
  $values = array_map('trim', explode(',', $element['#value']));
  $items = array();
  foreach ($values as $value) {
    $items[] = array('value' => $value);
  }
  form_set_value($element, $items, $form_state);
}

/**
 * Implements hook_field_access().
 */
function field_test_field_access($op, FieldInterface $field, $entity_type, $entity, $account) {
  if ($field['field_name'] == "field_no_{$op}_access") {
    return FALSE;
  }

  // Only grant view access to test_view_field fields when the user has
  // 'view test_view_field content' permission.
  if ($field['field_name'] == 'test_view_field' && $op == 'view' && !$account->hasPermission('view test_view_field content')) {
    return FALSE;
  }

  return TRUE;
}
