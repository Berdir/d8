<?php

/**
 * @file
 * Definition of Drupal\forum\Tests\ForumBlockTest.
 */

namespace Drupal\forum\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the Forum blocks.
 */
class ForumBlockTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('forum', 'block');

  /**
   * A user with various administrative privileges.
   */
  protected $adminUser;

  public static function getInfo() {
    return array(
      'name' => 'Forum blocks',
      'description' => 'Tests the forum blocks.',
      'group' => 'Forum',
    );
  }

  function setUp() {
    parent::setUp();

    // Create users.
    $this->adminUser = $this->drupalCreateUser(array(
      'access administration pages',
      'administer blocks',
      'administer nodes',
      'create forum content',
      'post comments',
      'skip comment approval',
    ));
  }

  /**
   * Tests disabling and re-enabling the Forum module.
   */
  function testNewForumTopicsBlock() {
    $this->drupalLogin($this->adminUser);

    // Create 5 forum topics.
    $topics = $this->createForumTopics();

    // Enable the new forum block.
    $edit = array();
    $edit['blocks[forum_new][region]'] = 'sidebar_second';
    $this->drupalPost('admin/structure/block', $edit, t('Save blocks'));
    $this->assertResponse(200);
    $this->assertText(t('The block settings have been updated.'), '"New forum topics" block was enabled');
    $this->assertLink(t('More'), 0, 'New forum topics block has a "more"-link.');
    $this->assertLinkByHref('forum', 0, 'New forum topics block has a "more"-link.');

    // We expect all 5 forum topics to appear in the "New forum topics" block.
    foreach ($topics as $topic) {
      $this->assertLink($topic, 0, format_string('Forum topic @topic found in the "New forum topics" block.', array('@topic' => $topic)));
    }

    // Configure the new forum block to only show 2 topics.
    $edit = array();
    $edit['block_new_limit'] = 2;
    $this->drupalPost('admin/structure/block/manage/forum/new/configure', $edit, t('Save block'));
    $this->assertResponse(200);

    // We expect only the 2 most recent forum topics to appear in the "New forum
    // topics" block.
    for ($index = 0; $index < 5; $index++) {
      if (in_array($index, array(3, 4))) {
        $this->assertLink($topics[$index], 0, format_string('Forum topic @topic found in the "New forum topics" block.', array('@topic' => $topics[$index])));
      }
      else {
        $this->assertNoText($topics[$index], format_string('Forum topic @topic not found in the "New forum topics" block.', array('@topic' => $topics[$index])));
      }
    }

    // Disable the "New forum topics" block again.
    $edit = array();
    $edit['blocks[forum_new][region]'] = BLOCK_REGION_NONE;
    $this->drupalPost('admin/structure/block', $edit, t('Save blocks'));
    $this->assertResponse(200);
    $this->assertText(t('The block settings have been updated.'), '"New forum topics" block was disabled');
  }

  function testActiveForumTopicsBlock() {
    $this->drupalLogin($this->adminUser);

    // Create 10 forum topics.
    $topics = $this->createForumTopics(10);

    // Comment on the first 5 topics.
    $timestamp = time();
    $langcode = LANGUAGE_NOT_SPECIFIED;
    for ($index = 0; $index < 5; $index++) {
      // Get the node from the topic title.
      $node = $this->drupalGetNodeByTitle($topics[$index]);
      $comment = entity_create('comment', array(
        'entity_id' => $node->nid,
        'field_name' => 'comment_node_forum',
        'entity_type' => 'node',
        'subject' => $this->randomString(20),
        'comment_body' => array(LANGUAGE_NOT_SPECIFIED => $this->randomString(256)),
        'created' => $timestamp + $index,
      ));
      comment_save($comment);
    }

    // Enable the active forum block.
    $edit = array();
    $edit['blocks[forum_active][region]'] = 'sidebar_second';
    $this->drupalPost('admin/structure/block', $edit, t('Save blocks'));
    $this->assertResponse(200);
    $this->assertText(t('The block settings have been updated.'), 'Active forum topics forum block was enabled');
    $this->assertLink(t('More'), 0, 'Active forum topics block has a "more"-link.');
    $this->assertLinkByHref('forum', 0, 'Active forum topics block has a "more"-link.');

    // We expect the first 5 forum topics to appear in the "Active forum topics"
    // block.
    $this->drupalGet('<front>');
    for ($index = 0; $index < 10; $index++) {
      if ($index < 5) {
        $this->assertLink($topics[$index], 0, format_string('Forum topic @topic found in the "Active forum topics" block.', array('@topic' => $topics[$index])));
      }
      else {
        $this->assertNoText($topics[$index], format_string('Forum topic @topic not found in the "Active forum topics" block.', array('@topic' => $topics[$index])));
      }
    }

    // Configure the active forum block to only show 2 topics.
    $edit = array();
    $edit['block_active_limit'] = 2;
    $this->drupalPost('admin/structure/block/manage/forum/active/configure', $edit, t('Save block'));
    $this->assertResponse(200);

    // We expect only the 2 forum topics with most recent comments to appear in
    // the "Active forum topics" block.
    for ($index = 0; $index < 10; $index++) {
      if (in_array($index, array(3, 4))) {
        $this->assertLink($topics[$index], 0, 'Forum topic found in the "Active forum topics" block.');
      }
      else {
        $this->assertNoText($topics[$index], 'Forum topic not found in the "Active forum topics" block.');
      }
    }

    // Disable the "Active forum topics" block again.
    $edit = array();
    $edit['blocks[forum_active][region]'] = BLOCK_REGION_NONE;
    $this->drupalPost('admin/structure/block', $edit, t('Save blocks'));
    $this->assertResponse(200);
    $this->assertText(t('The block settings have been updated.'), '"Active forum topics" block was disabled');
  }

  /**
   * Creates a forum topic.
   *
   * @return
   *   The title of the newly generated topic.
   */
  private function createForumTopics($count = 5) {
    $topics = array();
    $timestamp = time() - 24 * 60 * 60;

    for ($index = 0; $index < $count; $index++) {
      // Generate a random subject/body.
      $title = $this->randomName(20);
      $body = $this->randomName(200);

      $langcode = LANGUAGE_NOT_SPECIFIED;
      $edit = array(
        'title' => $title,
        "body[$langcode][0][value]" => $body,
        // Forum posts are ordered by timestamp, so force a unique timestamp by
        // adding the index.
        'date' => date('c', $timestamp + $index),
      );

      // Create the forum topic, preselecting the forum ID via a URL parameter.
      $this->drupalPost('node/add/forum/1', $edit, t('Save'));
      $topics[] = $title;
    }

    return $topics;
  }
}
