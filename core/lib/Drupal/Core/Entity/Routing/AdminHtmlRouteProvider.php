<?php

/**
 * @file
 * Contains Drupal\Core\Entity\Routing\AdminHtmlRouteProvider.
 */

namespace Drupal\Core\Entity\Routing;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides routes for the taxonomy_term entity.
 */
class AdminHtmlRouteProvider extends DefaultHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $entity_type_id = $entity_type->id();

    if ($route = $collection->get("entity.{$entity_type_id}.edit_form")) {
      $route->setOption('_admin_route', TRUE);
    }
    if ($route = $collection->get("entity.{$entity_type_id}.delete_form")) {
      $route->setOption('_admin_route', TRUE);
    }

    return $collection;
  }

}
