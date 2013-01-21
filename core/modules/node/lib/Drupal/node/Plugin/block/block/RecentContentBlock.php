<?php

/**
 * @file
 * Contains \Drupal\node\Plugin\block\block\RecentContentBlock.
 */

namespace Drupal\node\Plugin\block\block;

use Drupal\block\BlockBase;
use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides a 'Recent content' block.
 *
 * @Plugin(
 *   id = "node_recent_block",
 *   subject = @Translation("Recent content"),
 *   module = "node"
 * )
 */
class RecentContentBlock extends BlockBase {

  /**
   * Overrides \Drupal\block\BlockBase::settings().
   */
  public function settings() {
    return array(
      'block_count' => 10,
    );
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockAccess().
   */
  public function blockAccess() {
    return user_access('access content');
  }

  /**
   * Overrides \Drupal\block\BlockBase::blockForm().
   */
  public function blockForm($form, &$form_state) {
    $form['block_count'] = array(
      '#type' => 'select',
      '#title' => t('Number of recent content items to display'),
      '#default_value' => $this->configuration['block_count'],
      '#options' => drupal_map_assoc(array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 25, 30)),
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
    if ($nodes = node_get_recent($this->configuration['block_count'])) {
      return array(
        '#theme' => 'node_recent_block',
        '#nodes' => $nodes,
      );
    }
    else {
      return array(
        '#children' => t('No content available.'),
      );
    }
  }

}
