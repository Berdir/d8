<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\UploadInstance.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\SourceEntityInterface;
use Drupal\migrate\Row;

/**
 * Drupal 6 upload instance source from database.
 *
 * @PluginID("drupal6_upload_instance")
 */
class UploadInstance extends Upload implements SourceEntityInterface {

  /**
   * {@inheritdoc}
   */
  protected function runQuery() {
    $prefix = 'upload';
    $node_types = $this->getDatabase()->query('SELECT type FROM {node_type}')->fetchCol();
    foreach ($node_types as $node_type) {
      $variables[] = $prefix . '_' . $node_type;
    }

    $return = array();
    $values = $this->getDatabase()->query('SELECT name, value FROM {variable} WHERE name IN (:name)', array(':name' => $variables))->fetchAllKeyed();
    foreach ($node_types as $node_type) {
      $name = $prefix . '_' . $node_type;
      if (isset($values[$name])) {
        $enabled = unserialize($values[$name]);
        if ($enabled) {
          $return[$node_type]['node_type'] = $node_type;
        }
      }
    }

    return new \ArrayIterator($return);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array(
      'node_type' => array(
        'type' => 'string',
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Nothing needed here.
  }
}
