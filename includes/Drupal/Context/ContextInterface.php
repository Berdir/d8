<?php

namespace Drupal\Context;

/**
 * Interface definition for all context objects.
 */
interface ContextInterface {

  /**
   * Registers a class as the handler for a given context.
   *
   * @param string $context_key
   *   The context key to register for, such as "http:get".
   * @param string $class
   *   The name of the class that will handle this context key, unless overridden.
   *   The class must implement ContextHandlerInterface.
   * @param array $params
   *   An array of configuration options for the class.
   */
  public function setHandler($context_key, $class, $params = array());

  /**
   * Sets an explict value for a context key.
   *
   * @param string $context_key
   *   The context key to set.
   * @param mixed $value
   *   The value to which to set the context key.  It may be any primitive value
   *   or an instance of \Drupal\Context\ValueInterface.
   */
  public function setValue($context_key, $value);

  /**
   * Retrieves the value for the specified context key.
   *
   * The context key is a colon-delimited string.  If no literal value or
   * handler has been set for that value, the right-most fragment of the key
   * will be stripped off and used as a parameter to a handler on the remaining
   * key.  That process continues until either a value is found or the key runs
   * out.
   *
   * @param string $context_key
   *   The context key to retrieve.
   *
   * @return mixed
   *   The value that is associated with the context key. It may be a primitive
   *   value or an instance of \Drupal\Context\ValueInterface.
   */
  public function getValue($context_key);

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
