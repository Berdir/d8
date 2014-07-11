<?php

/**
 * @file
 * Contains \Drupal\Core\Field\Plugin\Field\FieldWidget\BooleanWidget.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'options_buttons' widget.
 *
 * @FieldWidget(
 *   id = "boolean",
 *   label = @Translation("Check boxes/radio buttons"),
 *   field_types = {
 *     "boolean",
 *   },
 *   multiple_values = TRUE
 * )
 */
class BooleanWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $options = array();
    if (!$this->fieldDefinition->isRequired() && !$this->fieldDefinition->getFieldStorageDefinition()->isMultiple()) {
      $options = array('_none' => t('N/A'));
    }
    $options += $items->getFieldDefinition()->getSetting('allowed_values');
    $selected_options = array();
    foreach ($items as $item) {
      $value = $item->value;
      // Keep the value if it actually is in the list of options (needs to be
      // checked against the flat list).
      if (isset($options[$value])) {
        $selected_options[] = $value;
      }
    }

    if ($this->fieldDefinition->getFieldStorageDefinition()->isMultiple()) {
      // If required and there is one single option, preselect it.
      $element['value'] = $element + array(
        '#type' => 'checkboxes',
        '#default_value' => $selected_options,
        '#options' => $options,
      );
    }
    else {
      $element['value'] = $element + array(
        '#type' => 'radios',
        // Radio buttons need a scalar value. Take the first default value, or
        // default to NULL so that the form element is properly recognized as
        // not having a default value.
        '#default_value' => $selected_options ? reset($selected_options) : NULL,
        '#options' => $options,
      );
    }

    $element['#element_validate'][] = array(get_class($this), 'validateElement');

    return $element;
  }

  /**
   * Form validation handler for boolean widget elements.
   *
   * @param array $element
   *   The form element.
   * @param array $form_state
   *   The form state.
   */
  public static function validateElement(array $element, array &$form_state) {
    if ($element['#required'] && $element['#value'] == '_none') {
      \Drupal::formBuilder()->setError($element, $form_state, t('!name field is required.', array('!name' => $element['#title'])));
    }

    if (is_array($element['value']['#value'])) {
      $values = array_values($element['value']['#value']);
    }
    else {
      $values = array($element['value']['#value']);
    }

    // Filter out the 'none' option. Use a strict comparison, because
    // 0 == 'any string'.
    $index = array_search('_none', $values, TRUE);
    if ($index !== FALSE) {
      unset($values[$index]);
    }

    // Transpose selections from field => delta to delta => field.
    $items = array();
    foreach ($values as $value) {
      $items[] = array('value' => $value);
    }
    \Drupal::formBuilder()->setValue($element, $items, $form_state);
  }

}
