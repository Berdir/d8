<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Theme\RegistryUnitTest.
 */

namespace Drupal\system\Tests\Theme;

use Drupal\Core\Theme\Registry;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests the theme registry.
 */
class RegistryUnitTest extends DrupalUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('system', 'theme_test');

  protected $registries = array();

  public static function getInfo() {
    return array(
      'name' => 'Theme Registry',
      'description' => 'Tests the theme registry.',
      'group' => 'Theme',
    );
  }

  function setUp() {
    parent::setUp();
    $this->registries = array();

    $this->installSchema('system', 'variable');

    theme_enable(array('test_theme'));
  }

  /**
   * Returns an registry instance for a certain theme.
   *
   * @param string $theme_name
   *   The name of the theme, which we need a registry for.
   *
   * @return \Drupal\Core\Theme\Registry
   *   Returns a registry instance.
   */
  protected function getRegistry($theme_name) {
    if (!isset($this->registries[$theme_name])) {
      $this->registries[$theme_name] = new Registry($this->container->get('cache.cache'), $this->container->get('module_handler'), $theme_name);
    }
    return $this->registries[$theme_name];
  }

  /**
   * Tests the behavior of the theme registry class.
   */
  function testThemeScope() {
    theme_enable(array('test_basetheme', 'test_subtheme'));
    // Load test_theme's template.php to verify that its hook_theme() is not
    // included for test_subtheme, even though it is defined.
    include_once DRUPAL_ROOT . '/' . drupal_get_path('theme', 'test_theme') . '/template.php';
    // Retrieve a random hook from its definition.

    $registry = $this->getRegistry('test_subtheme')->get();
    $this->assertTrue(isset($registry));
    echo "<pre>"; var_dump($registry); echo "</pre>\n";
  }

  /**
   * Tests registration of new hooks in a module.
   */
  function testModuleNewHooks() {
    $registry = $this->getRegistry('test_theme')->get();
    $path = drupal_get_path('module', 'theme_test');
    $file = $path . '/test_theme.inc';

    // Verify that a new theme function is registered correctly.
    $this->assertEqual($registry['test_theme_new_function'], array(
      'type' => 'module',
      'name' => 'theme_test',
      'theme path' => $path,
      'function' => 'theme_test_theme_new_function',
      'variables' => array(),
    ));
  }

  /**
   * Tests registration of new hooks in a theme.
   */
  function testThemeNewHooks() {
    $registry = $this->getRegistry('test_theme')->get();
    $path = drupal_get_path('theme', 'test_theme');
    $file = $path . '/test_theme.inc';

    // Verify that a new theme function is registered correctly.
    $this->assertEqual($registry['test_theme_new_function'], array(
      'type' => 'theme',
      'name' => 'test_theme',
      'theme path' => $path,
      'function' => 'test_theme_test_theme_new_function',
      'variables' => array(),
    ));

    // Verify theme functions with includes files.
    $this->assertInArray($registry['test_theme_new_function_include']['includes'], $file);
    $this->assertInArray($registry['test_theme_new_function_preprocess_include']['includes'], $file);
    // Neither 'path' nor 'file' should be recorded for them, since they are
    // functions, not templates.
    $this->assertNotIsset($registry['test_theme_new_function_include'], 'path');
    $this->assertNotIsset($registry['test_theme_new_function_preprocess_include'], 'path');

    // Verify theme functions with additional preprocess functions.
    // Base and module preprocess functions are not expected to run for theme
    // functions currently, due to performance reasons.
    $this->assertEqual($registry['test_theme_new_function_preprocess']['preprocess'], array(
      'template_preprocess_test_theme_new_function_preprocess',
    ));
    $this->assertNotIsset($registry['test_theme_new_function_preprocess'], 'process');

    // Verify custom theme function, explicitly defined in hook_theme().
    $this->assertEqual($registry['test_theme_new_function_custom']['function'], 'test_theme_new_function_customized');

    // Verify that a new theme template is registered correctly.
    $this->assertEqual($registry['test_theme_new_template'], array(
      'type' => 'theme',
      'name' => 'test_theme',
      'variables' => array(),
      'template' => 'test_theme_new_template',
      'theme path' => $path,
      'path' => $path . '/templates',
      'preprocess' => array(
        'template_preprocess',
        'theme_test_preprocess',
        'test_theme_preprocess',
      ),
      'process' => array(
        'theme_test_process',
        'test_theme_process',
      ),
    ));

    // Verify theme templates with additional preprocess functions.
    // - template_* preprocessors have to be sorted first.
    // - Any additional should be sorted according to the extension type (i.e.,
    //   in this case last, since the theme declared it).
    $this->assertEqual($registry['test_theme_new_template_preprocess_include']['preprocess'], array(
      'template_preprocess',
      'template_preprocess_test_theme_new_template_preprocess_include',
      'theme_test_preprocess',
      'test_theme_preprocess',
      'test_theme_preprocess_test_theme_new_template_preprocess_include',
    ));
    $this->assertEqual($registry['test_theme_new_template_preprocess_include']['process'], array(
      'theme_test_process',
      'test_theme_process',
    ));
  }

  /**
   * Tests extension of existing hooks by a theme.
   */
  function testThemeHookExtensions() {
    $registry = $this->getRegistry('test_theme')->get();
    $path = drupal_get_path('theme', 'test_theme');

    // Verify that the non-existing theme hook is not contained.
    $this->assertNotIsset($registry, 'non_existing_theme_hook');

    // Verify the additional preprocess function for a template.
    $this->assertInArray($registry['link']['preprocess'], 'test_theme_preprocess_link');
    $this->assertEqual($registry['link']['preprocess'], array(
      'test_theme_preprocess_link',
    ));
    $this->assertNotIsset($registry['link'], 'process');

    // Verify the additional preprocess function for a template.
    // Since this is the only and final theme, that preprocess should be last.
    $this->assertInArray($registry['html']['preprocess'], 'test_theme_preprocess_html');
    $this->assertEqual($registry['html']['preprocess'], array(
      // template_preprocess()
      'template_preprocess',
      // template_preprocess_HOOK(), if any. (declarative)
      'template_preprocess_html',
      // hook_preprocess().
      'theme_test_preprocess',
      // hook_preprocess_HOOK(), if any. (declarative)
      'theme_test_preprocess_html',
      // THEME_preprocess().
      'test_theme_preprocess',
      // THEME_preprocess_HOOK(), if any. (declarative)
      'test_theme_preprocess_html',
    ));
    $this->assertEqual($registry['html']['process'], array(
      // template_process_HOOK(), if any. (declarative)
      'template_process_html',
      // hook_process().
      'theme_test_process',
      // THEME_process().
      'test_theme_process',
    ));

    // Verify that a theme is able to replace a function with a template.
    $this->assertNotIsset($registry['theme_test_function_replace_template'], 'function');
    $this->assertEqual($registry['theme_test_function_replace_template']['theme path'], $path);
    $this->assertEqual($registry['theme_test_function_replace_template']['path'], $path . '/templates');
    $this->assertEqual($registry['theme_test_function_replace_template']['template'], 'theme_test_function_replace_template');
    $this->assertEqual($registry['theme_test_function_replace_template']['preprocess'], array(
      'template_preprocess',
      'template_preprocess_theme_test_function_replace_template',
      'theme_test_preprocess',
      'theme_test_preprocess_theme_test_function_replace_template',
      'test_theme_preprocess',
      'test_theme_preprocess_theme_test_function_replace_template',
    ));
    $this->assertEqual($registry['theme_test_function_replace_template']['process'], array(
      'theme_test_process',
      'test_theme_process',
    ));
  }

  /**
   * Tests that all registered theme hooks contain a 'theme path'.
   *
   * Separated from all other tests, since the 'theme path' is architecturally
   * broken right now.
   */
  function testHookThemePath() {
    // drupal_find_theme_functions() and drupal_find_theme_templates() expect
    // all theme hooks to have a 'theme path'.
    $registry = $this->getRegistry('test_theme')->get();
    foreach ($registry as $hook => $info) {
      $this->assertIsset($info, 'theme path', '@value key found for hook @hook.', array(
        '@hook' => $hook,
      ));
    }
  }

  protected function assertIsset($array, $key, $message = NULL, $args = array()) {
    $message = isset($message) ? $message : 'Key @value found.';
    return $this->assert(isset($array[$key]), format_string($message, $args + array(
      '@value' => var_export($key, TRUE),
    )));
  }

  protected function assertNotIsset($array, $key, $message = NULL, $args = array()) {
    $message = isset($message) ? $message : 'Key @value not found.';
    return $this->assert(!isset($array[$key]), format_string($message, $args + array(
      '@value' => var_export($key, TRUE),
    )));
  }

  protected function assertInArray($array, $value, $message = NULL, $args = array()) {
    $message = isset($message) ? $message : 'Value @value found.';
    return $this->assert(in_array($value, $array, TRUE), format_string($message, $args + array(
      '@value' => var_export($value, TRUE),
    )));
  }

}
