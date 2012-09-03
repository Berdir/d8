<?php

/**
 * @file
 * Definition of Drupal\node\Tests\NodeCreationTest.
 */

namespace Drupal\node\Tests;

use Drupal\Core\Database\Database;
use Exception;

class NodeCreationTest extends NodeTestBase {

  /**
   * Modules to enable.
   *
   * Enable dummy module that implements hook_node_insert() for exceptions.
   *
   * @var array
   */
  public static $modules = array('node_test_exception', 'dblog');

  public static function getInfo() {
    return array(
      'name' => 'Node creation',
      'description' => 'Create a node and test saving it.',
      'group' => 'Node',
    );
  }

  function setUp() {
    parent::setUp();

    $web_user = $this->drupalCreateUser(array('create page content', 'edit own page content'));
    $this->drupalLogin($web_user);
  }

  /**
   * Create a "Basic page" node and verify its consistency in the database.
   */
  function testNodeCreation() {
    // Create a node.
    $edit = array();
    $langcode = LANGUAGE_NOT_SPECIFIED;
    $edit["title"] = $this->randomName(8);
    $edit["body[$langcode][0][value]"] = $this->randomName(16);
    $this->drupalPost('node/add/page', $edit, t('Save'));

    // Check that the Basic page has been created.
    $this->assertRaw(t('!post %title has been created.', array('!post' => 'Basic page', '%title' => $edit["title"])), t('Basic page created.'));

    // Check that the node exists in the database.
    $node = $this->drupalGetNodeByTitle($edit["title"]);
    $this->assertTrue($node, t('Node found in database.'));
  }

  /**
   * Create a page node and verify that a transaction rolls back the failed creation
   */
  function testFailedPageCreation() {
    // Create a node.
    $edit = array(
      'uid'      => $this->loggedInUser->uid,
      'name'     => $this->loggedInUser->name,
      'type'     => 'page',
      'langcode' => LANGUAGE_NOT_SPECIFIED,
      'title'    => 'testing_transaction_exception',
    );

    try {
      entity_create('node', $edit)->save();
      $this->fail(t('Expected exception has not been thrown.'));
    }
    catch (Exception $e) {
      $this->pass(t('Expected exception has been thrown.'));
    }

    if (Database::getConnection()->supportsTransactions()) {
      // Check that the node does not exist in the database.
      $node = $this->drupalGetNodeByTitle($edit['title']);
      $this->assertFalse($node, t('Transactions supported, and node not found in database.'));
    }
    else {
      // Check that the node exists in the database.
      $node = $this->drupalGetNodeByTitle($edit['title']);
      $this->assertTrue($node, t('Transactions not supported, and node found in database.'));

      // Check that the failed rollback was logged.
      $records = db_query("SELECT wid FROM {watchdog} WHERE message LIKE 'Explicit rollback failed%'")->fetchAll();
      $this->assertTrue(count($records) > 0, t('Transactions not supported, and rollback error logged to watchdog.'));
    }

    // Check that the rollback error was logged.
    $records = db_query("SELECT wid FROM {watchdog} WHERE variables LIKE '%Test exception for rollback.%'")->fetchAll();
    $this->assertTrue(count($records) > 0, t('Rollback explanatory error logged to watchdog.'));
  }

  /**
   * Create an unpublished node and confirm correct redirect behavior.
   */
  function testUnpublishedNodeCreation() {
    // Set "Basic page" content type to be unpublished by default.
    variable_set('node_options_page', array());
    // Set the front page to the default "node" page.
    config('system.site')->set('page.front', 'node')->save();

    // Create a node.
    $edit = array();
    $edit["title"] = $this->randomName(8);
    $edit["body[" . LANGUAGE_NOT_SPECIFIED . "][0][value]"] = $this->randomName(16);
    $this->drupalPost('node/add/page', $edit, t('Save'));

    // Check that the user was redirected to the home page.
    $this->assertText(t('Welcome to Drupal'), t('The user is redirected to the home page.'));
  }
}
