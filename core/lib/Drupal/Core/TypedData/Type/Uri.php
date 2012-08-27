<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Uri.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The URI data type.
 *
 * @todo
 *   Consider using and return stream wrappers for all URI handling. To do so in
 *   a generic fashion we would need a read-only external stream wrapper.
 */
class Uri extends DataTypeBase implements DataWrapperInterface {

  /**
   * The data value.
   *
   * @var string
   */
  protected $value;

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
