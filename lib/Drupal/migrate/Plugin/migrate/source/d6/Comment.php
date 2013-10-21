<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Comment.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 6 comment source from database.
 *
 * @fixme Make this work.
 *
 * @PluginId("drupal6_comment")
 */
class Comment extends SqlBase {
  function query() {
    $query = $this->database
      ->select('comments', 'c')
      ->fields('c', array('cid', 'pid', 'nid', 'uid', 'subject',
        'comment', 'hostname', 'timestamp', 'status', 'thread', 'name',
        'mail', 'homepage', 'format'));
    $query->join('node', 'n', 'c.nid = n.nid');
    $query->fields('n', array('type'));
    return $query;
  }

  /**
   * Derived classes must implement fields(), returning a list of available
   * source fields.
   *
   * @return array
   *   Keys: machine names of the fields (to be passed to addFieldMapping)
   *   Values: Human-friendly descriptions of the fields.
   */
  public function fields() {
    // TODO: Implement fields() method.
  }

  /**
   * Derived classes must implement computeCount(), to retrieve a fresh count of
   * source records.
   *
   * @return int
   */
  function computeCount() {
    // TODO: Implement computeCount() method.
  }
}
