<?php

/**
 * @file
 * Contains \Drupal\aggregator\ItemSchemaHandler.
 */

namespace Drupal\aggregator;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Schema\ContentEntitySchemaHandler;

/**
 * Defines the item schema handler.
 */
class ItemSchemaHandler extends ContentEntitySchemaHandler {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type) {
    $schema = parent::getEntitySchema($entity_type);

    // Marking the respective fields as NOT NULL makes the indexes more
    // performant.
    $schema['aggregator_item']['fields']['timestamp']['not null'] = TRUE;

    $schema['aggregator_item']['indexes'] += array(
      'aggregator_item__timestamp' => array('timestamp'),
    );
    $schema['aggregator_item']['foreign keys'] += array(
      'aggregator_item__aggregator_feed' => array(
        'table' => 'aggregator_feed',
        'columns' => array('fid' => 'fid'),
      ),
    );

    return $schema;
  }

}
