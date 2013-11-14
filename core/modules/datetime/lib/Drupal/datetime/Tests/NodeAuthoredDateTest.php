<?php

/**
 * @file
 * Definition of Drupal\datetime\Tests\NodeAuthoredDateTest.
 */

namespace Drupal\datetime\Tests;

use Drupal\Core\Database\Database;
use Drupal\Core\Language\Language;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the authored date with datetime enabled.
 */
class NodeAuthoredDateTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'datetime',);

  public static function getInfo() {
    return array(
      'name' => 'Node authored date',
      'description' => 'Create a node and test different authored date.',
      'group' => 'Node',
    );
  }

  function setUp() {
    parent::setUp();
    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));
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
    $edit['date[date]'] = date('Y-m-d', $date);
    $edit['date[time]'] = date('H:i:s', $date);
    $this->drupalPostForm('node/add/page', $edit, t('Save and publish'));

    $node = $this->drupalGetNodeByTitle($edit['title']);
    $this->assertEqual($date, $node->getCreatedTime());

    // Create a node with the custom creation date in the future.
    $date = REQUEST_TIME + 86400;
    $edit = array();
    $edit['title'] = $this->randomName(8);
    $edit['body[0][value]'] = $this->randomName(16);
    $edit['date[date]'] = date('Y-m-d', $date);
    $edit['date[time]'] = date('H:i:s', $date);
    $this->drupalPostForm('node/add/page', $edit, t('Save and publish'));

    $node = $this->drupalGetNodeByTitle($edit['title']);
    $this->assertEqual($date, $node->getCreatedTime());

    // Test an invalid date.
    $edit = array();
    $edit['title'] = $this->randomName(8);
    $edit['body[0][value]'] = $this->randomName(16);
    $edit['date[date]'] = '2013-13-13';
    $edit['date[time]'] = '13:13:13';
    $this->drupalPostForm('node/add/page', $edit, t('Save and publish'));
    $this->assertText(t('The Authored on date is invalid.'));
    $this->assertFalse($this->drupalGetNodeByTitle($edit['title']));
  }

}
