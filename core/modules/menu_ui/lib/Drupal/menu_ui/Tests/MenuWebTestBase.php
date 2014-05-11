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
    /** @var \Drupal\Core\Menu\MenuLinkInterface $menu_link */
    $menu_tree = $this->container->get('menu.link_tree');
    $menu_tree->resetDefinitions();
    $definition = $menu_tree->getDefinition($menu_plugin_id);

//    $options = $definition['options'];

    // Pull the path from the menu link content.
    if (strpos($menu_plugin_id, 'menu_link_content') === 0) {
      list(, $uuid) = explode(':', $menu_plugin_id, 2);
      $links = \Drupal::entityManager()->getStorage('menu_link_content')->loadByProperties(array('uuid' => $uuid));
      /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $link */
      $link = reset($links);
      $url_object = $link->getUrlObject();

      // Force to not convert to aliases for the test.
      if ($url_object->isExternal()) {
        $url = \Drupal::urlGenerator()->generateFromPath($url_object->getPath(), $url_object->getOptions());
      }
      else {
        $url = \Drupal::url($url_object->getRouteName(), $url_object->getRouteParameters(), $url_object->getOptions() + array('alias' => TRUE));
      }
      if (!$url_object->isExternal()) {
        $base_url = \Drupal::service('router.request_context')->getBaseUrl();
        $url = substr($url, strlen($base_url) + 1);
      }
      $definition['url'] = $url;

      // Replace
      if (isset($expected_item['url']) && $expected_item['url'] == '<front>') {
        $expected_item['url'] = '';
      }
    }

    // The internal storage of the tree does use some INTs instead of the plugin
    // ids. Therefore first figure out the INTs for p1...p9.
    $raw_menu_tree_entry = \Drupal::database()->query("SELECT * FROM {menu_tree} WHERE id = :id", array(':id' => $menu_plugin_id))->fetchAssoc();
    for ($i = 1; $i <= 9; $i++) {
      if (isset($expected_item['p' . $i])) {
        // Get the internal MLID from the plugin ID.
        $mlid = \Drupal::database()->query("SELECT mlid FROM {menu_tree} where id = :id", array(':id' => $expected_item['p' . $i]))->fetchField();
        $this->assertEqual($mlid, $raw_menu_tree_entry['p' . $i]);
        unset($expected_item['p' . $i]);
      }
    }
    foreach (array('depth', 'has_children') as $internal_key) {
      if (isset($expected_item[$internal_key])) {
        $this->assertEqual($raw_menu_tree_entry[$internal_key], $expected_item[$internal_key]);
        unset($expected_item[$internal_key]);
      }
    }

    if (isset($expected_item['langcode'])) {
      $this->assertEqual($link->langcode->value, $expected_item['langcode']);
      unset($expected_item['langcode']);
    }

    foreach ($expected_item as $key => $value) {
      $this->assertEqual($definition[$key], $value);
    }
  }

}
