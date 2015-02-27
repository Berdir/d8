<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider.
 */

namespace Drupal\Core\Entity\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides a default implementation of an HTML route provider.
 *
 * It provides:
 * - A view route with title callback.
 * - An edit route with title callback.
 * - A delete route with title callback.
 */
class DefaultHtmlRouteProvider implements EntityRouteProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = new RouteCollection();

    $entity_type_id = $entity_type->id();

    if ($entity_type->hasLinkTemplate('canonical')) {
      $route = (new Route($entity_type->getLinkTemplate('canonical')));
      $route
        ->addDefaults([
          '_entity_view' => "{$entity_type_id}.full",
          '_title_callback' => '\Drupal\Core\Entity\Controller\EntityController::title',
        ])
        ->setRequirement('_entity_access', "{$entity_type_id}.view")
        ->setOption('parameters', [
          $entity_type_id => ['type' => 'entity:' . $entity_type_id],
        ]);
      $collection->add("entity.{$entity_type_id}.canonical", $route);
    }

    if ($entity_type->hasLinkTemplate('edit-form')) {
      $route = (new Route($entity_type->getLinkTemplate('edit-form')));
      // Use the "edit" form handler, otherwise default.
      $operation = 'default';
      if ($entity_type->getFormClass('edit')) {
        $operation = 'edit';
      }
      $route
        ->setDefaults([
          '_entity_form' => "{$entity_type_id}.{$operation}",
          '_title_callback' => 'Drupal\Core\Entity\Controller\EntityController::editTitle'
        ])
        ->setRequirement('_entity_access', "{$entity_type_id}.update")
        ->setOption('parameters', [
          $entity_type_id => ['type' => 'entity:' . $entity_type_id],
        ]);
      $collection->add("entity.{$entity_type_id}.edit_form", $route);
    }

    if ($entity_type->hasLinkTemplate('delete-form')) {
      $route = (new Route($entity_type->getLinkTemplate('delete-form')));
      $route
        ->addDefaults([
          '_entity_form' => "{$entity_type_id}.delete",
          '_title_callback' => 'Drupal\Core\Entity\Controller\EntityController::deleteTitle',
        ])
        ->setRequirement('_entity_access', "{$entity_type_id}.delete")
        ->setOption('parameters', [
          $entity_type_id => ['type' => 'entity:' . $entity_type_id],
        ]);
      $collection->add("entity.{$entity_type_id}.delete_form", $route);
    }

    return $collection;
  }

}
