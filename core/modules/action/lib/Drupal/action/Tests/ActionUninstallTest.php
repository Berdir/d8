<?php

/**
 * @file
 * Contains \Drupal\action\Tests\ActionUninstallTest.
 */

namespace Drupal\action\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\system\Entity\Action;

/**
 * Tests action uninstallation.
 *
 * @see \Drupal\action\Plugin\views\field\BulkForm
 */
class ActionUninstallTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('views', 'action');

  public static function getInfo() {
    return array(
      'name' => 'Uninstall action test',
      'description' => 'Tests that uninstalling actions does not remove other module\'s actions.',
      'group' => 'Action',
    );
  }

  /**
   * Tests Action uninstall.
   */
  public function testActionUninstall() {
    \Drupal::moduleHandler()->uninstall(array('action'));

    \Drupal::entityManager()->getStorageController('action')->resetCache();
    $this->assertTrue(Action::load('user_block_user_action'), 'Configuration entity \'user_block_user_action\' still exists after uninstalling action module.' );

    $admin_user = $this->drupalCreateUser(array('administer users'));
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/people');
    // Ensure we have the user_block_user_action listed.
    $this->assertRaw('<option value="user_block_user_action">Block the selected user(s)</option>');

  }

}
