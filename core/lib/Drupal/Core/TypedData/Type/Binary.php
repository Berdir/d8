<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Binary.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\DataWrapperInterface;

/**
 * The binary data type.
 */
class Binary implements DataWrapperInterface {

  /**
   * The data definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * The resource URI.
   *
   * @var string
   */
  protected $uri;

  /**
   * The resource stream wrapper.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperInterface
   */
  protected $streamWrapper;

  /**
   * The path that was opened.
   */
  protected $openedPath;

  /**
   * Implements DataWrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    $this->definition = $definition;
    if (isset($value)) {
      $this->setValue($value);
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
    $class = file_stream_wrapper_get_class($this->definition['scheme']);
    $this->streamWrapper = new $class;
    $this->streamWrapper->setUri($this->uri);
    $this->streamWrapper->stream_open($this->uri, 'r', STREAM_USE_PATH | STREAM_REPORT_ERRORS, $this->openedPath);
    return $this->streamWrapper->handle;
  }

  /**
   * Implements DataWrapperInterface::setValue().
   */
  public function setValue($value) {
    $this->uri = $value;
  }

  /**
   * Implements DataWrapperInterface::getString().
   */
  public function getString() {
    return base64_encode($this->streamWrapper->stream_read(filesize($this->openedPath)));
  }

  /**
   * Implements DataWrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
