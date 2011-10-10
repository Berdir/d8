<?php

namespace Drupal\Context\Handler;

use Drupal\Context\ContextInterface;

/**
 * Interface for context handler objects.
 * 
 * Handlers are stateless object: they receive the context to work with at
 * getValue() time.
 */
interface HandlerInterface {
  /**
   * Retrieves the value for this context key.
   *
   * This value must be assumed to be immutable within a given request.
   *
   * @param array $args
   *   Arguments to pass into the context handler.  Arguments are derived from
   *   the portion of the context key after the key fragment that led to this
   *   handler.
   * @param ContextInterface $context = null
   *   The current context scope within this handler must fetch values.
   * @return mixed
   *   The corresponding value for this context. Return here an new instance of
   *   ContextOffsetIsNull if you don't have any value corresponding to
   *   the given arguments to provide: this will cause the context to stop
   *   value lookup for this offset.
   */
  public function getValue(array $args = array(), ContextInterface $context = null);
}
