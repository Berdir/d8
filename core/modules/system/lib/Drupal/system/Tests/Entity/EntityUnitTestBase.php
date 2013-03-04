<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Entity\EntityUnitTestBase.
 */

namespace Drupal\system\Tests\Entity;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Defines an abstract test base for entity unit tests.
 */
abstract class EntityUnitTestBase extends DrupalUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('user', 'system', 'field', 'text', 'field_sql_storage', 'entity_test');

  public function setUp() {
    parent::setUp();
    $this->installSchema('user', 'users');
    $this->installSchema('system', 'sequences');
    $this->installSchema('field', array('field_config', 'field_config_instance'));
    $this->installSchema('entity_test', 'entity_test');
  }

  /**
   * Creates a user.
   *
   * @param array $values
   *   (optional) The values used to create the entity. To perform access checks
   *   and prevent the new user from becoming uid 1 (which exempts from all user
   *   access conditions), pass a unique value other than 1 as 'uid' in $values.
   *
   * @return \Drupal\user\Plugin\Core\Entity\User
   *   The created user entity.
   */
  protected function createUser($values = array()) {
    $account = entity_create('user', $values + array(
      'name' => $this->randomName(),
    ));
    // Force the entity to be new, so that callers are able to specify
    // a 'uid' in $values to skip over uid 1. If no uid is provided explicitly,
    // \Drupal\user\UserStorageController will automatically choose and insert
    // the next available ID.
    $account->enforceIsNew();
    $account->save();
    return $account;
  }

  /**
   * Creates a user role with provided permissions.
   *
   * The {role_permission} and {users_roles} tables must be installed before
   * this can be used.
   *
   * @param array $permissions
   *   List of permission names the role should have.
   *
   * @return \Drupal\user\Plugin\Core\Entity\Role
   *   The created user entity.
   */
  protected function createUserRole($permissions) {
    // Create a new role and apply permissions to it.
    $role = entity_create('user_role', array(
      'id' => strtolower($this->randomName(8)),
      'label' => $this->randomName(8),
    ));
    $role->save();
    user_role_grant_permissions($role->id(), $permissions);
    return $role;
  }

}
