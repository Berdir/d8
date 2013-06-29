<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\Plugin\DataType\Date.
 */

namespace Drupal\Core\TypedData\Plugin\DataType;

use Drupal\Core\TypedData\Annotation\DataType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\TypedData\TypedData;

/**
 * The date data type.
 *
 * The plain value of a date is an instance of the DrupalDateTime class. For
 * setting the value any value supported by the __construct() of the
 * DrupalDateTime class will work, including a DateTime object, a timestamp, a
 * string date, or an array of date parts.
 *
 * @DataType(
 *   id = "date",
 *   label = @Translation("Date"),
 *   primitive_type = 5
 * )
 */
class Date extends TypedData {

  /**
   * The data value.
   *
   * @var DateTime
   */
  protected $value;

  /**
   * Overrides TypedData::setValue().
   */
  public function setValue($value, $notify = TRUE) {
    // Don't try to create a date from an empty value.
    // It would default to the current time.
    if (!isset($value)) {
      $this->value = $value;
    }
    else {
      $this->value = $value instanceOf DrupalDateTime ? $value : new DrupalDateTime($value);
    }
    // Notify the parent of any changes.
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }
}
