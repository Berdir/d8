<?php

/**
 * @file
 * Contains \Drupal\file\FileSchemaHandler.
 */

namespace Drupal\file;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Schema\ContentEntitySchemaHandler;

/**
 * Defines the file schema handler.
 */
class FileSchemaHandler extends ContentEntitySchemaHandler {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type) {
    $schema = parent::getEntitySchema($entity_type);

    // Marking the respective fields as NOT NULL makes the indexes more
    // performant.
    $schema['file_managed']['fields']['status']['not null'] = TRUE;
    $schema['file_managed']['fields']['changed']['not null'] = TRUE;
    $schema['file_managed']['fields']['uri']['not null'] = TRUE;

    // @todo There should be a 'binary' field type or setting.
    $schema['file_managed']['fields']['uri']['binary'] = TRUE;
    $schema['file_managed']['indexes'] += array(
      'file__status' => array('status'),
      'file__changed' => array('changed'),
    );
    $schema['file_managed']['unique keys'] += array(
      'file__uri' => array('uri'),
    );

    return $schema;
  }

}
