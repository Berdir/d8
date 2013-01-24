<?php

/**
 * @file
 * Definition of Drupal\comment\Tests\CommentFieldsTest.
 */

namespace Drupal\comment\Tests;

/**
 * Tests fields on comments.
 */
class CommentFieldsTest extends CommentTestBase {

  /**
   * Enable the field UI.
   *
   * @var array
   */
  public static $modules = array('field_ui');

  public static function getInfo() {
    return array(
      'name' => 'Comment fields',
      'description' => 'Tests fields on comments.',
      'group' => 'Comment',
    );
  }

  /**
   * Tests that the default 'comment_body' field is correctly added.
   */
  function testCommentDefaultFields() {
    // Do not make assumptions on default node types created by the test
    // installation profile, and create our own.
    $this->drupalCreateContentType(array('type' => 'test_node_type'));
    comment_add_default_comment_field('node', 'test_node_type');

    // Check that the 'comment_body' field is present on the comment bundle.
    $instance = field_info_instance('comment', 'comment_body', 'comment');
    $this->assertTrue(!empty($instance), 'The comment_body field is added when a comment bundle is created');

    // Delete the instance.
    field_delete_instance($instance);

    // Check that the 'comment_body' field is deleted.
    $field = field_info_field('comment_body');
    $this->assertTrue(empty($field), 'The comment_body field was deleted');

    // Create a new content type.
    $type_name = 'test_node_type_2';
    $this->drupalCreateContentType(array('type' => $type_name));
    comment_add_default_comment_field('node', $type_name);

    // Check that the 'comment_body' field exists and has an instance on the
    // new comment bundle.
    $field = field_info_field('comment_body');
    $this->assertTrue($field, 'The comment_body field exists');
    $instances = field_info_instances('comment');
    $this->assertTrue(isset($instances['comment']['comment_body']), format_string('The comment_body field is present for comments on type @type', array('@type' => $type_name)));
  }

  /**
   * Tests that comment module works when enabled after a content module.
   */
  function testCommentEnable() {
    // Create a user to do module administration.
    $this->admin_user = $this->drupalCreateUser(array('access administration pages', 'administer modules'));
    $this->drupalLogin($this->admin_user);

    // Disable the comment module.
    $edit = array();
    $edit['modules[Core][comment][enable]'] = FALSE;
    $this->drupalPost('admin/modules', $edit, t('Save configuration'));
    $this->rebuildContainer();
    $this->assertFalse(module_exists('comment'), 'Comment module disabled.');

    // Enable core content type module (book).
    $edit = array();
    $edit['modules[Core][book][enable]'] = 'book';
    $this->drupalPost('admin/modules', $edit, t('Save configuration'));

    // Now enable the comment module.
    $edit = array();
    $edit['modules[Core][comment][enable]'] = 'comment';
    $this->drupalPost('admin/modules', $edit, t('Save configuration'));
    $this->rebuildContainer();
    $this->assertTrue(module_exists('comment'), 'Comment module enabled.');

    // Create nodes of each type.
    $book_node = $this->drupalCreateNode(array('type' => 'book'));

    $this->drupalLogout();

    // Try to post a comment on each node. A failure will be triggered if the
    // comment body is missing on one of these forms, due to postComment()
    // asserting that the body is actually posted correctly.
    $this->web_user = $this->drupalCreateUser(array('access content', 'access comments', 'post comments', 'skip comment approval'));
    $this->drupalLogin($this->web_user);
    $this->postComment($book_node, $this->randomName(), $this->randomName());
>>>>>>> 8.x
  }

  /**
   * Tests that comment module works correctly with plain text format.
   */
  function testCommentFormat() {
    // Disable text processing for comments.
    $this->drupalLogin($this->admin_user);
    $instance = field_info_instance('comment', 'comment_body', 'comment');
    $instance['settings']['text_processing'] = 0;
    field_update_instance($instance);

    // Post a comment without an explicit subject.
    $this->drupalLogin($this->web_user);
    $edit = array('comment_body[und][0][value]' => $this->randomName(8));
    $this->drupalPost('node/' . $this->node->nid, $edit, t('Save'));
  }
}
