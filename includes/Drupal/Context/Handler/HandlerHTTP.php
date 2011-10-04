<?php

namespace Drupal\Context\Handler;

/**
 * HTTP Context Handler implementation.
 */
class HandlerHTTP extends \Drupal\Context\Handler\HandlerAbstract {

  /**
   * Symfony Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $symfony_request = NULL;

  /**
   * List of stored HTTP propetries.
   *
   * @var array
   */
  static $http_propetries = array(
    'method', 'url', 'accept_types', 'domain', 'request_args', 'query_args',
    'languages', 'files', 'cookies', 'headers', 'server', 'request_body'
  );

  public function __construct(\Drupal\Context\ContextInterface $context, $params = array()) {
    $this->context = $context;
    $this->params = $params;

    // Check every HTTP propetry and populate it if needed.
    foreach (self::$http_propetries as $propetry) {
      if (isset($this->params[$propetry])) {
        continue;
      }

      // Init Symfony Request.
      if (empty($this->symfony_request)) {
        $this->symfony_request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
      }

      // Populate HTTP propetries.
      switch ($propetry) {
        case 'method':
          $this->params[$propetry] = $this->symfony_request->getMethod();
          break;
        case 'url':
          $this->params[$propetry] = $this->symfony_request->getUri();
          break;
        case 'accept_types':
          $this->params[$propetry] = $this->symfony_request->getAcceptableContentTypes();
          break;
        case 'domain':
          $this->params[$propetry] = $this->symfony_request->getHost();
          break;
        case 'request_args':
          $this->params[$propetry] = $this->symfony_request->request->all();
          break;
        case 'query_args':
          $this->params[$propetry] = $this->symfony_request->query->all();
          break;
        case 'languages':
          $this->params[$propetry] = $this->symfony_request->getLanguages();
          break;
        case 'files':
          $this->params[$propetry] = $this->symfony_request->files->all();
          break;
        case 'cookies':
          $this->params[$propetry] = $this->symfony_request->cookies->all();
          break;
        case 'headers':
          $this->params[$propetry] = $this->symfony_request->headers->all();
          break;
        case 'server':
          $this->params[$propetry] = $this->symfony_request->server->all();
          break;
        case 'request_body':
          $this->params[$propetry] = $this->symfony_request->getContent();
          break;
        default:
          $this->params[$propetry] = '';
      }
    }
  }

  public function getValue(array $args = array()) {
    return $this->params[implode(':', $args)];
  }
}
