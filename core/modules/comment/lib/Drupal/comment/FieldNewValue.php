<?php

/**
 * @file
 * Contains \Drupal\comment\FieldNewValue.
 */

namespace Drupal\comment;

use Drupal\Core\TypedData\TypedData;
use Drupal\Core\TypedData\ReadOnlyException;
use InvalidArgumentException;

/**
 * A computed property for the integer value of the 'new' field.
 *
 * @todo: Declare the list of allowed values once supported.
 */
class FieldNewValue extends TypedData {

  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue() {
    if (!isset($this->value)) {
      if (!isset($this->parent)) {
        throw new InvalidArgumentException('Computed properties require context for computation.');
      }
      $field = $this->parent->getParent();
      $entity = $field->getParent();
      $this->value = comment_mark($entity);
    }
    return $this->value;
  }

  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::setValue().
   */
  public function setValue($value, $notify = TRUE) {
    if (isset($value)) {
      throw new ReadOnlyException('Unable to set a computed property.');
    }
  }
}
