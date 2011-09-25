<?php

namespace Drupal\Context;

/**
 * Transaction-like class for the context stack.
 *
 * When this class is destroyed, so its its corresponding context object.
 */
class Tracker {

  /**
   * The context object we're tracking.
   *
   * @var Drupal\Context\Context
   */
  protected $context;

  /**
   * Constructor
   *
   * @var Drupal\Context\Context $context
   *   The context object we should be tracking.
   */
  public function __construct(\Drupal\Context\ContextInterface $context) {
    $this->context = $context;
  }

  /**
   * Destructor
   *
   * Destroys the corresponding context object, too.
   */
  public function __destruct() {
    if (isset($this->context)) {
      $this->context->__destruct();
    }
  }
}