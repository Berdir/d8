<?php

/**
 * @file
 * Contains \Drupal\user\Entity\UserHtmlRouteProvider.
 */

namespace Drupal\user\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides HTML routes for the user entity type.
 */
class UserHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $collection->get('entity.user.canonical')->setDefault('_title_callback', 'Drupal\user\Controller\UserController::userTitle');

    $collection->get('entity.user.edit_form')
      ->setDefault('_title_callback', 'Drupal\user\Controller\UserController::userTitle');

    $collection->remove('entity.user.delete');

    $route = (new Route('/user/{user}/cancel'))
      ->setDefaults([
        '_title' => 'Cancel account',
        '_entity_form' => 'user.cancel',
      ])
      ->setOption('_admin_route', TRUE)
      ->setRequirement('_entity_access', 'user.delete');
    $collection->add('entity.user.cancel_form', $route);

    return $collection;
  }

}
