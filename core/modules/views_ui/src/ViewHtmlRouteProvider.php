<?php

/**
 * @file
 * Contains Drupal\views_ui\ViewHtmlRouteProvider.
 */

namespace Drupal\views_ui;


use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;

/**
 * Provides HTML routes for the view entity type.
 */
class ViewHtmlRouteProvider extends DefaultHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $collection->get('entity.view.edit_form')
      // Replace the _entity_form with a custom _controller.
      ->setDefault('_entity_form', NULL)
      ->setDefault('_controller', '\Drupal\views_ui\Controller\ViewsUIController::edit')
      ->setOptions(['parameters' => ['view' => ['tempstore' => TRUE, 'type' => 'entity:view']]]);

    return $collection;
  }

}
