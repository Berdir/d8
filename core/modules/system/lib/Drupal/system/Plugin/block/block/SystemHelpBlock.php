<?php

/**
 * @file
 * Contains \Drupal\system\Plugin\block\block\SystemHelpBlock.
 */

namespace Drupal\system\Plugin\block\block;

use Drupal\block\BlockBase;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides a 'System Help' block.
 *
 * @Plugin(
 *   id = "system_help_block",
 *   admin_label = @Translation("System Help"),
 *   module = "system"
 * )
 */
class SystemHelpBlock extends BlockBase {

  /**
   * Stores the help text associated with the active menu item.
   *
   * @var string
   */
  protected $help;

  /**
   * Overrides \Drupal\block\BlockBase::blockAccess().
   */
  public function blockAccess() {
    $this->help = menu_get_active_help();
    return (bool) $this->help;
  }

  /**
   * Implements \Drupal\block\BlockBase::blockBuild().
   */
  protected function blockBuild() {
    return array(
      '#children' => $this->help,
    );
  }

}
