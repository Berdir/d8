<?php
/**
 * @file
 * Contains \Drupal\datetime\Plugin\Field\FieldWidget\DateTimeTimestampWidget.
 */

namespace Drupal\datetime\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'datetime timestamp' widget.
 *
 * @FieldWidget(
 *   id = "datetime_timestamp",
 *   label = @Translation("Datetime Timestamp"),
 *   field_types = {
 *     "timestamp",
 *     "created",
 *   }
 * )
 */
class DateTimeTimestampWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $format_type = datetime_default_format_type();
    $date_format = entity_load('date_format', 'html_date')->getPattern($format_type);
    $time_format = entity_load('date_format', 'html_time')->getPattern($format_type);
    $default_value = isset($items[$delta]->value) ? DrupalDateTime::createFromTimestamp($items[$delta]->value) : '';
    $element['value'] = $element + array(
      '#type' => 'datetime',
      '#default_value' => $default_value,
      '#element_validate' => array(
        array($this, 'elementValidate'),
      ),
    );
    $element['value']['#description'] = $this->t('Format: %format. Leave blank to use the time of form submission.', array('%format' => datetime_format_example($date_format . ' ' . $time_format)));

    return $element;
  }

  /**
   * Validates an element.
   *
   * @todo Convert to massageFormValues() after https://drupal.org/node/2226723 lands.
   */
  public function elementValidate($element, &$form_state, $form) {
    $date = $element['#value']['object'];
    if ($date->hasErrors()) {
      $value = -1;
    }
    else {
      $value = $date->getTimestamp();
    }
    form_set_value($element, $value, $form_state);
  }

}
