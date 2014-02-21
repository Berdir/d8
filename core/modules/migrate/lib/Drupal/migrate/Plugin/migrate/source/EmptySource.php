<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\Constants.
 */

namespace Drupal\migrate\Plugin\migrate\source;

/**
 * Source returning an empty row.
 *
 * @MigrateSource(
 *   id = "empty"
 * )
 */
class EmptySource extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function fields() {
    array();
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator(array(array('id' => '')));
  }

  public function __toString() {
    return '';
  }

  public function getIds() {
    $ids['id']['type'] = 'string';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return 1;
  }

}
