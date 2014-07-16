<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Menu\MenuLinkTreeElementTest.
 */

namespace Drupal\Tests\Core\Menu;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the menu link tree element value object.
 *
 * @group Menu
 *
 * @coversDefaultClass \Drupal\Core\Menu\MenuLinkTreeElement
 */
class MenuLinkTreeElementTest extends UnitTestCase {

  /**
   * Tests construction and setters.
   *
   * @covers ::__construct
   * @covers ::setAccessible
   * @covers ::setSubtree
   */
  public function testConstruction() {
    $link = MenuLinkMock::create(array('id' => 'test'));
    $item = new MenuLinkTreeElement($link, FALSE, 3, FALSE, array());
    $this->assertSame($link, $item->getLink());
    $this->assertSame(FALSE, $item->hasChildren());
    $this->assertSame(3, $item->getDepth());
    $this->assertSame(FALSE, $item->isInActiveTrail());
    $this->assertSame(array(), $item->getSubtree());
    $this->assertSame(NULL, $item->isAccessible());

    $item->setAccessible(TRUE);
    $this->assertSame(TRUE, $item->isAccessible());
    $item->setAccessible(FALSE);
    $this->assertSame(FALSE, $item->isAccessible());

    $subtree = array(new MenuLinkTreeElement(MenuLinkMock::create(array('id' => 'foobar')), FALSE, 4, FALSE, array()));
    $item->setSubtree($subtree);
    $this->assertSame($subtree, $item->getSubtree());
  }

  /**
   * Tests count().
   *
   * @covers ::count
   */
  public function testCount() {
    $link_1 = MenuLinkMock::create(array('id' => 'test_1'));
    $link_2 = MenuLinkMock::create(array('id' => 'test_2'));
    $child_item = new MenuLinkTreeElement($link_2, FALSE, 2, FALSE, array());
    $parent_item = new MenuLinkTreeElement($link_1, FALSE, 2, FALSE, array($child_item));
    $this->assertSame(1, $child_item->count());
    $this->assertSame(2, $parent_item->count());
  }

}
