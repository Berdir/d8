<?php

/**
 * @file
 * Definition of Drupal\number\Plugin\field\formatter\NumberUnformattedFormatter.
 */

namespace Drupal\number\Plugin\field\formatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Entity\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'number_unformatted' formatter.
 *
 * @FieldFormatter(
 *   id = "number_unformatted",
 *   label = @Translation("Unformatted"),
 *   field_types = {
 *     "number_integer",
 *     "number_decimal",
 *     "number_float"
 *   }
 * )
 */
class NumberUnformattedFormatter extends \Drupal\Core\Field\FormatterBase {

  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::viewElements().
   */
  public function viewElements(FieldItemListInterface $items) {
    $elements = array();

    foreach ($items as $delta => $item) {
      $elements[$delta] = array('#markup' => $item->value);
    }

    return $elements;
  }

}
