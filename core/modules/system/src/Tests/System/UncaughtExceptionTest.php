<?php
/**
 * @file
 * Contains UncaughtExceptionTest.php
 */

namespace Drupal\system\Tests\System;


use Drupal\simpletest\WebTestBase;

/**
 * Tests kernel panic when things are really messed up.
 *
 * @group system
 */
class UncaughtExceptionTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('error_service_test');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $settings_filename = $this->siteDirectory . '/settings.php';
    chmod($settings_filename, 0777);
    $settings_php = file_get_contents($settings_filename);
    $settings_php .= "\ninclude_once 'core/modules/system/src/Tests/Bootstrap/ErrorContainer.php';\n";
    $settings_php .= "\ninclude_once 'core/modules/system/src/Tests/Bootstrap/ExceptionContainer.php';\n";
    file_put_contents($settings_filename, $settings_php);
  }

  /**
   * Tests uncaught exception handling when system is in a bad state.
   */
  public function testUncaughtException() {
    \Drupal::state()->set('error_service_test.break_bare_html_renderer', TRUE);

    $this->config('system.logging')
      ->set('error_level', ERROR_REPORTING_HIDE)
      ->save();
    $this->drupalGet('');
    $this->assertResponse(500);
    $this->assertText('The website encountered an unexpected error. Please try again later.');
    $this->assertNoText('Oh oh, bananas in the instruments');

    $this->config('system.logging')
      ->set('error_level', ERROR_REPORTING_DISPLAY_ALL)
      ->save();
    $this->drupalGet('');
    $this->assertResponse(500);
    $this->assertText('The website encountered an unexpected error. Please try again later.');
    $this->assertText('Oh oh, bananas in the instruments');
  }

  /**
   * Tests a missing dependency on a service.
   */
  public function testMissingDependency() {
    $this->drupalGet('broken-service-class');

    $message = 'Argument 1 passed to Drupal\error_service_test\LonelyMonkeyClass::__construct() must be an instance of Drupal\Core\Database\Connection, non';

    $this->assertRaw('The website encountered an unexpected error.');
    $this->assertRaw($message);

    $found_exception = FALSE;
    foreach ($this->assertions as &$assertion) {
      if (strpos($assertion['message'], $message) !== FALSE) {
        $found_exception = TRUE;
        $this->deleteAssert($assertion['message_id']);
        unset($assertion);
      }
    }

    $this->assertTrue($found_exception, 'Ensure that the exception of a missing constructor argument was triggered.');
  }

  /**
   * Tests a container which has an error.
   */
  public function testErrorContainer() {
    $kernel = ErrorContainerRebuildKernel::createFromRequest($this->prepareRequestForGenerator(), $this->classLoader, 'prod', TRUE);
    $kernel->rebuildContainer();

    $this->prepareRequestForGenerator();
    // Ensure that we don't use the now broken generated container on the test
    // process.
    \Drupal::setContainer($this->container);

    $this->drupalGet('');
    $this->assertRaw('Fatal error');

    $found_error = FALSE;
    foreach ($this->assertions as &$assertion) {
      if ($assertion['message'] == 'Fatal error') {
        $found_error = TRUE;
        $this->deleteAssert($assertion['message_id']);
        unset($assertion);
      }
    }

    // The errors are expected. Do not interpret them as a test failure.
    // Not using File API; a potential error must trigger a PHP warning.
    unlink(\Drupal::root() . '/' . $this->siteDirectory . '/error.log');

    $this->assertTrue($found_error, 'Ensure that the error of the container was triggered.');
  }

  /**
   * Tests a container which has an exception really early.
   */
  public function testExceptionContainer() {
    $kernel = ExceptionContainerRebuildKernel::createFromRequest($this->prepareRequestForGenerator(), $this->classLoader, 'prod', TRUE);
    $kernel->rebuildContainer();

    $this->prepareRequestForGenerator();
    // Ensure that we don't use the now broken generated container on the test
    // process.
    \Drupal::setContainer($this->container);

    $this->drupalGet('');

    $message = 'Thrown exception during Container::get';

    $this->assertRaw('The website encountered an unexpected error');
    $this->assertRaw($message);

    $found_exception = FALSE;
    foreach ($this->assertions as &$assertion) {
      if (strpos($assertion['message'], $message) !== FALSE) {
        $found_exception = TRUE;
        $this->deleteAssert($assertion['message_id']);
        unset($assertion);
      }
    }

    $this->assertTrue($found_exception, 'Ensure that the exception of the container was triggered.');
  }

  /**
   * {@inheritdoc}
   */
  protected function error($message = '', $group = 'Other', array $caller = NULL) {
    if ($message === 'Oh oh, bananas in the instruments.') {
      // We're expecting this error.
      return;
    }
    return parent::error($message, $group, $caller);
  }

}
