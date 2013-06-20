<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Upgrade\LanguageUpgradePathTest.
 */

namespace Drupal\system\Tests\Upgrade;

/**
 * Tests upgrading a filled database with comment data.
 *
 * Loads a filled installation of Drupal 7 with comment data and runs the
 * upgrade process on it.
 */
class CommentUpgradePathTest extends UpgradePathTestBase {
  public static function getInfo() {
    return array(
      'name'  => 'Comment upgrade test',
      'description'  => 'Upgrade tests with comment data.',
      'group' => 'Upgrade path',
    );
  }

  public function setUp() {
    // Path to the database dump files.
    $this->databaseDumpFiles = array(
      drupal_get_path('module', 'system') . '/tests/upgrade/drupal-7.filled.standard_all.database.php.gz',
      // Language dataset includes nodes with comments so can be reused.
      drupal_get_path('module', 'system') . '/tests/upgrade/drupal-7.language.database.php',
    );
    parent::setUp();
  }

  /**
   * Tests a successful upgrade.
   */
  public function testCommentUpgrade() {
    $this->assertTrue($this->performUpgrade(), 'The upgrade was completed successfully.');

    // Check that comments display on the node.
    $this->drupalGet('node/50');
    $this->assertText('Node title 50', 'Node 50 displayed after update.');
    $this->assertText('First test comment', 'Comment 1 displayed after update.');
    $this->assertText('Reply to first test comment', 'Comment 2 displayed after update.');

    $expected_settings = array(
      'article' => array (
        'default_mode' => 1,
        'per_page' => 50,
        'form_location' => 1,
        'anonymous' => 0,
        'subject' => 1,
        'preview' => 1,
        'user_register_form' => false,
      ),
      'blog' => array (
        'default_mode' => 1,
        'per_page' => 50,
        'form_location' => 1,
        'anonymous' => 0,
        'subject' => 1,
        'preview' => 1,
        'user_register_form' => false,
      ),
      'book' => array (
        'default_mode' => 1,
        'per_page' => 50,
        'form_location' => 1,
        'anonymous' => 0,
        'subject' => 1,
        'preview' => 1,
        'user_register_form' => false,
      ),
      'forum' => array (
        'default_mode' => 1,
        'per_page' => 50,
        'form_location' => 1,
        'anonymous' => 0,
        'subject' => 1,
        'preview' => 1,
        'user_register_form' => false,
      ),
      'page' => array (
        'default_mode' => 1,
        'per_page' => 50,
        'form_location' => 1,
        'anonymous' => 0,
        'subject' => 1,
        'preview' => 1,
        'user_register_form' => false,
      ),
    );
    // Check one instance exists for each node type.
    $types = node_type_get_types();
    $types = array_keys($types);
    sort($types);
    $this->assertIdentical(array_keys($expected_settings), $types, 'All node types are upgraded');
    foreach ($types as $type) {
      $instance = field_info_instance('node', 'comment_node_' . $type, $type);
      $this->assertTrue($instance, format_string('Comment field found for the %type node type', array(
        '%type' => $type
      )));
      $this->assertIdentical($instance->settings, $expected_settings[$type], format_string('Comment field settings migrated for the %type node type', array(
        '%type' => $type,
      )));
    }
  }

}
