<?php

/**
 * @file
 * Contains \Drupal\aggregator\Plugin\block\block\AggregatorFeedBlock.
 */

namespace Drupal\aggregator\Plugin\block\block;

use Drupal\block\BlockBase;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides an 'Aggregator feed' block with the latest items from the feed.
 *
 * @Plugin(
 *   id = "aggregator_feed_block",
 *   admin_label = @Translation("Aggregator feed"),
 *   module = "aggregator",
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
      'feed' => NULL,
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
    $feeds = entity_load_multiple('aggregator_feed');
    $options = array();
    foreach ($feeds as $feed) {
      $options[$feed->id()] = $feed->label();
    }
    $form['feed'] = array(
      '#type' => 'select',
      '#title' => t('Select the feed that should be displayed'),
      '#default_value' => $this->configuration['feed'],
      '#options' => $options,
    );
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
    $this->configuration['feed'] = $form_state['values']['feed'];

  }

  /**
   * Implements \Drupal\block\BlockBase::build().
   */
  public function build() {
    $feed = aggregator_feed_load($this->configuration['feed']);
    if ($feed) {
      module_load_include('inc', 'aggregator', 'aggregator.pages');
      $items = aggregator_load_feed_items('source', $feed, $this->configuration['block_count']);
      if ($items) {
        $build['items'] = entity_view_multiple($items, 'default');
        $build['read_more'] = array(
          '#theme' => 'more_link',
          '#url' => 'aggregator/sources/' . $feed->id(),
          '#title' => t("View this feed's recent news."),
        );
        return $build;
      }
    }
  }

}
