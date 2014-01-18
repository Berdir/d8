<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\AggregatorItem.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\RequirementsInterface;


/**
 * Drupal 6 aggregator item source from database.
 *
 * @PluginId("drupal6_aggregator_item")
 */
class AggregatorItem extends Drupal6SqlBase implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('aggregator_item', 'ai')
      ->fields('ai', array('iid', 'fid', 'title', 'link', 'author',
        'description', 'timestamp', 'guid'))
      ->orderBy('iid');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'iid' => t('Primary Key: Unique ID for feed item.'),
      'fid' => t('The {aggregator_feed}.fid to which this item belongs.'),
      'title' => t('Title of the feed item.'),
      'link' => t('Link to the feed item.'),
      'author' => t('Author of the feed item.'),
      'description' => t('Body of the feed item.'),
      'timestamp' => t('Post date of feed item, as a Unix timestamp.'),
      'guid' => t('Unique identifier for the feed item.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    return $this->moduleExists('aggregator');
  }

}
