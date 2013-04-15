<?php

/**
 * @file
 * Contains \Drupal\search\Plugin\block\block\SearchBlock.
 */

namespace Drupal\search\Plugin\block\block;

use Drupal\block\BlockBase;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides a 'Search form' block.
 *
 * @Plugin(
 *   id = "search_form_block",
 *   admin_label = @Translation("Search form"),
 *   module = "search"
 * )
 */
class SearchBlock extends BlockBase {

  /**
   * Overrides \Drupal\block\BlockBase::blockAccess().
   */
  public function blockAccess() {
    return user_access('search content');
  }

  /**
   * Implements \Drupal\block\BlockBase::blockBuild().
   */
  protected function blockBuild() {
    return array(drupal_get_form('search_block_form'));
  }

}
