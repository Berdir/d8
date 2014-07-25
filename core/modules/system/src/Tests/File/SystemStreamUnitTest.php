<?php

/**
 * @file
 * Contains \Drupal\system\Tests\File\SystemStreamUnitTest.
 */

namespace Drupal\system\Tests\File;

use Drupal\Core\Site\Settings;
use Drupal\simpletest\KernelTestBase;

/**
 * Unit tests for system stream wrapper functions.
 *
 * @group system
 */
class SystemStreamUnitTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system', 'file', 'file_test');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    drupal_static_reset('file_get_stream_wrappers');
    new Settings(Settings::getAll() + array(
      'install_profile' => 'standard',
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Test the Module stream wrapper functions.
   */
  public function testModuleStream() {
    // Generate a module stream wrapper instance.
    $uri1 = 'module://system';
    $uri2 = 'module://system/css/system.admin.css';
    $uri3 = 'module://file_test/file_test.dummy.inc';
    $uri4 = 'module://file_test/includes/file_test.dummy.inc';
    $uri5 = 'module://ckeditor/ckeditor.info.yml';
    $uri6 = 'module://foo_bar/foo.bar.js';
    $instance = file_stream_wrapper_get_instance_by_scheme('module');
    /* @var $instance \Drupal\Core\StreamWrapper\ModuleStream */

    // getOwnerName()
    $this->assertEqual($instance->getOwnerName($uri1), 'system', 'Extract module name from a partial URI.');
    $this->assertEqual($instance->getOwnerName($uri2), 'system', 'Extract module name for a resource located in a subdirectory.');
    $this->assertEqual($instance->getOwnerName($uri3), 'file_test', 'Extract module name for a module located in a subdirectory.');
    $this->assertEqual($instance->getOwnerName($uri4), 'file_test', 'Extract module name even for a non-existing resource, as long as the module exists.');
    $this->assertFalse($instance->getOwnerName($uri5), "Fail returning a disabled module's name.");
    $this->assertFalse($instance->getOwnerName($uri6), "Fail returning a non-existing module's name.");

    // getTarget()
    $this->assertEqual($instance->getTarget($uri1), '', 'Return empty target from a partial URI.');
    $this->assertEqual($instance->getTarget($uri2), 'css/system.admin.css', 'Extract target for a resource located in a subdirectory.');
    $this->assertEqual($instance->getTarget($uri3), 'file_test.dummy.inc', 'Extract target for a module in a non-standard location.');
    $this->assertFalse($instance->getTarget($uri4), 'Fail returning a target for a non-existing resource.');
    $this->assertFalse($instance->getTarget($uri5), "Fail returning a target within a disabled module.");
    $this->assertFalse($instance->getTarget($uri6), "Fail returning a target within a non-existing module.");

    // getDirectoryPath()
    $this->assertEqual($instance->getDirectoryPath($uri1), 'core/modules/system', "Lookup module's directory path for a partial URI.");
    $this->assertEqual($instance->getDirectoryPath($uri2), 'core/modules/system', "Lookup module's directory path for a resource located in a subdirectory.");
    $this->assertEqual($instance->getDirectoryPath($uri3), 'core/modules/file/tests/file_test', "Lookup module's directory path for a module located in a subdirectory.");
    $this->assertEqual($instance->getDirectoryPath($uri4), 'core/modules/file/tests/file_test', "Lookup module's directory path even for a non-existing resource, as long as the module exists.");
    $this->assertFalse($instance->getDirectoryPath($uri5), "Fail returning a disabled module's directory path");
    $this->assertFalse($instance->getDirectoryPath($uri6), "Fail returning a non-existing module's directory path.");

    // getExternalUrl()
    $base_url = \Drupal::request()->getBaseUrl() . '/';
    $this->assertEqual($instance->getExternalUrl($uri1), $base_url . 'core/modules/system', "Lookup module's directory path for a partial URI.");
    $this->assertEqual($instance->getExternalUrl($uri2), $base_url . 'core/modules/system/css/system.admin.css', "Lookup module's directory path for a resource located in a subdirectory.");
    $this->assertEqual($instance->getExternalUrl($uri3), $base_url . 'core/modules/file/tests/file_test/file_test.dummy.inc', "Lookup module's directory path for a module located in a subdirectory.");
    $this->assertEqual($instance->getExternalUrl($uri4), $base_url . 'core/modules/file/tests/file_test/includes/file_test.dummy.inc', "Lookup module's directory path even for a non-existing resource, as long as the module exists.");
    $this->assertFalse($instance->getExternalUrl($uri5), "Fail returning a disabled module's directory path");
    $this->assertFalse($instance->getExternalUrl($uri6), "Fail returning a non-existing module's directory path.");
  }

  /**
   * Test the Profile stream wrapper functions.
   */
  public function testProfileStream() {
    $uri1 = 'profile://minimal';
    $uri2 = 'profile://minimal/config/install/block.block.stark_login.yml';
    $uri3 = 'profile://minimal/config/install/node.type.article.yml';
    $uri4 = 'profile://foo_bar/';
    $uri5 = 'profile://current';
    $uri6 = 'profile://current/standard.info.yml';
    $instance = file_stream_wrapper_get_instance_by_scheme('profile');
    /* @var $instance \Drupal\Core\StreamWrapper\ProfileStream */

    // getOwnerName()
    $this->assertEqual($instance->getOwnerName($uri1), 'minimal', "Extract profile's name from a partial URI.");
    $this->assertEqual($instance->getOwnerName($uri2), 'minimal', "Extract profile's name for a resource located in a subdirectory.");
    $this->assertEqual($instance->getOwnerName($uri3), 'minimal', "Extract profile's name even for a non-existing resource, as long as the profile exists.");
    $this->assertFalse($instance->getOwnerName($uri4), "Fail returning a non-existing profile's name.");
    $this->assertEqual($instance->getOwnerName($uri5), 'standard', format_string('Lookup real name of %current for a partial URI.', array('%current' => 'profile://current')));
    $this->assertEqual($instance->getOwnerName($uri6), 'standard', format_string('Lookup real name of %current for a resource.', array('%current' => 'profile://current')));

    // getTarget()
    $this->assertEqual($instance->getTarget($uri1), '', 'Return empty target for a partial URI giving only the profile.');
    $this->assertEqual($instance->getTarget($uri2), 'config/install/block.block.stark_login.yml', 'Extract target for a resource located in a subdirectory.');
    $this->assertFalse($instance->getTarget($uri3), 'Fail returning a target for a non-existing resource.');
    $this->assertFalse($instance->getTarget($uri4), 'Fail returning a target within a non-existing profile.');
    $this->assertEqual($instance->getTarget($uri5), '', format_string('Return empty target for a partial URI giving only %current.', array('%current' => 'profile://current')));
    $this->assertEqual($instance->getTarget($uri6), 'standard.info.yml', format_string("Extract target from a resource within %current.", array('%current' => 'profile://current')));

    // getDirectoryPath()
    $this->assertEqual($instance->getDirectoryPath($uri1), 'core/profiles/minimal', "Lookup profile's directory path for a partial URI.");
    $this->assertEqual($instance->getDirectoryPath($uri2), 'core/profiles/minimal', "Lookup profile's directory path for a resource located in a subdirectory.");
    $this->assertEqual($instance->getDirectoryPath($uri3), 'core/profiles/minimal', "Lookup profile's directory path even for a non-existing resource, as long as the profile exists.");
    $this->assertFalse($instance->getDirectoryPath($uri4), "Fail returning a non-existing profile's directory path.");
    $this->assertEqual($instance->getDirectoryPath($uri5), 'core/profiles/standard', format_string('Lookup real directory path of %current for a partial URI.', array('%current' => 'profile://current')));
    $this->assertEqual($instance->getDirectoryPath($uri6), 'core/profiles/standard', format_string('Lookup real directory path of %current for a resource.', array('%current' => 'profile://current')));

    // getExternalUrl()
    $base_url = \Drupal::request()->getBaseUrl() . '/';
    $this->assertEqual($instance->getExternalUrl($uri1), $base_url . 'core/profiles/minimal', "Lookup profile's directory path for a partial URI.");
    $this->assertEqual($instance->getExternalUrl($uri2), $base_url . 'core/profiles/minimal/config/install/block.block.stark_login.yml', "Lookup profile's directory path for a resource located in a subdirectory.");
    $this->assertEqual($instance->getExternalUrl($uri3), $base_url . 'core/profiles/minimal/config/install/node.type.article.yml', "Lookup profile's directory path even for a non-existing resource, as long as the profile exists.");
    $this->assertFalse($instance->getExternalUrl($uri4), "Fail returning a non-existing profile's directory path.");
    $this->assertEqual($instance->getExternalUrl($uri5), $base_url . 'core/profiles/standard', format_string('Lookup real directory path of %current for a partial URI.', array('%current' => 'profile://current')));
    $this->assertEqual($instance->getExternalUrl($uri6), $base_url . 'core/profiles/standard/standard.info.yml', format_string('Lookup real directory path of %current for a resource.', array('%current' => 'profile://current')));
  }

  /**
   * Test the Theme stream wrapper functions.
   */
  public function testThemeStream() {
    // Disable Bartik theme.
    $system_theme_disabled = \Drupal::config('system.theme.disabled');
    $this->assertNotIdentical($system_theme_disabled->get('bartik'), 0, format_string('%bartik theme was enabled.', array('%bartik' => 'Bartik')));
    $system_theme_disabled->set('bartik', 0)->save();
    $this->assertIdentical($system_theme_disabled->get('bartik'), 0, format_string('Now disable %bartik theme.', array('%bartik' => 'Bartik')));

    // Set admin theme to Seven.
    $system_theme = \Drupal::config('system.theme');
    $this->assertNull($system_theme->get('admin'), 'No admin theme was set.');
    file_put_contents('admin.theme', print_r($system_theme->get('admin'), TRUE));
    $system_theme->set('admin', 'seven')->save();
    $this->assertEqual($system_theme->get('admin'), 'seven', format_string('Now make %seven the admin theme.', array('%seven' => 'Seven')));

    $uri1 = 'theme://seven';
    $uri2 = 'theme://seven/style.css';
    $uri3 = 'theme://bartik/color/preview.js';
    $uri4 = 'theme://fifteen/screenshot.png';
    $uri5 = 'theme://current';
    $uri6 = 'theme://current/logo.png';
    $uri7 = 'theme://default';
    $uri8 = 'theme://default/stark.info.yml';
    $uri9 = 'theme://admin';
    $uri10 = 'theme://admin/stark.info.yml';
    $instance = file_stream_wrapper_get_instance_by_scheme('theme');
    /* @var $instance \Drupal\Core\StreamWrapper\ThemeStream */

    // getOwnerName()
    $this->assertEqual($instance->getOwnerName($uri1), 'seven', "Extract theme's name from a partial URI.");
    $this->assertEqual($instance->getOwnerName($uri2), 'seven', "Extract theme's name from a full URI.");
    $this->assertEqual($instance->getOwnerName($uri3), 'bartik', "Extract theme's name from a full URI with subdirectory.");
    $this->assertFalse($instance->getOwnerName($uri4), "Fail returning a non-existing theme's name.");
    $this->assertEqual($instance->getOwnerName($uri5), 'bla', format_string('Lookup real name of %current for a partial URI.', array('%current' => 'theme://current')) . $instance->getOwnerName($uri5));
    $this->assertEqual($instance->getOwnerName($uri6), 'bla', format_string('Lookup real name of %current for a resource.', array('%current' => 'theme://current')) . $instance->getOwnerName($uri6));
    $this->assertEqual($instance->getOwnerName($uri7), 'stark', format_string('Lookup real name of %default for a partial URI.', array('%default' => 'theme://default')));
    $this->assertEqual($instance->getOwnerName($uri8), 'stark', format_string('Lookup real name of %default for a resource.', array('%default' => 'theme://default')));
    $this->assertEqual($instance->getOwnerName($uri9), 'seven', format_string('Lookup real name of %admin for a partial URI.', array('%admin' => 'theme://admin')));
    $this->assertEqual($instance->getOwnerName($uri10), 'seven', format_string('Lookup real name of %admin even for a non-existing resource.', array('%admin' => 'theme://admin')));

    // getTarget()
    $this->assertEqual($instance->getTarget($uri1), '', 'Return empty target for a partial URI giving only the theme.');
    $this->assertEqual($instance->getTarget($uri2), 'style.css', 'Extract target for a resource.' . $instance->getTarget($uri3));
    $this->assertEqual($instance->getTarget($uri3), 'color/preview.js', 'Extract target for a resource located in a subdirectory.');
    $this->assertFalse($instance->getTarget($uri4), 'Fail returning a target within a non-existing theme.');
    $this->assertEqual($instance->getTarget($uri5), '', format_string('Return empty target for a partial URI giving only %current.', array('%current' => 'theme://current')));
    $this->assertEqual($instance->getTarget($uri6), 'logo.png', format_string("Extract target from a resource within %current.", array('%current' => 'theme://current')));
    $this->assertEqual($instance->getTarget($uri7), '', format_string('Return empty target for a partial URI giving only %default.', array('%default' => 'theme://default')));
    $this->assertEqual($instance->getTarget($uri8), 'stark.info.yml', format_string("Extract target from a resource located in a subdirectory of %default.", array('%default' => 'theme://default')) . $instance->getTarget($uri8));
    $this->assertEqual($instance->getTarget($uri9), '', format_string('Return empty target for a partial URI giving only %admin.', array('%admin' => 'theme://admin')));
    $this->assertFalse($instance->getTarget($uri10), format_string("Fail returning target for a non-existing resource within %admin.", array('%admin' => 'theme://admin')));

    // getDirectoryPath()
    $this->assertEqual($instance->getDirectoryPath($uri1), 'core/themes/seven', "Lookup theme's directory path for a partial URI.");
    $this->assertEqual($instance->getDirectoryPath($uri2), 'core/themes/seven', "Lookup theme's directory path for a resource located in a subdirectory.");
    $this->assertEqual($instance->getDirectoryPath($uri3), 'core/themes/bartik', "Lookup theme's directory path for a resource.");
    $this->assertFalse($instance->getDirectoryPath($uri4), "Fail returning a non-existing theme's directory path.");
    $this->assertEqual($instance->getDirectoryPath($uri5), 'core/themes/standard', format_string('Lookup real directory path of %current for a partial URI.', array('%current' => 'profile://current')));
    $this->assertEqual($instance->getDirectoryPath($uri6), 'core/themes/standard', format_string('Lookup real directory path of %current for a resource.', array('%current' => 'profile://current')));
    $this->assertEqual($instance->getDirectoryPath($uri7), 'core/themes/stark', format_string('Lookup real directory path of %default for a partial URI.', array('%default' => 'profile://default')));
    $this->assertEqual($instance->getDirectoryPath($uri8), 'core/themes/stark', format_string('Lookup real directory path of %default for a resource.', array('%default' => 'profile://default')));
    $this->assertEqual($instance->getDirectoryPath($uri9), 'core/themes/seven', format_string('Lookup real directory path of %admin for a partial URI.', array('%admin' => 'profile://admin')));
    $this->assertEqual($instance->getDirectoryPath($uri10), 'core/themes/seven', format_string('Lookup real directory path of %admin for a resource.', array('%admin' => 'profile://admin')));

    // getExternalUrl()
    $base_url = \Drupal::request()->getBaseUrl() . '/';
    $this->assertEqual($instance->getExternalUrl($uri1), $base_url . 'core/themes/seven', "Lookup theme's directory path for a partial URI.");
    $this->assertEqual($instance->getExternalUrl($uri2), $base_url . 'core/themes/seven', "Lookup theme's directory path for a resource located in a subdirectory.");
    $this->assertEqual($instance->getExternalUrl($uri3), $base_url . 'core/themes/bartik', "Lookup theme's directory path for a resource.");
    $this->assertFalse($instance->getExternalUrl($uri4), "Fail returning a non-existing theme's directory path.");
    $this->assertEqual($instance->getExternalUrl($uri5), $base_url . 'core/themes/standard', format_string('Lookup real directory path of %current for a partial URI.', array('%current' => 'profile://current')));
    $this->assertEqual($instance->getExternalUrl($uri6), $base_url . 'core/themes/standard', format_string('Lookup real directory path of %current for a resource.', array('%current' => 'profile://current')));
    $this->assertEqual($instance->getExternalUrl($uri7), $base_url . 'core/themes/stark', format_string('Lookup real directory path of %default for a partial URI.', array('%default' => 'profile://default')));
    $this->assertEqual($instance->getExternalUrl($uri8), $base_url . 'core/themes/stark', format_string('Lookup real directory path of %default for a resource.', array('%default' => 'profile://default')));
    $this->assertEqual($instance->getExternalUrl($uri9), $base_url . 'core/themes/seven', format_string('Lookup real directory path of %admin for a partial URI.', array('%admin' => 'profile://admin')));
    $this->assertEqual($instance->getExternalUrl($uri10), $base_url . 'core/themes/seven', format_string('Lookup real directory path of %admin for a resource.', array('%admin' => 'profile://admin')));
  }
}
