<?php

/**
 * @file
 * Definition of Drupal\simpletest\UnitTestBase.
 */

namespace Drupal\simpletest;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\ConnectionNotDefinedException;

/**
 * Test case for Drupal unit tests.
 *
 * These tests can not access the database nor files. Calling any Drupal
 * function that needs the database will throw exceptions. These include
 * watchdog(), module_implements(), module_invoke_all() etc.
 */
abstract class UnitTestBase extends TestBase {

  /**
   * Constructor for UnitTestBase.
   */
  function __construct($test_id = NULL) {
    parent::__construct($test_id);
    $this->skipClasses[__CLASS__] = TRUE;
  }

  /**
   * Sets up unit test environment.
   *
   * Unlike Drupal\simpletest\WebTestBase::setUp(), UnitTestBase::setUp() does not
   * install modules because tests are performed without accessing the database.
   * Any required files must be explicitly included by the child class setUp()
   * method.
   */
  protected function setUp() {
    global $conf;

    // Create the database prefix for this test.
    $this->prepareDatabasePrefix();

    // Prepare the environment for running tests.
    $this->prepareEnvironment();
    if (!$this->setupEnvironment) {
      return FALSE;
    }
    $this->originalThemeRegistry = theme_get_registry(FALSE);

    // Reset all statics and variables to perform tests in a clean environment.
    $conf = array();
    drupal_static_reset();

    // Enforce an empty module list.
    module_list(NULL, array());

    $conf['file_public_path'] = $this->public_files_directory;

    // Change the database prefix.
    // All static variables need to be reset before the database prefix is
    // changed, since Drupal\Core\Utility\CacheArray implementations attempt to
    // write back to persistent caches when they are destructed.
    $this->changeDatabasePrefix();
    if (!$this->setupDatabasePrefix) {
      return FALSE;
    }

    // Set user agent to be consistent with WebTestBase.
    $_SERVER['HTTP_USER_AGENT'] = $this->databasePrefix;

    $this->setup = TRUE;
  }

    /**
   * Deletes created files, database tables, and reverts all environment changes.
   *
   * This method needs to be invoked for both unit and integration tests.
   *
   * @see TestBase::prepareDatabasePrefix()
   * @see TestBase::changeDatabasePrefix()
   * @see TestBase::prepareEnvironment()
   */
  protected function tearDown() {
    global $user, $conf;
    $language_interface = language(LANGUAGE_TYPE_INTERFACE);

    // In case a fatal error occurred that was not in the test process read the
    // log to pick up any fatal errors.
    simpletest_log_read($this->testId, $this->databasePrefix, get_class($this), TRUE);

    $emailCount = count(variable_get('drupal_test_email_collector', array()));
    if ($emailCount) {
      $message = format_plural($emailCount, '1 e-mail was sent during this test.', '@count e-mails were sent during this test.');
      $this->pass($message, t('E-mail'));
    }

    // Delete temporary files directory.
    file_unmanaged_delete_recursive($this->originalFileDirectory . '/simpletest/' . substr($this->databasePrefix, 10), array($this, 'filePreDeleteCallback'));

    $this->removeTestDatabase();

    // Restore original globals.
    $GLOBALS['theme_key'] = $this->originalThemeKey;
    $GLOBALS['theme'] = $this->originalTheme;

    // Reset all static variables.
    drupal_static_reset();

    // Reset module list and module load status.
    module_list_reset();
    module_load_all(FALSE, TRUE);


    // Restore original in-memory configuration.
    $conf = $this->originalConf;

    // Restore original statics and globals.
    drupal_container($this->originalContainer);
    $language_interface = $this->originalLanguage;
    $GLOBALS['config_directories'] = $this->originalConfigDirectories;
    if (isset($this->originalPrefix)) {
      drupal_valid_test_ua($this->originalPrefix);
    }

    // Restore original shutdown callbacks.
    $callbacks = &drupal_register_shutdown_function();
    $callbacks = $this->originalShutdownCallbacks;

    // Restore original user session.
    $user = $this->originalUser;
    drupal_save_session(TRUE);
  }
}
