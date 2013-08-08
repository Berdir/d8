<?php

/**
 * @file
 * Contains \Drupal\custom_block\Plugin\Menu\LocalAction\CustomBlockAddLocalAction.
 */

namespace Drupal\custom_block\Plugin\Menu\LocalAction;

use Drupal\Core\Annotation\Menu\LocalAction;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Menu\LocalActionBase;

/**
 * @LocalAction(
 *   id = "custom_block_add_action",
 *   route_name = "custom_block_add_page",
 *   title = @Translation("Add custom block"),
 *   appears_on = {"block_admin_display"}
 * )
 */
class CustomBlockAddLocalAction extends LocalActionBase {

}
