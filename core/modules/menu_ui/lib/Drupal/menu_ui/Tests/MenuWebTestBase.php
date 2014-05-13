<?php

/**
 * @file
 * Contains \Drupal\menu_ui\Tests\MenuWebTestBase.
 */

namespace Drupal\menu_ui\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Defines a base class for menu web tests.
 */
class MenuWebTestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('menu_ui', 'menu_link_content');

  /**
   * Fetchs the menu item from the database and compares it to expected item.
   *
   * @param int $menu_plugin_id
   *   Menu item id.
   * @param array $expected_item
   *   Array containing properties to verify.
   */
  function assertMenuLink($menu_plugin_id, array $expected_item) {
    // Retrieve menu link.
    /** @var \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree */
    $menu_tree = $this->container->get('menu.link_tree');
    $menu_tree->resetDefinitions();
    $definition = $menu_tree->getDefinition($menu_plugin_id);

    $entity = NULL;

    // Pull the path from the menu link content.
    if (strpos($menu_plugin_id, 'menu_link_content') === 0) {
      list(, $uuid) = explode(':', $menu_plugin_id, 2);
      $links = \Drupal::entityManager()->getStorage('menu_link_content')->loadByProperties(array('uuid' => $uuid));
      /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $link */
      $entity = reset($links);
    }

    if (isset($expected_item['children'])) {
      $child_ids = array_values($menu_tree->getChildIds($menu_plugin_id));
      sort($expected_item['children']);
      if ($child_ids) {
        sort($child_ids);
      }
      $this->assertEqual($expected_item['children'], $child_ids);
      unset($expected_item['children']);
    }

    if (isset($expected_item['parents'])) {
      $parent_ids = array_values($menu_tree->getParentIds($menu_plugin_id));
      $this->assertEqual($expected_item['parents'], $parent_ids);
      unset($expected_item['parents']);
    }

    if (isset($expected_item['langcode']) && $entity) {
      $this->assertEqual($entity->langcode->value, $expected_item['langcode']);
      unset($expected_item['langcode']);
    }

    foreach ($expected_item as $key => $value) {
      $this->assertEqual($definition[$key], $value);
    }
  }

}
