<?php

/**
 * @file
 * Contains \Drupal\language\Plugin\block\block\LanguageBlock.
 */

namespace Drupal\language\Plugin\block\block;

use Drupal\block\BlockBase;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides a 'Language switcher' block.
 *
 * @Plugin(
 *   id = "language_block",
 *   admin_label = @Translation("Language switcher"),
 *   module = "language",
 *   derivative = "Drupal\language\Plugin\Derivative\LanguageBlock"
 * )
 */
class LanguageBlock extends BlockBase {

  /**
   * Overrides \Drupal\block\BlockBase::blockAccess().
   */
  function blockAccess() {
    return language_multilingual();
  }

  /**
   * Implements \Drupal\block\BlockBase::blockBuild().
   */
  protected function blockBuild() {
    $build = array();
    $path = drupal_is_front_page() ? '<front>' : current_path();
    list($plugin_id, $type) = explode(':', $this->getPluginId());
    $links = language_negotiation_get_switch_links($type, $path);

    if (isset($links->links)) {
      $build = array(
        '#theme' => 'links__language_block',
        '#links' => $links->links,
        '#attributes' => array(
          'class' => array(
            "language-switcher-{$links->method_id}",
          ),
        ),
      );
    }
    return $build;
  }

}
