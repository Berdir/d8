<?php

/**
 * @file
 * Contains \Drupal\Core\DependencyInjection\DependencySerialization.
 */

namespace Drupal\Core\DependencyInjection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dependency injection friendly methods for serialization.
 */
abstract class DependencySerialization {

  /**
   * An array of service IDs keyed by property name used for serialization.
   *
   * @var array
   */
  protected $_serviceIds = array();

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $this->_serviceIds = array();
    $vars = get_object_vars($this);
    foreach ($vars as $key => $value) {
      if (is_object($value) && isset($value->_serviceId)) {
        // If a class member was instantiated by the dependency injection
        // container, only store its ID so it can be used to get a fresh object
        // on unserialization.
        $this->_serviceIds += array($key => $value->_serviceId);
        unset($vars[$key]);
      }
      // Special case the container, which might not have a service ID.
      elseif ($value instanceof ContainerInterface) {
        $this->_serviceIds[$key] = 'service_container';
        unset($vars[$key]);
      }
    }

    return array_keys($vars);
  }

  /**
   * {@inheritdoc}
   */
  public function __wakeup() {
    $container = \Drupal::getContainer();
    foreach ($this->_serviceIds as $key => $service_id) {
      $this->$key = $container->get($service_id);
    }
    unset($this->_serviceIds);
  }

}
