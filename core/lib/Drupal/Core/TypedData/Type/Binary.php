<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Binary.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The binary data type.
 *
 * @todo
 *   Consider to use either the URI data wrapper or Drupal's stream wrapper API
 *   to handle the file.
 */
class Binary implements DataWrapperInterface {

  /**
   * The data definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * The filename of the binary file.
   *
   * @var string
   */
  protected $filename = NULL;

  /**
   * Resource pointer to the file.
   *
   * @var mixed
   */
  protected $handle = NULL;

  /**
   * Implements DataWrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, $context = array()) {
    $this->definition = $definition;
    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Close file resource handle when desctructing this wrapper.
   */
  public function __destruct() {
    if ($this->handle !== NULL) {
      fclose($this->handle);
    }
  }

  /**
   * Implements DataWrapperInterface::getType().
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Implements DataWrapperInterface::getDefinition().
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Implements DataWrapperInterface::getValue().
   */
  public function getValue() {
    if (file_exists($this->filename)) {
      $this->handle = fopen($this->filename);
      return fread($this->handle, filesize($this->filename));
    }
    else {
      return NULL;
    }
  }

  /**
   * Implements DataWrapperInterface::setValue().
   */
  public function setValue($value) {
    $this->filename = $value;
  }

  /**
   * Implements DataWrapperInterface::getString().
   */
  public function getString() {
    return base64_encode($this->getValue());
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
