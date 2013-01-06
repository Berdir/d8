<?php

/**
 * @file
 * Contains \Drupal\block_test\Plugin\block\block\TestCacheBlock.
 */

namespace Drupal\block_test\Plugin\block\block;

use Drupal\block\BlockBase;
use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides a block to test caching.
 *
 * @Plugin(
 *   id = "test_cache",
 *   subject = @Translation("Test block caching"),
 *   module = "block_test"
 * )
 */
class TestCacheBlock extends BlockBase {

  /**
   * Overrides \Drupal\block\BlockBase::blockSettings().
   *
   * Sets a different caching strategy for testing purposes.
   */
  public function blockSettings() {
    return array(
      'cache' => DRUPAL_CACHE_PER_ROLE,
    );
  }

  /**
   * Implements \Drupal\block\BlockBase::blockBuild().
   */
  public function blockBuild() {
    return array(
      '#children' => state()->get('block_test.content'),
    );
  }

}
