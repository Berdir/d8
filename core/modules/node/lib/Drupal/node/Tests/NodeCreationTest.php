<?php

/**
 * @file
 * Definition of Drupal\node\Tests\NodeCreationTest.
 */

namespace Drupal\node\Tests;

use Drupal\Core\Database\Database;
use Drupal\Core\Language\Language;
use Drupal\simpletest\WebTestBase;

/**
 * Tests creating and saving a node.
 */
class NodeCreationTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * Enable dummy module that implements hook_node_insert() for exceptions.
   *
   * @var array
   */
  public static $modules = array('node', 'node_test_exception', 'dblog', 'test_page_test');

  public static function getInfo() {
    return array(
      'name' => 'Node creation',
      'description' => 'Create a node and test saving it.',
      'group' => 'Node',
    );
  }

  function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));

    $web_user = $this->drupalCreateUser(array('create page content', 'edit own page content'));
    $this->drupalLogin($web_user);
  }

  /**
   * Creates a "Basic page" node and verifies its consistency in the database.
   */
  function dtestNodeCreation() {
    // Create a node.
    $edit = array();
    $edit["title"] = $this->randomName(8);
    $edit["body[0][value]"] = $this->randomName(16);
    $this->drupalPostForm('node/add/page', $edit, t('Save'));

    // Check that the Basic page has been created.
    $this->assertRaw(t('!post %title has been created.', array('!post' => 'Basic page', '%title' => $edit["title"])), 'Basic page created.');

    // Check that the node exists in the database.
    $node = $this->drupalGetNodeByTitle($edit["title"]);
    $this->assertTrue($node, 'Node found in database.');
  }

  /**
   * Verifies that a transaction rolls back the failed creation.
   */
  function dtestFailedPageCreation() {
    // Create a node.
    $edit = array(
      'uid'      => $this->loggedInUser->id(),
      'name'     => $this->loggedInUser->name,
      'type'     => 'page',
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
      'title'    => 'testing_transaction_exception',
    );

    try {
      entity_create('node', $edit)->save();
      $this->fail(t('Expected exception has not been thrown.'));
    }
    catch (\Exception $e) {
      $this->pass(t('Expected exception has been thrown.'));
    }

    if (Database::getConnection()->supportsTransactions()) {
      // Check that the node does not exist in the database.
      $node = $this->drupalGetNodeByTitle($edit['title']);
      $this->assertFalse($node, 'Transactions supported, and node not found in database.');
    }
    else {
      // Check that the node exists in the database.
      $node = $this->drupalGetNodeByTitle($edit['title']);
      $this->assertTrue($node, 'Transactions not supported, and node found in database.');

      // Check that the failed rollback was logged.
      $records = db_query("SELECT wid FROM {watchdog} WHERE message LIKE 'Explicit rollback failed%'")->fetchAll();
      $this->assertTrue(count($records) > 0, 'Transactions not supported, and rollback error logged to watchdog.');
    }

    // Check that the rollback error was logged.
    $records = db_query("SELECT wid FROM {watchdog} WHERE variables LIKE '%Test exception for rollback.%'")->fetchAll();
    $this->assertTrue(count($records) > 0, 'Rollback explanatory error logged to watchdog.');
  }

  /**
   * Creates an unpublished node and confirms correct redirect behavior.
   */
  function dtestUnpublishedNodeCreation() {
    // Set the front page to the test page.
    \Drupal::config('system.site')->set('page.front', 'test-page')->save();

    // Set "Basic page" content type to be unpublished by default.
    \Drupal::config('node.type.page')->set('settings.node.options', array())->save();

    // Create a node.
    $edit = array();
    $edit['title'] = $this->randomName(8);
    $edit['body[0][value]'] = $this->randomName(16);
    $this->drupalPostForm('node/add/page', $edit, t('Save'));

    // Check that the user was redirected to the home page.
    $this->assertUrl('');
    $this->assertText(t('Test page text'));

    // Confirm that the node was created.
    $this->assertRaw(t('!post %title has been created.', array('!post' => 'Basic page', '%title' => $edit["title"])));
  }

  /**
   * Creates nodes with different authored dates.
   */
  function testAuthoredDate() {
    $admin = $this->drupalCreateUser(array('create page content', 'edit own page content', 'administer nodes'));
    $this->drupalLogin($admin);

    // Create a node with the default creation date.
    $edit = array();
    $edit['title'] = $this->randomName(8);
    $edit['body[0][value]'] = $this->randomName(16);
    $this->drupalPostForm('node/add/page', $edit, t('Save and publish'));

    $node = $this->drupalGetNodeByTitle($edit['title']);
    $this->assertNotEqual(NULL, $node->getCreatedTime());

    // Create a node with the custom creation date in the past.
    $date = REQUEST_TIME - 86400;
    $edit = array();
    $edit['title'] = $this->randomName(8);
    $edit['body[0][value]'] = $this->randomName(16);
    $edit['date'] = date('Y-d-m H:m:i', $date);
    $this->drupalPostForm('node/add/page', $edit, t('Save and publish'));

    $node = $this->drupalGetNodeByTitle($edit['title']);
    $this->assertEqual($date, $node->getCreatedTime());

    // Create a node with the custom creation date in the future.
    $date = REQUEST_TIME + 86400;
    $edit = array();
    $edit['title'] = $this->randomName(8);
    $edit['body[0][value]'] = $this->randomName(16);
    $edit['date'] = date('Y-d-m H:m:i', $date);
    debug($edit);
    $this->drupalPostForm('node/add/page', $edit, t('Save and publish'));

    $node = $this->drupalGetNodeByTitle($edit['title']);
    $this->assertEqual($date, $node->getCreatedTime());

    // Test an invalid date.
    $edit = array();
    $edit['title'] = $this->randomName(8);
    $edit['body[0][value]'] = $this->randomName(16);
    $edit['date'] = 'Invalid date';
    $this->drupalPostForm('node/add/page', $edit, t('Save and publish'));
    $this->assertText(t('You have to specify a valid date.'));
    $this->assertFalse($this->drupalGetNodeByTitle($edit['title']));
  }

  /**
   * Tests the author autocompletion textfield.
   */
  public function dtestAuthorAutocomplete() {
    $admin_user = $this->drupalCreateUser(array('administer nodes', 'create page content'));
    $this->drupalLogin($admin_user);

    $this->drupalGet('node/add/page');

    $result = $this->xpath('//input[@id = "edit-name-autocomplete"]');
    $this->assertEqual(count($result), 0, 'No autocompletion without access user profiles.');

    $admin_user = $this->drupalCreateUser(array('administer nodes', 'create page content', 'access user profiles'));
    $this->drupalLogin($admin_user);

    $this->drupalGet('node/add/page');

    $result = $this->xpath('//input[@id = "edit-name-autocomplete"]');
    $this->assertEqual((string) $result[0]['value'], url('user/autocomplete'));
    $this->assertEqual(count($result), 1, 'Ensure that the user does have access to the autocompletion');
  }

}
