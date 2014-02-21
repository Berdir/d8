<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Boxes.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

/**
 * Drupal 6 block source from database.
 *
 * @MigrateSource("d6_box")
 */
class Box extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('boxes', 'b')
      ->fields('b', array('bid', 'body', 'info', 'format'));
    $query->orderBy('bid');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'bid' => $this->t('The numeric identifier of the block/box'),
      'body' => $this->t('The block/box content'),
      'info' => $this->t('Admin title of the block/box.'),
      'format' => $this->t('Input format of the custom block/box content.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['bid']['type'] = 'integer';
    return $ids;
  }
}
