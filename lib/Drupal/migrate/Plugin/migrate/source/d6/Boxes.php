<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Boxes.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\d6\Drupal6SqlBase;

/**
 * Drupal 6 block source from database.
 *
 * @PluginId("drupal6_boxes")
 */
class Boxes extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->database
      ->select('boxes', 'b')
      ->fields('b', array('bid', 'body', 'info', 'format'));
    $query->orderBy('bid');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'bid' => t('The numeric identifier of the block/box'),
      'body' => t('The block/box content'),
      'info' => t('Admin title of the block/box.'),
      'format' => t('Input format of the custom block/box content.'),
    );
  }

}
