<?php

namespace Drupal\Context;

/**
 * Interface definition for all context objects.
 */
interface ContextInterface extends \ArrayAccess {

  /**
   * Register a class as the handler for a given context.
   *
   * @param string $context_key
   *   The context key to register for, such as "http:get".
   * @param string $class
   *   The name of the class that will handle this context key, unless overridden.
   *   The class must implement ContextHandlerInterface.
   * @param array $params
   *   An array of configuration options for the class.
   */
  public function registerHandler($context_key, $class, $params = array());

  /**
   * Return a set of keys to objects used in the current context
   *
   * This converts any context values referenced in the current scope into
   * a normalised array.
   *
   * @return an array of context keys and their corresponding values
   */
  public function usedKeys();

  /**
   * Lock this context object against futher modification.
   *
   * This allows us to setup a mocked context object very easily, and then
   * make it immutable so we know that it won't change out from under us.
   */
  public function lock();

  /**
   * Spawns a new context object that is pushed to the context stack.
   *
   * @return DrualContextInterface
   */
  public function addLayer();
}
