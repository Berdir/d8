<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Menu\MenuLinkTreeTest.
 */

namespace Drupal\system\Tests\Menu;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests the menu link tree.
 *
 * @see \Drupal\Core\Menu\MenuLinkTree
 */
class MenuLinkTreeTest extends DrupalUnitTestBase {

  /**
   * The tested menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $linkTree;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system', 'menu_test', 'menu_link_content', 'field');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Tests \Drupal\Core\Menu\MenuLinkTree',
      'description' => '',
      'group' => 'Menu'
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installSchema('system', array('router'));
    $this->installSchema('menu_link_content', array('menu_link_content', 'menu_link_content_data'));

    $this->linkTree = \Drupal::menuTree();
  }

  public function testDeleteLinksInMenu() {
    \Drupal::service('router.builder')->rebuild();

    \Drupal::entityManager()->getStorage('menu')->create(array('id' => 'menu1'))->save();
    \Drupal::entityManager()->getStorage('menu')->create(array('id' => 'menu2'))->save();

    \Drupal::entityManager()->getStorage('menu_link_content')->create(array('route_name' => 'menu_test.menu_name_test', 'menu_name' => 'menu1', 'bundle' => 'menu_link_content'))->save();
    \Drupal::entityManager()->getStorage('menu_link_content')->create(array('route_name' => 'menu_test.menu_name_test', 'menu_name' => 'menu1', 'bundle' => 'menu_link_content'))->save();
    \Drupal::entityManager()->getStorage('menu_link_content')->create(array('route_name' => 'menu_test.menu_name_test', 'menu_name' => 'menu2', 'bundle' => 'menu_link_content'))->save();

    $output = $this->linkTree->buildTree('menu1');
    $this->assertEqual(count($output), 2);
    $output = $this->linkTree->buildTree('menu2');
    $this->assertEqual(count($output), 1);

    $this->linkTree->deleteLinksInMenu('menu1');
    $this->linkTree->resetDefinitions();

    $output = $this->linkTree->buildTree('menu1');
    $this->assertEqual(count($output), 0);

    $output = $this->linkTree->buildTree('menu2');
    $this->assertEqual(count($output), 1);
  }

  public function testGetParentDepthLimit() {
    \Drupal::service('router.builder')->rebuild();

    $storage = \Drupal::entityManager()->getStorage('menu_link_content');

    // root
    // - child1
    // -- child2
    // --- child3
    // ---- child4
    $root = $storage->create(array('route_name' => 'menu_test.menu_name_test', 'menu_name' => 'menu1', 'bundle' => 'menu_link_content'));
    $root->save();
    $child1 = $storage->create(array('route_name' => 'menu_test.menu_name_test', 'menu_name' => 'menu1', 'bundle' => 'menu_link_content', 'parent' => $root->getPluginId()));
    $child1->save();
    $child2 = $storage->create(array('route_name' => 'menu_test.menu_name_test', 'menu_name' => 'menu1', 'bundle' => 'menu_link_content', 'parent' => $child1->getPluginId()));
    $child2->save();
    $child3 = $storage->create(array('route_name' => 'menu_test.menu_name_test', 'menu_name' => 'menu1', 'bundle' => 'menu_link_content', 'parent' => $child2->getPluginId()));
    $child3->save();
    $child4 = $storage->create(array('route_name' => 'menu_test.menu_name_test', 'menu_name' => 'menu1', 'bundle' => 'menu_link_content', 'parent' => $child3->getPluginId()));
    $child4->save();

    $this->assertEqual($this->linkTree->getParentDepthLimit($root->getPluginId()), 4);
    $this->assertEqual($this->linkTree->getParentDepthLimit($child1->getPluginId()), 5);
    $this->assertEqual($this->linkTree->getParentDepthLimit($child2->getPluginId()), 6);
    $this->assertEqual($this->linkTree->getParentDepthLimit($child3->getPluginId()), 7);
    $this->assertEqual($this->linkTree->getParentDepthLimit($child4->getPluginId()), 8);
  }

}

