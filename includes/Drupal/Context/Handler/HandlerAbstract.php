<?php

namespace Drupal\Context\Handler;

use \Drupal\Context\ContextInterface;

/**
 * Base implementation of a Context Handler.
 *
 * Other handlers may extend this class to make their job easier.
 */
abstract class HandlerAbstract implements HandlerInterface {
  /**
   * Parameters for the context handler.
   *
   * @var array
   */
  protected $params;

  public function __construct(array $params = array()) {
    $this->params = $params;
  }
}
