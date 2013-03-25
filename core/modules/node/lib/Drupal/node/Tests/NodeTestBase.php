<?php

/**
 * @file
 * Definition of Drupal\node\Tests\NodeTestBase.
 */

namespace Drupal\node\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Sets up page and article content types.
 */
abstract class NodeTestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'datetime');

  function setUp() {
    parent::setUp();

    if ($this->profile != 'standard') {
      // Enable default permissions for system roles.
      user_role_grant_permissions(DRUPAL_ANONYMOUS_RID, array('access content'));
      user_role_grant_permissions(DRUPAL_AUTHENTICATED_RID, array('access content'));

      // Create Basic page and Article node types.
      $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));
      $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
    }
  }
}
