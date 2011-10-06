<?php

namespace Drupal\Context\Handler;

use \Drupal\Context\ContextInterface as ContextInterface,
    \Symfony\Component\HttpFoundation\Request as Request,
    \Drupal\Context\Handler;

/**
 * HTTP Context Handler implementation.
 */
class HandlerHTTP extends HandlerAbstract {

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

  public function __construct(ContextInterface $context, $params = array()) {
    $this->context = $context;

    // Init Symfony Request.
    if (!empty($params)) {
      // Default values.
      $uri = 'http://localhost';
      $method = 'GET';
      $parameters = array();
      $cookies = array();
      $files = array();
      $server = array();
      $content = NULL;

      // Extract values.
      extract($params);

      $this->request = Request::create($uri, $method, $parameters, $cookies, $files, $server, $content);
    }
    else {
      $this->request = Request::createFromGlobals();
    }
  }

  public function getValue(array $args = array()) {
    $property = $args[0];

    // Check whether requested prperty is known.
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
          $this->params[$property] = $this->request->getLanguages();
          break;
        case 'files':
          $this->params[$property] = $this->request->files->all();
          break;
        case 'cookies':
          $this->params[$property] = $this->request->cookies->all();
          break;
        case 'headers':
          $this->params[$property] = $this->request->headers->all();
          $this->arrayCleanup($this->params[$property]);
          break;
        case 'server':
          $this->params[$property] = $this->request->server->all();
          break;
        case 'request_body':
          $this->params[$property] = $this->request->getContent();
          break;
      }
    }

    if (!is_array($this->params[$property])) {
      return $this->params[$property];
    }
    // If parameter is array we use $args to get proper value of the array.
    array_shift($args);
    return drupal_array_get_nested_value($this->params[$property], $args);
  }

  /**
   * Remove not necessary nesting level.
   *
   * @param array $array
   */
  public function arrayCleanup(&$array) {
    foreach ($array as &$item) {
      if (is_array($item)) {
        $item = current($item);
      }
    }
  }
}
