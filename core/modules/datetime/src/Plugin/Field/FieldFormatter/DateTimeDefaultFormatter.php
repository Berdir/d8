<?php

/**
 * @file
 * Contains \Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeDefaultFormatter.
 */

namespace Drupal\datetime\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'Default' formatter for 'datetime' fields.
 *
 * @FieldFormatter(
 *   id = "datetime_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class DateTimeDefaultFormatter extends DateTimeFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'format_type' => 'medium',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    foreach ($items as $delta => $item) {
      $output = '';
      $iso_date = '';
      $output2 = '';
      $iso_date2 = '';

      if ($item->date) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
        $date = $item->date;
        // Create the ISO date in Universal Time.
        $iso_date = $date->format("Y-m-d\TH:i:s") . 'Z';

        if ($this->getFieldSetting('datetime_type') == 'date') {
          // A date without time will pick up the current time, use the default.
          datetime_date_default_time($date);
        }
        $this->setTimeZone($date);

        $output = $this->formatDate($date);
      }

      if ($item->date2 && $item->date != $item->date2) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
        $date2 = $item->date2;
        // Create the ISO date in Universal Time.
        $iso_date2 = $date->format("Y-m-d\TH:i:s") . 'Z';

        if ($this->getFieldSetting('datetime_type') == 'date') {
          // A date without time will pick up the current time, use the default.
          datetime_date_default_time($date2);
        }
        $this->setTimeZone($date2);

        $output2 = $this->formatDate($date2);

        // Check if start day == end day.
        if ($this->getFieldSetting('datetime_type') == 'datetime') {
          // TODO better logic to split the date and time based on the format.
          $length = strspn($output2, $output);
          if ($length) {

            $output2 = substr($output2, $length - 1);
          }
        }
      }

      // Display the date using theme datetime.
      $elements[$delta][] = [
        '#cache' => [
          'contexts' => [
            'timezone',
          ],
        ],
        '#theme' => 'time',
        '#text' => $output,
        '#html' => FALSE,
        '#attributes' => ['datetime' => $iso_date],
      ];

      // Append on the end date.
      if ($this->getFieldSetting('enddate_get') && !empty($output2)) {
        $elements[$delta][] = [
          '#markup' => $this->t(' to '),
        ];
        $elements[$delta][] = [
          '#cache' => [
            'contexts' => [
              'timezone',
            ],
          ],
          '#theme' => 'time',
          '#text' => $output2,
          '#html' => FALSE,
          '#attributes' => ['datetime' => $iso_date2],
        ];
      }
      if (!empty($item->_attributes)) {
        $elements[$delta][0]['#attributes'] += $item->_attributes;
        if ($this->getFieldSetting('enddate_get') && !empty($output2)) {
          $elements[$delta][2]['#attributes'] += $item->_attributes;
        }
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }
    }

    return $elements;

  }

  /**
   * {@inheritdoc}
   */
  protected function formatDate($date) {
    $format_type = $this->getSetting('format_type');
    $timezone = $this->getSetting('timezone_override');
    return $this->dateFormatter->format($date->getTimestamp(), $format_type, '', $timezone != '' ? $timezone : NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $time = new DrupalDateTime();
    $format_types = $this->dateFormatStorage->loadMultiple();
    $options = [];
    foreach ($format_types as $type => $type_info) {
      $format = $this->dateFormatter->format($time->format('U'), $type);
      $options[$type] = $type_info->label() . ' (' . $format . ')';
    }

    $form['format_type'] = array(
      '#type' => 'select',
      '#title' => t('Date format'),
      '#description' => t("Choose a format for displaying the date. Be sure to set a format appropriate for the field, i.e. omitting time for a field that only has a date."),
      '#options' => $options,
      '#default_value' => $this->getSetting('format_type'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $date = new DrupalDateTime();
    $summary[] = t('Format: @display', array('@display' => $this->formatDate($date, $this->getFormatSettings())));

    return $summary;
  }

}
