<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Binary.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;
use InvalidArgumentException;

/**
 * The binary data type.
 */
class Binary extends DataTypeBase implements DataWrapperInterface {

  /**
   * The resource URI.
   *
   * @var string
   */
  protected $uri;

  /**
   * A generic resource handle.
   *
   * @var resource
   */
  public $handle = NULL;

  /**
   * Implements DataWrapperInterface::getValue().
   */
  public function getValue() {
    if (!isset($this->handle) && isset($this->uri)) {
      $this->handle = fopen($this->uri, 'rb');
    }
    return $this->handle;
  }

  /**
   * Implements DataWrapperInterface::setValue().
   */
  public function setValue($value) {
    if (is_resource($value)) {
      $this->handle = $value;
    }
    elseif (is_string($value)) {
      $this->uri = $value;
    }
    else {
      throw new InvalidArgumentException("Invalid value for binary data given.");
    }
  }

  /**
   * Implements DataWrapperInterface::getString().
   */
  public function getString() {
    $contents = '';
    while (!feof($this->getValue())) {
      $contents .= fread($this->handle, 8192);
    }
    return $contents;
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
