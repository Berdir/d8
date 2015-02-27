<?php

/**
 * @file
 * Contains \Drupal\node\Entity\NodeHtmlRouteProvider.
 */

namespace Drupal\node\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\Core\Entity\Routing\EntityRouteProviderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides HTML routes for the node entity type.
 */
class NodeHtmlRouteProvider extends DefaultHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $collection->get('entity.node.canonical')
      ->setDefault('_entity_view', NULL)
      ->setDefault('_controller', '\Drupal\node\Controller\NodeViewController::view');

    $collection->get('entity.node.delete_form')
      ->setOption('_node_operation_route', TRUE);

    $collection->get('entity.node.edit_form')
      ->setOption('_node_operation_route', TRUE);

    return $collection;
  }

}
