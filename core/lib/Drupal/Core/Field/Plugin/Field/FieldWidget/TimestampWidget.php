<?php

/**
 * @file
 * Contains \Drupal\Core\Field\Plugin\Field\FieldWidget\TimestampWidget.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'timestamp' widget.
 *
 * @FieldWidget(
 *   id = "timestamp",
 *   label = @Translation("Timestamp"),
 *   field_types = {
 *     "timestamp",
 *     "created",
 *   },
 *   settings = {
 *     "use_request_time_on_empty" = FALSE,
 *   },
 * )
 */
class TimestampWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $default_value = isset($items[$delta]->value) ? format_date($items[$delta]->value, 'custom', 'Y-m-d H:i:s O') : '';
    $element['value'] = $element + array(
      '#type' => 'textfield',
      '#default_value' => $default_value,
      '#maxlength' => 25,
      '#element_validate' => array(
        array($this, 'elementValidate'),
      ),
    );

    $timestamp = (int) $items[$delta]->value;
    $created_timestamp = (int) $items->getEntity()->getCreatedTime();
    $element['value']['#description'] = $this->t('Format: %time. The date format is YYYY-MM-DD and %timezone is the time zone offset from UTC. Leave blank to use the time of form submission.', array('%time' => !empty($default_value) ?
        date_format(date_create($default_value), 'Y-m-d H:i:s O') : format_date($created_timestamp, 'custom', 'Y-m-d H:i:s O'), '%timezone' => !empty($default_value) ? date_format(date_create($default_value), 'O') : format_date($created_timestamp, 'custom', 'O')));

    return $element;
  }

  /**
   * Validates an element.
   *
   * @todo Convert to massageFormValues() after https://drupal.org/node/2226723 lands.
   */
  public function elementValidate($element, &$form_state, $form) {
    $value = trim($element['#value']);
    if (empty($value)) {
      $value = $this->getSetting('use_request_time_on_empty') ? REQUEST_TIME : 0;
    }
    else {
      $date = new DrupalDateTime($value);
      if ($date->hasErrors()) {
        $value = FALSE;
      }
      else {
        $value = $date->getTimestamp();
      }
    }
    form_set_value($element, $value, $form_state);
  }

}
