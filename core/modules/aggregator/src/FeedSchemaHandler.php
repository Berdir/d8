<?php

/**
 * @file
 * Contains \Drupal\aggregator\FeedSchemaHandler.
 */

namespace Drupal\aggregator;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Schema\ContentEntitySchemaHandler;

/**
 * Defines the feed schema handler.
 */
class FeedSchemaHandler extends ContentEntitySchemaHandler {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type) {
    $schema = parent::getEntitySchema($entity_type);

    // Marking the respective fields as NOT NULL makes the indexes more
    // performant.
    $schema['aggregator_feed']['fields']['url']['not null'] = TRUE;
    $schema['aggregator_feed']['fields']['queued']['not null'] = TRUE;
    $schema['aggregator_feed']['fields']['title']['not null'] = TRUE;

    $schema['aggregator_feed']['indexes'] += array(
      'aggregator_feed__url'  => array(array('url', 255)),
      'aggregator_feed__queued' => array('queued'),
    );
    $schema['aggregator_feed']['unique keys'] += array(
      'aggregator_feed__title' => array('title'),
    );

    return $schema;
  }

}
