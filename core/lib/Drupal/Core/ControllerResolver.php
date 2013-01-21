<?php

/**
 * @file
 * Definition of Drupal\Core\ControllerResolver.
 */

namespace Drupal\Core;

use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ControllerResolver to enhance controllers beyond Symfony's basic handling.
 *
 * It adds two behaviors:
 *
 *  - When creating a new object-based controller that implements
 *    ContainerAwareInterface, inject the container into it. While not always
 *    necessary, that allows a controller to vary the services it needs at
 *    runtime.
 *
 *  - By default, a controller name follows the class::method notation. This
 *    class adds the possibility to use a service from the container as a
 *    controller by using a service:method notation (Symfony uses the same
 *    convention).
 */
class ControllerResolver extends BaseControllerResolver {

  /**
   * The injection container that should be injected into all controllers.
   *
   * @var Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Constructs a new ControllerResolver.
   *
   * @param Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A ContainerInterface instance.
   * @param Symfony\Component\HttpKernel\Log\LoggerInterface $logger
   *   (optional) A LoggerInterface instance.
   */
  public function __construct(ContainerInterface $container, LoggerInterface $logger = NULL) {
    $this->container = $container;

    parent::__construct($logger);
  }

  /**
   * Returns a callable for the given controller.
   *
   * @param string $controller
   *   A Controller string.
   *
   * @return mixed
   *   A PHP callable.
   *
   * @throws \LogicException
   *   If the controller cannot be parsed
   *
   * @throws \InvalidArgumentException
   *   If the controller class does not exist
   */
  protected function createController($controller) {
    // class::method
    if (strpos($controller, '::') !== FALSE) {
      list($class, $method) = explode('::', $controller, 2);

      if (!class_exists($class)) {
        throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
      }

      $controller = new $class();
      if ($controller instanceof ContainerAwareInterface) {
        $controller->setContainer($this->container);
      }
      return array($controller, $method);
    }

    // service:method
    if (substr_count($controller, ':') == 1) {
      // controller in the service:method notation
      list($service, $method) = explode(':', $controller, 2);
      return array($this->container->get($service), $method);
    }

    throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
  }
}
