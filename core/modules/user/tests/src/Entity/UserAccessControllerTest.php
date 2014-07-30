<?php

/**
 * @file
 * Contains \Drupal\user\Tests\Entity\UserAccessControllerTest.
 */

namespace Drupal\user\Tests\Entity;

use Drupal\Component\Utility\String;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserAccessController;

/**
 * Tests the user access controller.
 *
 * @group Drupal
 * @group User
 *
 * @coversDefaultClass \Drupal\user\UserAccessController
 */
class UserAccessControllerTest extends UnitTestCase {

  /**
   * The user access controller to test.
   *
   * @var \Drupal\user\UserAccessController
   */
  protected $accessController;

  /**
   * The mock user account with view access.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $viewer;

  /**
   * The mock user account that is able to change their own account name.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $owner;

  /**
   * The mock adminstrative test user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $admin;

  /**
   * The mocked test field items.
   *
   * @var \Drupal\Core\Field\FieldItemList
   */
  protected $items;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'User access controller',
      'description' => 'Tests the user access controller.',
      'group' => 'User',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->viewer = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $this->viewer
      ->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValue(FALSE));
    $this->viewer
      ->expects($this->any())
      ->method('id')
      ->will($this->returnValue(1));

    $this->owner = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $this->owner
      ->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValueMap(array(
        array('administer users', FALSE),
        array('change own username', TRUE),
      )));

    $this->owner
      ->expects($this->any())
      ->method('id')
      ->will($this->returnValue(2));

    $this->admin = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $this->admin
      ->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValue(TRUE));

    $entity_type = $this->getMock('Drupal\Core\Entity\EntityTypeInterface');

    $this->accessController = new UserAccessController($entity_type);
    $module_handler = $this->getMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $module_handler->expects($this->any())
      ->method('getImplementations')
      ->will($this->returnValue(array()));
    $this->accessController->setModuleHandler($module_handler);

    $this->items = $this->getMockBuilder('Drupal\Core\Field\FieldItemList')
      ->disableOriginalConstructor()
      ->getMock();
    $this->items
      ->expects($this->any())
      ->method('defaultAccess')
      ->will($this->returnValue(TRUE));
  }

  /**
   * Asserts correct field access grants for a field.
   */
  public function assertFieldAccess($field, $viewer, $target, $view, $edit) {
    $field_definition = $this->getMock('Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->any())
      ->method('getName')
      ->will($this->returnValue($field));

    $this->items
      ->expects($this->any())
      ->method('getEntity')
      ->will($this->returnValue($this->{$target}));

    foreach (array('view' => $view, 'edit' => $edit) as $operation => $result) {
      $message = String::format("User @field field access returns @result with operation '@op' for @account accessing @target", array(
        '@field' => $field,
        '@result' => !isset($result) ? 'null' : ($result ? 'true' : 'false'),
        '@op' => $operation,
        '@account' => $viewer,
        '@target' => $target,
      ));
      $this->assertSame($result, $this->accessController->fieldAccess($operation, $field_definition, $this->{$viewer}, $this->items), $message);
    }
  }

  /**
   * Ensures user name access is working properly.
   *
   * @dataProvider userNameProvider
   */
  public function testUserNameAccess($viewer, $target, $view, $edit) {
    $this->assertFieldAccess('name', $viewer, $target, $view, $edit);
  }

  /**
   * Provides test data for estUserNameAccess().
   */
  public function userNameProvider() {
    $name_access = array(
      // The viewer user is allowed to see user names on all accounts.
      array(
        'viewer' => 'viewer',
        'target' => 'viewer',
        'view' => TRUE,
        'edit' => FALSE,
      ),
      array(
        'viewer' => 'owner',
        'target' => 'viewer',
        'view' => TRUE,
        'edit' => FALSE,
      ),
      array(
        'viewer' => 'viewer',
        'target' => 'owner',
        'view' => TRUE,
        'edit' => FALSE,
      ),
      // The owner user is allowed to change its own user name.
      array(
        'viewer' => 'owner',
        'target' => 'owner',
        'view' => TRUE,
        'edit' => TRUE,
      ),
      // The users-administrator user has full access.
      array(
        'viewer' => 'admin',
        'target' => 'owner',
        'view' => TRUE,
        'edit' => TRUE,
      ),
    );
    return $name_access;
  }

  /**
   * Tests that private user settings cannot be viewed by other users.
   *
   * @dataProvider hiddenUserSettingsProvider
   */
  public function testHiddenUserSettings($field, $viewer, $target, $view, $edit) {
    $this->assertFieldAccess($field, $viewer, $target, $view, $edit);
  }

  /**
   * Provides test data for testHiddenUserSettings().
   */
  public function hiddenUserSettingsProvider() {
    foreach (array(
      'preferred_langcode',
      'preferred_admin_langcode',
      'signature',
      'signature_format',
      'timezone',
      'mail') as $field
    ) {
      $access_info = array(
        array(
          'field' => $field,
          'viewer' => 'viewer',
          'target' => 'viewer',
          'view' => TRUE,
          'edit' => TRUE,
        ),
        array(
          'field' => $field,
          'viewer' => 'viewer',
          'target' => 'owner',
          'view' => FALSE,
          // Anyone with edit access to the user can also edit these fields.
          'edit' => TRUE,
        ),
        array(
          'field' => $field,
          'viewer' => 'admin',
          'target' => 'owner',
          'view' => TRUE,
          'edit' => TRUE,
        )
      );
      return $access_info;
    }
  }

  /**
   * Tests that private user settings cannot be viewed by other users.
   *
   * @dataProvider adminFieldAccessProvider
   */
  public function testAdminFieldAccess($field, $viewer, $target, $view, $edit) {
    $this->assertFieldAccess($field, $viewer, $target, $view, $edit);
  }

  /**
   * Provides test data for testAdminFieldAccess().
   */
  public function adminFieldAccessProvider() {
    foreach (array(
      'roles',
      'status',
      'access',
      'login',
      'init') as $field
    ) {
      $access_info = array(
        array(
          'field' => $field,
          'viewer' => 'viewer',
          'target' => 'viewer',
          'view' => FALSE,
          'edit' => FALSE,
        ),
        array(
          'field' => $field,
          'viewer' => 'viewer',
          'target' => 'owner',
          'view' => FALSE,
          'edit' => FALSE,
        ),
        array(
          'field' => $field,
          'viewer' => 'admin',
          'target' => 'owner',
          'view' => TRUE,
          'edit' => TRUE,
        )
      );
      return $access_info;
    }
  }

  /**
   * Tests that paswords cannot be viewed, just edited.
   *
   * @dataProvider passwordAccessProvider
   */
  public function testPasswordAccess($viewer, $target, $view, $edit) {
    $this->assertFieldAccess('pass', $viewer, $target, $view, $edit);
  }

  /**
   * Provides test data for passwordAccessProvider().
   */
  public function passwordAccessProvider() {
    $pass_access = array(
      array(
        'viewer' => 'viewer',
        'target' => 'viewer',
        'view' => FALSE,
        'edit' => TRUE,
      ),
      array(
        'viewer' => 'owner',
        'target' => 'viewer',
        'view' => FALSE,
        'edit' => TRUE,
      ),
      array(
        'viewer' => 'admin',
        'target' => 'owner',
        'view' => TRUE,
        'edit' => TRUE,
      ),
    );
    return $pass_access;
  }

  /**
   * Tests the user created field access.
   *
   * @dataProvider createdAccessProvider
   */
  public function testCreatedAccess($viewer, $target, $view, $edit) {
    $this->assertFieldAccess('created', $viewer, $target, $view, $edit);
  }

  /**
   * Provides test data for testCreatedAccess().
   */
  public function createdAccessProvider() {
    $created_access = array(
      array(
        'viewer' => 'viewer',
        'target' => 'viewer',
        'view' => TRUE,
        'edit' => FALSE,
      ),
      array(
        'viewer' => 'owner',
        'target' => 'viewer',
        'view' => TRUE,
        'edit' => FALSE,
      ),
      array(
        'viewer' => 'admin',
        'target' => 'owner',
        'view' => TRUE,
        'edit' => TRUE,
      ),
    );
    return $created_access;
  }

}
