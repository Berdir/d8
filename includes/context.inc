<?php

/**
 * Exception thrown when attempting to modify a locked context object.
 */
class ContextLockedException extends Exception {}

/**
 * Exception thrown when attempting to use an unlocked context object.
 */
class ContextNotLockedException extends Exception {}

/**
 * Exception thrown when attempting to fall through to a parent context object
 * that already got garbace collected
 */
class ContextParentContextNotExistsException extends Exception {}

/**
 * Generic wrapper for an HTTP request.
 *
 * @deprecated.  This will be removed as soon as the Symfony HTTP library is
 *   in place.
 */
interface DrupalRequestInterface {
  /**
   * Retrieves a value from the GET query.
   *
   * See the PHP ext/filter documentation for details.
   *
   * @link http://www.php.net/manual/en/book.filter.php
   *
   * @param string $param
   *   The name of the GET parameter to retrieve.
   * @param int $filter
   *   The PHP filter rules to apply to the filter. See the ext/filter
   *   documentation for a complete list of available settings.
   * @param array $options
   *   An array of options to customize $filter.  See the ext/filter
   *   documentation for a complete list of available settings.
   * @return mixed
   */
  public function get($param, $filter = FILTER_UNSAFE_RAW, $options = array());

  /**
   * Retrieves a value from the POST query.
   *
   * See the PHP ext/filter documentation for details.
   *
   * @link http://www.php.net/manual/en/book.filter.php
   *
   * @param int $filter
   *   The PHP filter rules to apply to the filter. See the ext/filter
   *   documentation for a complete list of available settings.
   * @param array $options
   *   An array of options to customize $filter.  See the ext/filter
   *   documentation for a complete list of available settings.
   *
   * @return mixed
   */
  public function post($param, $filter = FILTER_UNSAFE_RAW, $options = array());

  /**
   * Retrives an arbitrary HTTP header.
   *
   * @param string $header
   *   The name of the header to request.
   * @return mixed
   */
  public function header($header);

}

/**
 * Generic wrapper for an HTTP request.
 *
 * @deprecated.  This will be removed as soon as the Symfony HTTP library is
 *   in place.
 */
class DrupalRequestHttp implements DrupalRequestInterface {

  protected $getQuery;

  protected $postQuery;

  protected $filesQuery;

  protected $cookieQuery;

  protected $requestQuery;

  protected $requestHeaders;

  public function __construct() {
    $this->getQuery = $_GET;
    $this->postQuery = $_POST;
    $this->filesQuery = $_FILES;
    $this->cookieQuery = $_COOKIE;
    $this->requestQuery = $_REQUEST;

    array_walk($this->getQuery, '_fix_gpc_magic');
    array_walk($this->postQuery, '_fix_gpc_magic');
    array_walk($this->filesQuery, '_fix_gpc_magic');
    array_walk($this->cookieQuery, '_fix_gpc_magic');
    array_walk($this->requestQuery, '_fix_gpc_magic');
  }

  /**
   * Implements DrupalRequestInterface::get().
   */
  public function get($param, $filter = FILTER_UNSAFE_RAW, $options = array()) {
    if (!empty($this->getQuery[$param])) {
      return filter_var($this->getQuery[$param], $filter, $options);
    }
    return FALSE;
  }

  /**
   * Implements DrupalRequestInterface::post().
   */
  public function post($param, $filter = FILTER_UNSAFE_RAW, $options = array()) {
    if (!empty($this->postQuery[$param])) {
      return filter_var($this->postQuery[$param], $filter, $options);
    }
    return FALSE;
  }


  /**
   * Implements DrupalRequestInterface::header().
   */
  public function header($header) {
    // @todo This is, of course, Apache-specific.  We need to find the IIS
    // equilvalent, too.  Maybe that's even an alternate class implementation?
    if (!$this->headers) {
      $this->requestHeaders = apache_request_headers();
    }

    // @todo This needs proper escaping.  I'm not entirely sure how to do that.
    return isset($this->requestHeaders[$header]) ? $this->requestHeaders[$header] : NULL;
  }

}

/**
 * Interface definition for all context objects.
 */
interface DrupalContextInterface extends ArrayAccess {

  /**
   * Access the HTTP request information.
   *
   * @return DrupalRequestInterface
   */
  public function request();

  /**
   * Register a class as the handler for a given context.
   *
   * @param string $context
   *   The context key to register for, such as "http:get".
   * @param string $class
   *   The name of the class that will handle this context key, unless overridden.
   *   The class must implement ContextHandlerInterface.
   * @param array $params
   *   An array of configuration options for the class.
   */
  public function registerHandler($context, $class, $params = array());

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

/**
 * Internal class that is meant for context values existence check optimization.
 * 
 * Do not use elsewhere.
 */
class ContextOffsetDoesNotExists {}

/**
 * Default Drupal context object.
 *
 * It handles routing of context requests to handlers.
 */
class DrupalContext implements DrupalContextInterface {

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
   * The initial request object that started this page request.
   *
   * @var DrupalRequestInterface
   */
  protected $request = NULL;

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

  public function __construct(DrupalRequestInterface $request, $parent_id = NULL) {
    $this->request = $request;

    $this->queryString = $this->request->get('q');

    if ($parent_id) {
      $this->parentId = $parent_id;
    }
  }

  /**
   * Returns the top-most context object, which is the active object.
   *
   * @return DrupalContextInterface
   */
  public static function getActiveContext() {
   return end(self::$contextStack);
  }

  /**
   * Implmenents DrupalContextInterface::request().
   */
  public function request() {
    return $this->request;
  }

  public function arg($index) {
    // @todo Eventually reverse this, so arg() calls context->arg().
    return arg($index, $this->queryString);
  }

  /**
   * Implements ArrayAccess::offsetExists().
   */
  public function offsetExists($offset) {
    if (!array_key_exists($offset, $this->context)) {
      $this->offsetGet($offset);
    }
    return !$this->context[$offset] instanceof ContextOffsetDoesNotExists;
  }

  /**
   * Implements ArrayAccess:offsetGet().
   */
  public function offsetGet($offset) {
    if (!$this->locked) {
      throw new ContextNotLockedException(t('This context object has not been locked. It must be locked before it can be used.'));
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
          if (!isset($this->handlers[$context_key]) && class_exists($this->handlerClasses[$context_key]['class'])) {
            $this->handlers[$context_key] = new $this->handlerClasses[$context_key]['class']($this, $this->handlerClasses[$context_key]['params']);
          }

          if (isset($this->handlers[$context_key])) {
            $this->context[$offset] = $this->handlers[$context_key]->getValue($args);
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
            $this->context[$offset] = new ContextOffsetDoesNotExists;
            // FIXME: pounard: This exception masks the affectation uper, should
            // it be kept? Is that exception fatal for runtime or not? Another
            // problem is that another hit to this method won't throw the
            // exception another time if key exists in array, this is a weird
            // and inconsistent behavior?
            throw new ContextParentContextNotExistsException('The parent of the current butler object does not exist anymore');
          }
        } else {
          $this->context[$offset] = new ContextOffsetDoesNotExists;
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
      throw new ContextLockedException(t('This context object has been locked. It no longer accepts new explicit context sets.'));
    }
    // Set an explicit override for a given context value.
    $this->context[$offset] = $value;
  }

  /**
   * Implements ArrayAccess:offsetUnset().
   */
  public function offsetUnset($offset) {
    if ($this->locked) {
      throw new ContextLockedException(t('This context object has been locked. It no longer accepts context clears.'));
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
      throw new ContextLockedException(t('This context object has been locked. It no longer accepts new handler registrations.'));
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
      if ($value instanceof ContextValueInterface) {
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
    return new ContextTracker($this);
  }

  /**
   * Implements DrupalContextInterface::addLayer();
   */
  public function addLayer() {

    $layer = new self($this->request, spl_object_hash($this));
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

/**
 * Transaction-like class for the context stack.
 *
 * When this class is destroyed, so its its corresponding context object.
 */
class ContextTracker {

  /**
   * The context object we're tracking.
   *
   * @var DrupalContext
   */
  protected $context;

  /**
   * Constructor
   *
   * @var DrupalContext $context
   *   The context object we should be tracking.
   */
  public function __construct($context) {
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

/**
 * Returns the currently active context object.
 *
 * The return value from this funciton should never be statically cached. Doing
 * so could lead to strange behavior if it has been removed from the stack, as
 * it may no longer be valid and the object may even have been deleted.
 *
 * @return DrupalContextInterface
 */
function drupal_get_context() {
  return DrupalContext::getActiveContext();
}

/**
 * Interface for context handler objects.
 */
interface ContextHandlerInterface {

  /**
   * Retrieves the value for this context key.
   *
   * This value must be assumed to be immutable within a given request.
   *
   * @param array $args
   *   Arguments to pass into the context handler.  Arguments are derived from
   *   the portion of the context key after the key fragment that led to this
   *   handler.
   * @return mixed
   *   The corresponding value for this context. Return here an new instance of
   *   ContextOffsetDoesNotExists if you don't have any value corresponding to
   *   the given arguments to provide: this will cause the context to stop
   *   value lookup for this offset.
   */
  public function getValue(array $args = array());
}

/**
 * Base implementation of a Context Handler.
 *
 * Other handlers may extend this class to make their job easier.
 */
abstract class ContextHandlerAbstract implements ContextHandlerInterface {

  /**
   * Reference to the context object.
   *
   * Note: This creates a circular reference.  We should probably get rid of it
   * and pass it every time.
   *
   * @todo Get rid of this property and avoid the circular dependency.
   *
   * @var DrupalContextInterface
   */
  protected $context;

  /**
   * Parameters for the context handler.
   *
   * @var array
   */
  protected $params;

  public function __construct(DrupalContextInterface $context, $params) {
    $this->context = $context;
    $this->params = $params;
  }
}

/**
 * ContextHandlerHttp
 */
class ContextHandlerHttp extends ContextHandlerAbstract {

  public function getValue(array $args = array()) {
    // @todo Make this actually generic.
    $param = $args[0];
    if ($this->params['query'] == 'GET') {
      return $this->context->request()->get($param);
    }
    if ($this->params['query'] == 'POST') {
      return $this->context->request()->post($param);
    }
  }
}

/**
 * Interface for context value objects.
 *
 * ContextValueInterface includes a method - contextKey() - that will return a
 * load key for that object. It is up to the object to return something
 * meaningful. The load key is the value by which we can load that object later,
 * such as nid, view machine name, etc.
 */
interface ContextValueInterface {

  /**
   * Retrieves the key for the object to be loaded
   *
   * @return mixed
   *   A key for the object to be returned by the appropriate handler
   */
  public function contextKey();
}
