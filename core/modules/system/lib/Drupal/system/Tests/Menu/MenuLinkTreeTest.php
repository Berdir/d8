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

}

