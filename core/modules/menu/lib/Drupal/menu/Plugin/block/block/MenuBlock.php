<?php

/**
 * @file
 * Contains \Drupal\menu\Plugin\block\block\MenuBlock.
 */

namespace Drupal\menu\Plugin\block\block;

use Drupal\system\Plugin\block\block\SystemMenuBlock;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides a generic Menu block.
 *
 * @Plugin(
 *   id = "menu_menu_block",
 *   admin_label = @Translation("Menu"),
 *   module = "menu",
 *   derivative = "Drupal\menu\Plugin\Derivative\MenuBlock"
 * )
 */
class MenuBlock extends SystemMenuBlock {

  /**
   * Implements \Drupal\block\BlockBase::blockBuild().
   */
  protected function blockBuild() {
    list($plugin, $menu) = explode(':', $this->getPluginId());
    return menu_tree($menu);
  }

}
