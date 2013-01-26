<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Query\QueryFactory.
 */

namespace Drupal\Core\Entity\Query;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Factory class Creating entity query objects.
 */
class QueryFactory {

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
   * @param string $entity_type
   * @param string $conjunction
   * @return QueryInterface
   */
  public function get($entity_type, $conjunction = 'AND') {
    $service_name = drupal_container()->get('plugin.manager.entity')->getStorageController($entity_type)->getQueryServicename();
    return $this->container->get($service_name)->get($entity_type, $conjunction);
  }

}
