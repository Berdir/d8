<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\AggregatorFeed.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;
use Drupal\migrate\Plugin\RequirementsInterface;

/**
 * Drupal 6 feed source from database.
 *
 * @PluginId("drupal6_aggregator_feed")
 */
class AggregatorFeed extends Drupal6SqlBase implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('aggregator_feed', 'af')
      ->fields('af', array(
        'fid',
        'title',
        'url',
        'refresh',
        'checked',
        'link',
        'description',
        'image',
        'etag',
        'modified',
        'block',
      ));

    $query->orderBy('fid');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'fid' => t('The feed ID.'),
      'title' => t('Title of the feed.'),
      'url' => t('URL to the feed.'),
      'refresh' => t('Refresh frequency in seconds.'),
      'checked' => t('Last-checked unix timestamp.'),
      'link' => t('Parent website of feed.'),
      'description' => t('Parent website\'s description fo the feed.'),
      'image' => t('An image representing the feed.'),
      'etag' => t('Entity tage HTTP response header.'),
      'modified' => t('When the feed was last modified.'),
      'block' => t("Number of items to display in the feed's block."),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    return $this->moduleExists('aggregator');
  }
}
