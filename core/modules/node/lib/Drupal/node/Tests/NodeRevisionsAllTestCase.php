<?php

/**
 * @file
 * Definition of Drupal\node\Tests\NodeRevisionsAllTestCase.
 */

namespace Drupal\node\Tests;

/**
 * Tests actions against revisions for user with access to all revisions.
 */
class NodeRevisionsAllTestCase extends NodeTestBase {
  protected $nodes;
  protected $logs;
  protected $profile = "standard";

  public static function getInfo() {
    return array(
      'name' => 'Node revisions all',
      'description' => 'Create a node with revisions and test viewing, saving, reverting, and deleting revisions for user with access to all.',
      'group' => 'Node',
    );
  }

  function setUp() {
    parent::setUp();

    // Create and log in user.
    $web_user = $this->drupalCreateUser(
      array(
        'view page revisions',
        'revert page revisions',
        'delete page revisions',
        'edit any page content',
        'delete any page content'
      )
    );
    $this->drupalLogin($web_user);

    // Create an initial node.
    $node = $this->drupalCreateNode();

    $settings = get_object_vars($node);
    $settings['revision'] = 1;

    $nodes = array();
    $logs = array();

    // Get the original node.
    $nodes[] = $node;

    // Create three revisions.
    $revision_count = 3;
    for ($i = 0; $i < $revision_count; $i++) {
      $logs[] = $settings['log'] = $this->randomName(32);

      // Create revision with a random title and body and update variables.
      $this->drupalCreateNode($settings);
      $node = node_load($node->id()); // Make sure we get revision information.
      $settings = get_object_vars($node);
      $nodes[] = $node;
    }

    $this->nodes = $nodes;
    $this->logs = $logs;
  }

  /**
   * Checks node revision operations.
   */
  function testRevisions() {
    $nodes = $this->nodes;
    $logs = $this->logs;

    // Get last node for simple checks.
    $node = $nodes[3];

    // Create and login user.
    $content_admin = $this->drupalCreateUser(
      array(
        'view all revisions',
        'revert all revisions',
        'delete all revisions',
        'edit any page content',
        'delete any page content'
      )
    );
    $this->drupalLogin($content_admin);

    // Confirm the correct revision text appears on "view revisions" page.
    $this->drupalGet('node/' . $node->id(). '/revisions/' . $node->getRevisionId() . '/view');
    $this->assertText($node->body->value, t('Correct text displays for version.'));

    // Confirm the correct log message appears on "revisions overview" page.
    $this->drupalGet('node/' . $node->id(). '/revisions');
    foreach ($logs as $log) {
      $this->assertText($log, t('Log message found.'));
    }

    // Confirm that this is the current revision.
    $this->assertTrue($node->isDefaultRevision(), 'Third node revision is the current one.');

    // Confirm that revisions revert properly.
    $this->drupalPost('node/' . $node->id(). '/revisions/' . $nodes[1]->getRevisionId() .'/revert', array(), t('Revert'));
    $this->assertRaw(t('@type %title has been reverted back to the revision from %revision-date.',
      array(
        '@type' => 'Basic page',
        '%title' => $nodes[1]->title->value,
        '%revision-date' => format_date($nodes[1]->revision_timestamp->value)
      )),
      'Revision reverted.');
    $reverted_node = node_load($node->id());
    $this->assertTrue(($nodes[1]->body->value == $reverted_node->value), t('Node reverted correctly.'));

    // Confirm that this is not the current version.
    $node = node_revision_load($node->getRevisionId());
    $this->assertFalse($node->isDefaultRevision(), 'Third node revision is not the current one.');

    // Confirm revisions delete properly.
    $this->drupalPost('node/' . $node->id(). '/revisions/' . $nodes[1]->getRevisionId() . '/delete', array(), t('Delete'));
    $this->assertRaw(t('Revision from %revision-date of @type %title has been deleted.',
      array(
        '%revision-date' => format_date($nodes[1]->revision_timestamp->value),
        '@type' => 'Basic page',
        '%title' => $nodes[1]->title->value,
      )),
      'Revision deleted.');
    $this->assertTrue(db_query('SELECT COUNT(vid) FROM {node_revision} WHERE nid = :nid and vid = :vid',
      array(':nid' => $node->id(), ':vid' => $nodes[1]->getRevisionId()))->fetchField() == 0,
      'Revision not found.');

    // Set the revision timestamp to an older date to make sure that the
    // confirmation message correctly displays the stored revision date.
    $old_revision_date = REQUEST_TIME - 86400;
    db_update('node_revision')
      ->condition('vid', $nodes[2]->getRevisionId())
      ->fields(array(
        'timestamp' => $old_revision_date,
      ))
      ->execute();
    $this->drupalPost('node/' . $node->id(). '/revisions/' . $nodes[2]->getRevisionId() . '/revert', array(), t('Revert'));
    $this->assertRaw(t('@type %title has been reverted back to the revision from %revision-date.', array(
      '@type' => 'Basic page',
      '%title' => $nodes[2]->title->value,
      '%revision-date' => format_date($old_revision_date),
    )));
  }
}
