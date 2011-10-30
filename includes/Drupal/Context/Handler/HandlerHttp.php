<?php

namespace Drupal\Context\Handler;

use \Drupal\Context\ContextInterface;
use \Symfony\Component\HttpFoundation\Request;
use \Drupal\Context\Handler;

/**
 * HTTP Context Handler implementation.
 */
class HandlerHttp extends HandlerAbstract {

  /**
   * Symfony Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request = NULL;

  /**
   * List of stored HTTP properties.
   *
   * @var array
   */
  static protected $httpProperties = array(
    'method', 'url', 'accept_types', 'domain', 'request_args', 'query_args',
    'languages', 'files', 'cookies', 'headers', 'server', 'request_body'
  );

  public function __construct($params = array()) {
    // If the values key is set in $params, we assume the caller wants to inject
    // HTTP information into the request object rather than using the
    // superglobals.  Otherwise we assume that this is a "normal" request and
    // simply wrap the superglobals per usual.
    if (isset($params['values'])) {
      // Set default values.
      $params['values'] += array(
        'uri' => 'http://localhost',
        'method' => 'GET',
        'parameters' => array(),
        'cookies' => array(),
        'files' => array(),
        'server' => array(),
        'content' => NULL,
      );

      $values = $params['values'];
      $this->request = Request::create($values['uri'], $values['method'], $values['parameters'], $values['cookies'], $values['files'], $values['server'], $values['content']);
    }
    else {
      $this->request = Request::createFromGlobals();
    }
  }

  public function getValue(array $args = array(), ContextInterface $context = null) {
    $property = $args[0];

    // Check whether requested property is known.
    if (!in_array($property, self::$httpProperties)) {
      return;
    }

    // Populate HTTP property if it is not set.
    if (!isset($this->params[$property])) {
      switch ($property) {
        case 'method':
          $this->params[$property] = $this->request->getMethod();
          break;
        case 'url':
          $this->params[$property] = $this->request->getUri();
          break;
        case 'accept_types':
          $this->params[$property] = $this->request->getAcceptableContentTypes();
          break;
        case 'domain':
          $this->params[$property] = $this->request->getHost();
          break;
        case 'request_args':
          $this->params[$property] = $this->request->request->all();
          break;
        case 'query_args':
          $this->params[$property] = $this->request->query->all();
          break;
        case 'languages':
          // Although HttpFoundation has a getLanguages() method already, it
          // does some case folding that is incompatible with Drupal's language
          // string formats.
          // @todo Revisit this after https://github.com/symfony/symfony/issues/2468
          // is resolved
          $this->params[$property] = $this->request->splitHttpAcceptHeader($this->request->headers->get('Accept-Language'));
          break;
        case 'files':
          $this->params[$property] = $this->request->files->all();
          break;
        case 'cookies':
          $this->params[$property] = $this->request->cookies->all();
          break;
        case 'headers':
          $this->params[$property] = $this->request->headers->all();
          // Cleanup from unnecessary nesting level.
          foreach ($this->params[$property] as &$item) {
            if (is_array($item)) {
              $item = current($item);
            }
          }
          break;
        case 'server':
          $this->params[$property] = $this->request->server->all();
          break;
        case 'request_body':
          $this->params[$property] = $this->request->getContent();
          break;
      }
    }

    // Many of the properties of the HTTP handler are actually arrays that
    // we never want directly, but want a specific element out of. For instance,
    // http:query is an array of GET parameters.  The following routine lets us
    // retrieve third-level properties consistently, so $_GET['foo'] would map
    // to http:query:foo.  The same code also supports cookies, headers, and so
    // forth.
    // Only one level of nesting is supported.  If a GET parameter is itself an
    // array, it will be returned as an array.
    if (!is_array($this->params[$property]) || !isset($args[1])) {
      return $this->params[$property];
    }
    else {
      // Return second nesting level value if it exists.
      if (!empty($args[1]) && isset($this->params[$property][$args[1]])) {
        return $this->params[$property][$args[1]];
      }
      else {
        // We return empty string if there is no
        // second argument key in $this->params[$property].
        return '';
      }
    }
  }
}
