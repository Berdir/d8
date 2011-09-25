<?php

namespace Drupal\Context;

/**
 * Default Drupal context object.
 *
 * It handles routing of context requests to handlers.
 */
class Context implements ContextInterface {

  /**
   * The stack of context objects in the system.
   *
   * In a just world this would be the SplObjectStorage class in PHP 5.3.
   *
   * @var array
   */
  protected static $contextStack = array();

  /**
   * The query string for this page. This generally means the value of $_GET['q'].
   *
   * @var string
   */
  protected $queryString;

  /**
   * Index of registered handler classes.
   *
   * @var array
   */
  protected $handlerClasses = array();

  /**
   * Index of already-instantiated handler objects.
   *
   * @var array
   */
  protected $handlers = array();

  /**
   * Key/value store of already-derived context information.
   *
   * @var array
   */
  protected $context = array();

  /**
   * An array of keys for all the values and objects in $context accessed in
   * the current scope.
   *
   * @var array
   */
  protected $usedKeys = array();

  /**
   * Whether or not this object has been locked against further changes.
   * @var boolean
   */
  protected $locked = FALSE;

  /**
   * The hash of the parent context object from which this object will inherit
   * data.
   *
   * @var string
   */
  protected $parentId = NULL;

  public function __construct($parent_id = NULL) {
    if ($parent_id) {
      $this->parentId = $parent_id;
    }
  }

  /**
   * Returns the top-most context object, which is the active object.
   *
   * @return ContextInterface
   */
  public static function getActiveContext() {
   return end(self::$contextStack);
  }

  /**
   * Implements ArrayAccess::offsetExists().
   */
  public function offsetExists($offset) {
    return $this->offsetGet($offset) !== NULL;
  }

  /**
   * Implements ArrayAccess:offsetGet().
   */
  public function offsetGet($offset) {
    if (!$this->locked) {
      throw new NotLockedException(t('This context object has not been locked. It must be locked before it can be used.'));
    }

    // We do not have data for this offset yet: use array_key_exists() because
    // the value can be NULL. We do not want to re-run all handlerClasses for a
    // variable with data.
    if (!array_key_exists($offset, $this->context)) {
      // Loop over the possible context keys.
      $context_key = $offset;
      $offset_elements = explode(':', $offset);
      $args = array();
      while ($offset_elements) {
        if (isset($this->handlerClasses[$context_key])) {
          // Lazzy handler instanciation.
          if (!isset($this->handlers[$context_key]) && class_exists($this->handlerClasses[$context_key]['class'])) {
            $this->handlers[$context_key] = new $this->handlerClasses[$context_key]['class']($this, $this->handlerClasses[$context_key]['params']);
          }

          if (isset($this->handlers[$context_key])) {
            $handlerValue = $this->handlers[$context_key]->getValue($args);
            // NULL value here means the context pass, and let potential parent
            // overrides happen.
            if (NULL !== $handlerValue) {
              // The null object here means it's definitely a NULL and parent
              // cannot override it.
              if ($handlerValue instanceof OffsetIsNull) {
                $this->context[$offset] = NULL;
              } else {
                $this->context[$offset] = $handlerValue;
              }
            }
          }
        }

        array_unshift($args, array_pop($offset_elements));
        $context_key = implode(':', $offset_elements);
      }

      // If we did not found a value using local handlers, check for parents.
      if (!array_key_exists($offset, $this->context)) {
        if (isset($this->parentId)) {
          if (isset(self::$contextStack[$this->parentId])) {
            $this->context[$offset] = self::$contextStack[$this->parentId]->offsetGet($offset);
          } else {
            throw new ParentContextNotExistsException('Parent context does not exists anymore.');
          }
        } else {
          $this->context[$offset] = null;
        }
      }
    }

    // Store the value for key retrieval.
    if (!isset($this->usedKeys[$offset])) {
      $this->usedKeys[$offset] = $offset;
    }

    return $this->context[$offset];
  }

  /**
   * Implements ArrayAccess:offsetSet().
   */
  public function offsetSet($offset, $value) {
    if ($this->locked) {
      throw new LockedException(t('This context object has been locked. It no longer accepts new explicit context sets.'));
    }
    // Set an explicit override for a given context value.
    $this->context[$offset] = $value;
  }

  /**
   * Implements ArrayAccess:offsetUnset().
   */
  public function offsetUnset($offset) {
    if ($this->locked) {
      throw new LockedException(t('This context object has been locked. It no longer accepts context clears.'));
    }

    // Remove this value from the usedKeys and unset any saved context so that
    // it can be regenerated by the context handler as needed.
    unset($this->usedKeys[$offset], $this->context[$offset]);
  }

  /**
   * Implmenents DrupalContextInterface::registerHandler().
   */
  public function registerHandler($context, $class, $params = array()) {
    if ($this->locked) {
      throw new LockedException(t('This context object has been locked. It no longer accepts new handler registrations.'));
    }
    $this->handlerClasses[$context] = array('class' => $class, 'params' => $params);
  }

  /**
   * Implements DrupalContextInterface::usedKeys().
  */
  function usedKeys() {
    $key_list = array();

    foreach ($this->usedKeys as $key) {
      $value = $this->context[$key];
      if ($value instanceof ValueInterface) {
        $key_list[$key] = $value->contextKey();
      }
      else {
        $key_list[$key] = $value;
      }
    }

    return $key_list;
  }

  /**
   * Implmenents DrupalContextInterface::lock().
   */
  public function lock() {
    $this->locked = TRUE;
    self::$contextStack[spl_object_hash($this)] = $this;
    return new Tracker($this);
  }

  /**
   * Implements DrupalContextInterface::addLayer();
   */
  public function addLayer() {

    $layer = new self(spl_object_hash($this));
    return $layer;
  }

  /**
   * When destroying this object, pop it off the stack and everything above it.
   *
   * Note that this method does not actively destroy those context objects, it
   * just pops them off the stack.  PHP will delete them for us unless someone
   * has one hanging around somewhere.
   */
  public function __destruct() {
    $me = spl_object_hash($this);

    // Never remove the root item from the stack.
    $offset = array_search($me, array_keys(self::$contextStack));
    if ($offset) {
      self::$contextStack = array_slice(self::$contextStack, 0, $offset, TRUE);
    }
  }
}
