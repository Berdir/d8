<?php

/**
 * @file
 * Contains Drupal\aggregator\FeedHtmlRouteProvider.
 */

namespace Drupal\aggregator;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;

/**
 * Provides HTML routes for the feed entity type.
 */
class FeedHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $collection->get('entity.aggregator_feed.edit_form')
      ->setDefault('_title', 'Configure');

    return $collection;
  }

}
