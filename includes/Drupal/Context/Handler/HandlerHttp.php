<?php

namespace Drupal\Context\Handler;

use \Drupal\Context\Handler;

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

  public function __construct(\Drupal\Context\ContextInterface $context, $params = array()) {
    parent::__construct($context, $params);

    // Init Symfony Request.
    $this->request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
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
          break;
        case 'server':
          $this->params[$property] = $this->request->server->all();
          break;
        case 'request_body':
          $this->params[$property] = $this->request->getContent();
          break;
      }
    }

    return $this->params[$property];
  }
}
