<?php

/**
 * @file
 * Definition of Drupal\node\Tests\NodeUnitTestBase.
 */

namespace Drupal\node\Tests;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Sets up page and article content types.
 */
abstract class NodeUnitTestBase extends DrupalUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'datetime', 'entity', 'user', 'field', 'field_sql_storage');

  /**
   * User object used for creating nodes.
   * @var type
   */
  protected $user;

  function setUp() {
    parent::setUp();
    $this->installSchema('user', array('users', 'users_roles'));
    $this->installSchema('field', array('field_config', 'field_config_instance'));
    $this->installSchema('node', array('node', 'node_revision', 'node_type', 'node_access'));
  }

  /**
   * Creates a node.
   *
   * @param array $values
   *   Array of values to pass to entity_create().
   *
   * @return \Drupal\node\Plugin\Core\Entity\Node
   *   An unsaved node entity.
   */
  function createNode($values = array()) {
    if (!$this->user) {
      $this->user = entity_create('user', array('uid' => 2));
      $this->user->enforceIsNew();
      $this->user->save();
    }
    return entity_create('node', $values + array('uid' => $this->user->id()));
  }
}
