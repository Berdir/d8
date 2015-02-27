<?php

/**
 * @file
 * Contains Drupal\comment\CommentHtmlRouteProvider.
 */

namespace Drupal\comment;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides HTML routes for the comment entity type.
 */
class CommentHtmlRouteProvider extends DefaultHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $collection->get('entity.comment.canonical')
      ->setDefaults([
        '_title_callback' => '\Drupal\comment\Controller\CommentController::commentPermalinkTitlek',
        '_controller' => '\Drupal\comment\Controller\CommentController::commentPermalink',
      ]);

    return $collection;
  }

}
