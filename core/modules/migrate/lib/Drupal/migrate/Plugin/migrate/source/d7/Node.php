<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d7\Node.
 */

namespace Drupal\migrate\Plugin\migrate\source\d7;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 7 node source.
 *
 * @PluginID("drupal7_node")
 */
class Node extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->database->select('node', 'n')
     ->fields('n', array('nid', 'vid', 'language', 'title', 'uid',
       'status', 'created', 'changed', 'comment', 'promote', 'sticky',
       'tnid', 'translate'))
     ->condition('n.type', $this->sourceType)
     ->orderBy('n.changed');

  }

  public function getCurrentKey() {
    // TODO: Implement getCurrentKey() method.
  }

  public function fields() {
    // TODO: Implement fields() method.
  }

  public function getIgnored() {
    // TODO: Implement getIgnored() method.
  }

  public function getProcessed() {
    // TODO: Implement getProcessed() method.
  }

  public function resetStats() {
    // TODO: Implement resetStats() method.
  }

  /**
   * (PHP 5 &gt;= 5.1.0)<br/>
   * Count elements of an object
   * @link http://php.net/manual/en/countable.count.php
   * @return int The custom count as an integer.
   * </p>
   * <p>
   * The return value is cast to an integer.
   */
  public function count() {
    // TODO: Implement count() method.
  }


}
