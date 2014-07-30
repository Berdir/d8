<?php
/**
 * @file
 * Contains \Drupal\node\Tests\NodeFieldAccessTest.
 */

namespace Drupal\node\Tests;

use Drupal\system\Tests\Entity\EntityUnitTestBase;

/**
 * Tests node field level access.
 *
 * @group node
 */
class NodeFieldAccessTest extends EntityUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node');

  public static $administrative_node_fields = array(
    'status',
    'promote',
    'sticky',
    'created',
    'changed',
    'uid',
  );

  /**
   * Test permissions on nodes status field.
   */
  function testAccessToAdministrativeFields() {

    // A all mighty user
    $chuck_norris = $this->createUser(array(), array('bypass node access'));

    // An administrator user
    $content_admin_user = $this->createUser(array(), array('administer nodes'));

    // Two different editor users
    $page_creator_user = $this->createUser(array(), array('create page content', 'edit own page content', 'delete own page content'));
    $page_manager_user = $this->createUser(array(), array('create page content', 'edit any page content', 'delete any page content'));

    // An unprivileged user
    $page_unrelated_user = $this->createUser(array(), array('access content'));

    // List of all users
    $test_users = array(
      $chuck_norris,
      $content_admin_user,
      $page_creator_user,
      $page_manager_user,
      $page_unrelated_user,
    );

    // Create three "Basic pages". One is owned by our test-user
    // "page_creator", one by "page_manager", and one by someone else.
    $node1 = entity_create('node', array(
      'title' => $this->randomName(8),
      'uid' => $page_creator_user->id(),
      'type' => 'page',
    ));
    $node2 = entity_create('node', array(
      'title' => $this->randomName(8),
      'uid' => $page_manager_user->id(),
      'type' => 'page',
    ));
    $node3 = entity_create('node', array(
      'title' => $this->randomName(8),
      'uid' => $chuck_norris->id(),
      'type' => 'page',
    ));

    foreach(NodeFieldAccessTest::$administrative_node_fields as $field) {

      // Checks on view operations:
      foreach($test_users as $account) {
        $may_view = $node1->{$field}->access("view", $account);
        $this->assertTrue($may_view, "Any user may view status fields.");
      }

      // Checks on edit operations:
      $may_update = $node1->{$field}->access("edit", $page_creator_user);
      $this->assertFalse($may_update, "Users with permission \"edit own <type> content\" must not edit $field fields.");
      $may_update = $node2->{$field}->access("edit", $page_creator_user);
      $this->assertFalse($may_update, "Users with permission \"edit own <type> content\" must not edit $field fields.");
      $may_update = $node2->{$field}->access("edit", $page_manager_user);
      $this->assertFalse($may_update, "Users with permission \"edit any <type> content\" must not edit $field fields.");
      $may_update = $node1->{$field}->access("edit", $page_manager_user);
      $this->assertFalse($may_update, "Users with permission \"edit any <type> content\" must not edit $field fields.");
      $may_update = $node2->{$field}->access("edit", $page_unrelated_user);
      $this->assertFalse($may_update, "Users not having permission \"edit any <type> content\" must not edit $field fields.");
      $may_update = $node1->{$field}->access("edit", $chuck_norris) && $node3->status->access("edit", $chuck_norris);
      $this->assertTrue($may_update, "Users with permission \"bypass node access\" may edit $field fields on all nodes.");
      $may_update = $node1->{$field}->access("edit", $content_admin_user) && $node3->status->access("edit", $content_admin_user);
      $this->assertTrue($may_update, "Users with permission \"administer nodes\" may edit $field fields on all nodes.");
    }

  }

}
