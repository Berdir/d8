<?php

/**
 * @file
 * Contains \Drupal\menu_ui\Tests\MenuWebTestBase.
 */

namespace Drupal\menu_ui\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Base class for menu web tests.
 */
abstract class MenuWebTestBase extends WebTestBase {

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
    /** @var \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager */
    $menu_link_manager = $this->container->get('plugin.manager.menu.link');
    $menu_link_manager->resetDefinitions();
    // Reset the static load cache.
    \Drupal::entityManager()->getStorage('menu_link_content')->resetCache();
    $definition = $menu_link_manager->getDefinition($menu_plugin_id);

    $entity = NULL;

    // Pull the path from the menu link content.
    if (strpos($menu_plugin_id, 'menu_link_content') === 0) {
      list(, $uuid) = explode(':', $menu_plugin_id, 2);
      $links = \Drupal::entityManager()->getStorage('menu_link_content')->loadByProperties(array('uuid' => $uuid));
      /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $link */
      $entity = reset($links);
    }

    if (isset($expected_item['children'])) {
      $child_ids = array_values($menu_link_manager->getChildIds($menu_plugin_id));
      sort($expected_item['children']);
      if ($child_ids) {
        sort($child_ids);
      }
      $this->assertEqual($expected_item['children'], $child_ids);
      unset($expected_item['children']);
    }

    if (isset($expected_item['parents'])) {
      $parent_ids = array_values($menu_link_manager->getParentIds($menu_plugin_id));
      $this->assertEqual($expected_item['parents'], $parent_ids);
      unset($expected_item['parents']);
    }

    if (isset($expected_item['langcode']) && $entity) {
      $this->assertEqual($entity->langcode->value, $expected_item['langcode']);
      unset($expected_item['langcode']);
    }

    foreach ($expected_item as $key => $value) {
      $this->assertTrue(isset($definition[$key]));
      $this->assertEqual($definition[$key], $value);
    }
  }

}
