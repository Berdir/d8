<?php

/**
 * @file
 * Contains \Drupal\aggregator\Plugin\block\block\AggregatorCategoryBlock.
 */

namespace Drupal\aggregator\Plugin\block\block;

use Drupal\block\BlockBase;
use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides an 'Aggregator category' block for the latest items in a category.
 *
 * @Plugin(
 *   id = "aggregator_category_block",
 *   subject = @Translation("Aggregator category"),
 *   module = "aggregator",
 *   derivative = "Drupal\aggregator\Plugin\Derivative\AggregatorCategoryBlock"
 * )
 */
class AggregatorCategoryBlock extends BlockBase {

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
    $id = $this->getPluginId();
    if ($category = db_query('SELECT cid, title, block FROM {aggregator_category} WHERE cid = :cid', array(':cid' => $id))->fetchObject()) {
      $result = db_query_range('SELECT i.* FROM {aggregator_category_item} ci LEFT JOIN {aggregator_item} i ON ci.iid = i.iid WHERE ci.cid = :cid ORDER BY i.timestamp DESC, i.iid DESC', 0, $this->configuration['block_count'], array(':cid' => $category->cid));
      $read_more = theme('more_link', array('url' => 'aggregator/categories/' . $category->cid, 'title' => t("View this category's recent news.")));

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
      return array();
    }
  }

}
