<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Menu.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 6 menu source from database.
 *
 * @PluginId("drupal6_menu")
 */
class Menu extends SqlBase {

  /**
   * {@inheritdoc}
   */
  function query() {
    $query = $this->database
      ->select('menu_custom', 'm')
      ->fields('m', array('menu_name', 'title', 'description'));
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'menu_name' => t('The menu name. Primary key.'),
      'title' => t('The human-readable name of the menu.'),
      'description' => t('A description of the menu'),
    );
  }

}
