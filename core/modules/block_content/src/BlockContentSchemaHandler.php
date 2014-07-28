<?php

/**
 * @file
 * Contains \Drupal\block_content\BlockContentSchemaHandler.
 */

namespace Drupal\block_content;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Schema\ContentEntitySchemaHandler;

/**
 * Defines the block content schema handler.
 */
class BlockContentSchemaHandler extends ContentEntitySchemaHandler {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type) {
    $schema = parent::getEntitySchema($entity_type);

    // Marking the respective fields as NOT NULL makes the indexes more
    // performant.
    $schema['block_content']['fields']['info']['not null'] = TRUE;

    $schema['block_content']['unique keys'] += array(
      'block_content__info' => array('info'),
    );

    return $schema;
  }

}
