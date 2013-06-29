<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\Plugin\DataType\DurationIso8601.
 */

namespace Drupal\Core\TypedData\Plugin\DataType;

use Drupal\Core\TypedData\Annotation\DataType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\TypedData\Type\DurationInterface;

/**
 * The duration ISO8601 data type.
 *
 * The plain value of this data type is a ISO8601 duration string.
 *
 * @DataType(
 *   id = "duration",
 *   label = @Translation("Duration")
 * )
 */
class DurationIso8601 extends String implements DurationInterface {

  /**
   * {@inheritdoc}
   */
  public function getDuration() {
    if ($this->value) {
      // @todo: Add support for negative intervals on top of the DateInterval
      // constructor.
      return new \DateInterval($this->value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setDuration(\DateInterval $duration) {
    // Generate an ISO 8601 formatted string as supported by
    // DateInterval::__construct() and setValue().
    $this->value = $duration->format('%rP%yY%mM%dDT%hH%mM%sS');
  }

}
