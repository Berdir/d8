<?php

/**
 * @file
 * Contains \Drupal\number\Plugin\field\formatter\NumberIntegerFormatter.
 */

namespace Drupal\number\Plugin\Field\FieldFormatter;

/**
 * Plugin implementation of the 'number_integer' formatter.
 *
 * The 'Default' formatter is different for integer fields on the one hand, and
 * for decimal and float fields on the other hand, in order to be able to use
 * different settings.
 *
 * @FieldFormatter(
 *   id = "number_integer",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "number_integer"
 *   }
 * )
 */
class NumberIntegerFormatter extends DefaultNumberFormatter {

  /**
   * {@inheritdoc}
   */
  public static function settings() {
    $settings = parent::settings();
    $settings['thousand_separator'] = '';
    $settings['prefix_suffix'] = TRUE;
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  protected function numberFormat($number) {
    return number_format($number, 0, '', $this->getSetting('thousand_separator'));
  }

}
