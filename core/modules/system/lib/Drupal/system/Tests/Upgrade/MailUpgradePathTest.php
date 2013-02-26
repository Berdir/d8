<?php

/**
 * @file
 * Definition of \Drupal\system\Tests\Upgrade\MailUpgradePathTest.
 */

namespace Drupal\system\Tests\Upgrade;

/**
 * Tests upgrade of mail backend system variables.
 */
class MailUpgradePathTest extends UpgradePathTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Mail upgrade test',
      'description' => 'Tests upgrade of Mail backend configuration.',
      'group' => 'Upgrade path',
    );
  }

  public function setUp() {
    $this->databaseDumpFiles = array(
      drupal_get_path('module', 'system') . '/tests/upgrade/drupal-7.bare.standard_all.database.php.gz',
      drupal_get_path('module', 'system') . '/tests/upgrade/drupal-7.system.database.php',
    );
    parent::setUp();
  }

  /**
   * Tests that mail backends are upgraded to their Drupal 8 equivalents.
   */
  function testMailSystemUpgrade() {
    // Perform the upgrade without redirecting to check for pending updates,
    // so that we can test for user warnings.
    $this->setPendingUpdateCheck(FALSE);
    $this->performUpgrade(TRUE);
    // Ensure the user is informed about mail backends that need updating.
    $this->assertText('The following mail backends need to be re-configured: MaillogMailSystem', 'User notified about outdated mail backends.');
    $this->assertTrue($this->checkPendingUpdates(), 'The upgrade was completed successfully.');

    // Get the new mailer definitions.
    $mail_system = config('system.mail')->get('interface');

    // Check that the default mailer has been changed to a PSR-0 definition.
    $this->assertTrue($mail_system['default'] == 'Drupal\Core\Mail\PhpMail', 'Default mailer upgraded to Drupal 8 syntax.');

    // Check that a custom mailer has been preserved through the upgrade.
    $this->assertTrue($mail_system['maillog'] == 'MaillogMailSystem', 'Custom mail backend preserved during upgrade.');
  }

}
