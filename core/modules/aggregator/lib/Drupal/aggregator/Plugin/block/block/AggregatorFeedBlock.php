<?php

/**
 * @file
 * Contains \Drupal\aggregator\Plugin\block\block\AggregatorFeedBlock.
 */

namespace Drupal\aggregator\Plugin\block\block;

use Drupal\block\BlockBase;
use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides an 'Aggregator feed' block with the latest items from the feed.
 *
 * @Plugin(
 *   id = "aggregator_feed_block",
 *   subject = @Translation("Aggregator feed"),
 *   module = "aggregator",
 *   derivative = "Drupal\aggregator\Plugin\Derivative\AggregatorFeedBlock"
 * )
 */
class AggregatorFeedBlock extends BlockBase {

  /**
   * Overrides \Drupal\block\BlockBase::settings().
   */
  public function settings() {
    // By default, the block will contain 10 feed items.
    return array(
      'block_count' => 10,
    );
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockAccess().
   */
  public function blockAccess() {
    // Only grant access to users with the 'access news feeds' permission.
    return user_access('access news feeds');
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockForm().
   */
  public function blockForm($form, &$form_state) {
    $form['block_count'] = array(
      '#type' => 'select',
      '#title' => t('Number of news items in block'),
      '#default_value' => $this->configuration['block_count'],
      '#options' => drupal_map_assoc(range(2, 20)),
    );
    return $form;
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockSubmit().
   */
  public function blockSubmit($form, &$form_state) {
    $this->configuration['block_count'] = $form_state['values']['block_count'];
  }

  /**
   * Implements \Drupal\block\BlockBase::build().
   */
  public function build() {
    // Plugin IDs look something like this: aggregator_feed_block:1.
    list(, $id) = explode(':', $this->getPluginId());
    if ($feed = db_query('SELECT fid, title, block FROM {aggregator_feed} WHERE block <> 0 AND fid = :fid', array(':fid' => $id))->fetchObject()) {
      $result = db_query_range("SELECT * FROM {aggregator_item} WHERE fid = :fid ORDER BY timestamp DESC, iid DESC", 0, $this->configuration['block_count'], array(':fid' => $id));
      $read_more = theme('more_link', array('url' => 'aggregator/sources/' . $feed->fid, 'title' => t("View this feed's recent news.")));

      $items = array();
      foreach ($result as $item) {
        $items[] = theme('aggregator_block_item', array('item' => $item));
      }
      // Only display the block if there are items to show.
      if (count($items) > 0) {
        return array(
          '#children' => theme('item_list', array('items' => $items)) . $read_more,
        );
      }
    }
  }

}
