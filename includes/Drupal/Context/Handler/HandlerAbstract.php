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
   * Reference to the context object.
   *
   * Note: This creates a circular reference.  We should probably get rid of it
   * and pass it every time.
   *
   * @todo Get rid of this property and avoid the circular dependency.
   *
   * @var Drupal\Context\ContextInterface
   */
  protected $context;

  /**
   * Parameters for the context handler.
   *
   * @var array
   */
  protected $params;

  public function __construct(ContextInterface $context, $params) {
    $this->context = $context;
    $this->params = $params;
  }
}
