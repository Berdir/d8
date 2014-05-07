<?php
/**
 * @file
 * Contains \Drupal\node\NodeStorage.
 */

namespace Drupal\node;

use Drupal\Core\Entity\ContentEntityDatabaseStorage;

/**
 * Provides storage for the 'node' entity type.
 */
class NodeStorage extends ContentEntityDatabaseStorage {

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $schema = parent::getSchema();

    // @todo Revisit index definitions in https://drupal.org/node/2015277.
    $schema['node_revision']['indexes'] += array(
      'node__langcode' => array('langcode'),
    );
    $schema['node_revision']['foreign keys'] += array(
      'node__revision_author' => array(
        'table' => 'users',
        'columns' => array('revision_uid' => 'uid'),
      ),
    );

    $schema['node_field_data']['indexes'] += array(
      'node__changed' => array('changed'),
      'node__created' => array('created'),
      'node__default_langcode' => array('default_langcode'),
      'node__langcode' => array('langcode'),
      'node__frontpage' => array('promote', 'status', 'sticky', 'created'),
      'node__status_type' => array('status', 'type', 'nid'),
      'node__title_type' => array('title', array('type', 4)),
    );

    $schema['node_field_revision']['indexes'] += array(
      'node__default_langcode' => array('default_langcode'),
      'node__langcode' => array('langcode'),
    );

    return $schema;
  }

}
