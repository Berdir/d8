<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6Comment.
 */

namespace Drupal\migrate_drupal\Tests\Dump;

use Drupal\Core\Database\Connection;

class Drupal6Comment {

  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public static function load(Connection $database) {
    $database->schema()->createTable('node', array(
      'description' => 'The base table for nodes.',
      'fields' => array(
        'nid' => array(
          'description' => 'The primary identifier for a node.',
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE),
        'vid' => array(
          'description' => 'The current {node_revisions}.vid version identifier.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0),
        'type' => array(
          'description' => 'The {node_type}.type of this node.',
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => ''),
        'language' => array(
          'description' => 'The {languages}.language of this node.',
          'type' => 'varchar',
          'length' => 12,
          'not null' => TRUE,
          'default' => ''),
        'title' => array(
          'description' => 'The title of this node, always treated as non-markup plain text.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => ''),
        'uid' => array(
          'description' => 'The {users}.uid that owns this node; initially, this is the user that created it.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0),
        'status' => array(
          'description' => 'Boolean indicating whether the node is published (visible to non-administrators).',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 1),
        'created' => array(
          'description' => 'The Unix timestamp when the node was created.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0),
        'changed' => array(
          'description' => 'The Unix timestamp when the node was most recently saved.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0),
        'comment' => array(
          'description' => 'Whether comments are allowed on this node: 0 = no, 1 = read only, 2 = read/write.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0),
        'promote' => array(
          'description' => 'Boolean indicating whether the node should be displayed on the front page.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0),
        'moderate' => array(
          'description' => 'Previously, a boolean indicating whether the node was "in moderation"; mostly no longer used.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0),
        'sticky' => array(
          'description' => 'Boolean indicating whether the node should be displayed at the top of lists in which it appears.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0),
        'tnid' => array(
          'description' => 'The translation set id for this node, which equals the node id of the source post in each set.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0),
        'translate' => array(
          'description' => 'A boolean indicating whether this translation page needs to be updated.',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0),
      ),
      'indexes' => array(
        'node_changed'        => array('changed'),
        'node_created'        => array('created'),
        'node_moderate'       => array('moderate'),
        'node_promote_status' => array('promote', 'status'),
        'node_status_type'    => array('status', 'type', 'nid'),
        'node_title_type'     => array('title', array('type', 4)),
        'node_type'           => array(array('type', 4)),
        'uid'                 => array('uid'),
        'tnid'                => array('tnid'),
        'translate'           => array('translate'),
      ),
      'unique keys' => array(
        'vid'     => array('vid')
      ),
      'primary key' => array('nid'),
    ));
    $database->schema()->createTable('comments', array(
      'description' => 'Stores comments and associated data.',
      'fields' => array(
        'cid' => array(
          'type' => 'serial',
          'not null' => TRUE,
          'description' => 'Primary Key: Unique comment ID.',
        ),
        'pid' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'description' => 'The {comments}.cid to which this comment is a reply. If set to 0, this comment is not a reply to an existing comment.',
        ),
        'nid' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'description' => 'The {node}.nid to which this comment is a reply.',
        ),
        'uid' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'description' => 'The {users}.uid who authored the comment. If set to 0, this comment was created by an anonymous user.',
        ),
        'subject' => array(
          'type' => 'varchar',
          'length' => 64,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The comment title.',
        ),
        'comment' => array(
          'type' => 'text',
          'not null' => TRUE,
          'size' => 'big',
          'description' => 'The comment body.',
        ),
        'hostname' => array(
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
          'default' => '',
          'description' => "The author's host name.",
        ),
        'timestamp' => array(
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'description' => 'The time that the comment was created, or last edited by its author, as a Unix timestamp.',
        ),
        'status' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'size' => 'tiny',
          'description' => 'The published status of a comment. (0 = Published, 1 = Not Published)',
        ),
        'format' => array(
          'type' => 'int',
          'size' => 'small',
          'not null' => TRUE,
          'default' => 0,
          'description' => 'The {filter_formats}.format of the comment body.',
        ),
        'thread' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'description' => "The vancode representation of the comment's place in a thread.",
        ),
        'name' => array(
          'type' => 'varchar',
          'length' => 60,
          'not null' => FALSE,
          'description' => "The comment author's name. Uses {users}.name if the user is logged in, otherwise uses the value typed into the comment form.",
        ),
        'mail' => array(
          'type' => 'varchar',
          'length' => 64,
          'not null' => FALSE,
          'description' => "The comment author's e-mail address from the comment form, if user is anonymous, and the 'Anonymous users may/must leave their contact information' setting is turned on.",
        ),
        'homepage' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
          'description' => "The comment author's home page address from the comment form, if user is anonymous, and the 'Anonymous users may/must leave their contact information' setting is turned on.",
        )
      ),
      'indexes' => array(
        'pid'    => array('pid'),
        'nid'    => array('nid'),
        'comment_uid'    => array('uid'),
        'status' => array('status'), // This index is probably unused
      ),
      'primary key' => array('cid'),
    )
  );

    $database->insert('node')->fields(array(
      'nid',
      'vid',
      'type',
      'language',
      'title',
      'uid',
      'status',
      'created',
      'changed',
      'comment',
      'promote',
      'moderate',
      'sticky',
      'tnid',
      'translate',
    ))
      ->values(array(
        'nid' => 1,
        'vid' => 1,
        'type' => 'story',
        'language'=> 'en',
        'title' => 'My node title',
        'uid' => 1,
        'status' => 1,
        'created' => 1390264988,
        'changed' => 1390264988,
        'comment' => 2,
        'promote' => 1,
        'moderate' => 0,
        'sticky' => 0,
        'tnid' => 0,
        'translate' => 0,
      ))
      ->execute();

    $database->insert('comments')->fields(array(
      'cid',
      'pid',
      'nid',
      'uid',
      'subject',
      'comment',
      'hostname',
      'timestamp',
      'status',
      'format',
      'thread',
      'name',
      'mail',
      'homepage',
    ))
    // The comment structure is:
    // -1
    // -3
    // --2
      ->values(array(
        'cid' => 1,
        'pid' => 0,
        'nid' => 1,
        'uid' => 0,
        'subject' => 'The first comment.',
        'comment' => 'The first comment body.',
        'hostname' => '127.0.0.1',
        'timestamp' => 1390264918,
        'status' => 0,
        'format' => 1,
        'thread' => '01/',
        'name' => '1st comment author name',
        'mail' => 'comment1@example.com',
        'homepage' => 'http://drupal.org',
      ))
      ->values(array(
        'cid' => 2,
        'pid' => 3,
        'nid' => 1,
        'uid' => 0,
        'subject' => 'The response to the second comment.',
        'comment' => 'The second comment response body.',
        'hostname' => '127.0.0.1',
        'timestamp' => 1390264938,
        'status' => 0,
        'format' => 1,
        'thread' => '02/01',
        'name' => '3rd comment author name',
        'mail' => 'comment3@example.com',
        'homepage' => 'http://drupal.org',
      ))
      ->values(array(
        'cid' => 3,
        'pid' => 0,
        'nid' => 1,
        'uid' => 0,
        'subject' => 'The third comment.',
        'comment' => 'The third comment body.',
        'hostname' => '127.0.0.1',
        'timestamp' => 1390264948,
        // This comment is unpublished.
        'status' => 1,
        'format' => 1,
        'thread' => '02/',
        'name' => '3rd comment author name',
        'mail' => 'comment3@example.com',
        'homepage' => 'http://drupal.org',
      ))
      ->execute();
  }

}
