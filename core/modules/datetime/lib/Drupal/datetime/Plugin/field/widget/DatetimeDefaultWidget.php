<?php
/**
 * @file
 * Contains \Drupal\datetime\Plugin\field\widget\DateTimeDefaultWidget.
 */

namespace Drupal\datetime\Plugin\field\widget;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\field\Plugin\Type\Widget\WidgetBase;
use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Plugin\PluginSettingsBase;
use Drupal\field\Plugin\Core\Entity\FieldInstance;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Plugin implementation of the 'datetime_default' widget.
 *
 * @Plugin(
 *   id = "datetime_default",
 *   module = "datetime",
 *   label = @Translation("Date and time"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class DateTimeDefaultWidget extends WidgetBase {

  /**
   * Constructs a DateTimeDefault Widget object.
   *
   * @param array $plugin_id
   *   The plugin_id for the widget.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\field\Plugin\Core\Entity\FieldInstance $instance
   *   The field instance to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param int $weight
   *   The widget weight.
   */
  public function __construct($plugin_id, array $plugin_definition, FieldInstance $instance, array $settings, $weight) {
    // Identify the function used to set the default value.
    $instance['default_value_function'] = $this->defaultValueFunction();
    parent::__construct($plugin_id, $plugin_definition, $instance, $settings, $weight);
  }

  /**
   * Return the callback used to set a date default value.
   *
   * @return string
   *   The name of the callback to use when setting a default date value.
   */
  public function defaultValueFunction() {
    return 'datetime_default_value';
  }

  /**
   * Implements \Drupal\field\Plugin\Type\Widget\WidgetInterface::formElement().
   *
   */
  public function formElement(array $items, $delta, array $element, $langcode, array &$form, array &$form_state) {

    $field = $this->field;
    $instance = $this->instance;
    $format_type = datetime_default_format_type();

    // We are nesting some sub-elements inside the parent, so we need a wrapper.
    // We also need to add another #title attribute at the top level for ease in
    // identifying this item in error messages. We do not want to display this
    // title because the actual title display is handled at a higher level by
    // the Field module.

    $element['#theme_wrappers'][] = 'datetime_wrapper';
    $element['#attributes']['class'][] = 'container-inline';
    $element['#element_validate'][] = 'datetime_datetime_widget_validate';

    // Identify the type of date and time elements to use.
    switch ($field['settings']['datetime_type']) {
      case 'date':
        $date_type = 'date';
        $time_type = 'none';
        $date_format = config('system.date')->get('formats.html_date.pattern.' . $format_type);
        $time_format = '';
        $element_format = $date_format;
        $storage_format = DATETIME_DATE_STORAGE_FORMAT;
        break;

      default:
        $date_type = 'date';
        $time_type = 'time';
        $date_format = config('system.date')->get('formats.html_date.pattern.' . $format_type);
        $time_format = config('system.date')->get('formats.html_time.pattern.' . $format_type);
        $element_format = $date_format . ' ' . $time_format;
        $storage_format = DATETIME_DATETIME_STORAGE_FORMAT;
        break;
    }

    $element['value'] = array(
      '#type' => 'datetime',
      '#default_value' => NULL,
      '#date_increment' => 1,
      '#date_date_format'=>  $date_format,
      '#date_date_element' => $date_type,
      '#date_date_callbacks' => array(),
      '#date_time_format' => $time_format,
      '#date_time_element' => $time_type,
      '#date_time_callbacks' => array(),
      '#date_timezone' => drupal_get_user_timezone(),
      '#required' => $element['#required'],
    );

    // Set the storage and widget options so the validation can use them. The
    // validator will not have access to field or instance settings.
    $element['value']['#date_element_format'] = $element_format;
    $element['value']['#date_storage_format'] = $storage_format;

    if (!empty($items[$delta]['date'])) {
      $date = $items[$delta]['date'];
      // The date was created and verified during field_load(), so it is safe to
      // use without further inspection.
      $date->setTimezone(new \DateTimeZone($element['value']['#date_timezone']));
      if ($field['settings']['datetime_type'] == 'date') {
        // A date without time will pick up the current time, use the default
        // time.
        datetime_date_default_time($date);
      }
      $element['value']['#default_value'] = $date;
    }

    return $element;
  }

}
