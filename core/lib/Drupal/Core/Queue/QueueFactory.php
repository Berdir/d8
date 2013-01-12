<?php

/**
 * @file
 * Contains \Drupal\Core\Queue\QueueFactory.
 */

namespace Drupal\Core\Queue;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the key/value store factory.
 */
class QueueFactory {

  /**
   * Instantiated queues, keyed by name.
   *
   * @var array
   */
  protected $queues = array();

  /**
   * var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;


  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   */
  function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * Constructs a new key/value store for a given collection name.
   *
   * @param string $name
   *   The name of the queue to work with.
   * @param bool $reliable
   *   (optional) TRUE if the ordering of items and guaranteeing every item executes at
   *   least once is important, FALSE if scalability is the main concern. Defaults
   *   to FALSE.
   *
   * @return \Drupal\Core\QueueStore\QueueInterface
   *   A key/value store implementation for the given $collection.
   */
  public function get($name, $reliable = FALSE) {
    global $conf;
    if (!isset($this->queues[$name])) {
      if ($reliable && isset($conf['queue_reliable_service_' . $name])) {
        $service_name = $conf['queue_reliable_service_' . $name];
      }
      elseif (isset($conf['queue_service_' . $name])) {
        $service_name = $conf['queue_service_' . $name];
      }
      elseif (isset($conf['queue_default'])) {
        $service_name = $conf['queue_default'];
      }
      else {
        $service_name = 'queue.database';
      }
      $this->queues[$name] = $this->container->get($service_name)->get($name);
    }
    return $this->queues[$name];
  }
}

