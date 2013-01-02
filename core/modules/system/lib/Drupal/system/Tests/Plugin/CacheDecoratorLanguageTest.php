<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Plugin\CacheDecoratorLanguageTest.
 */

namespace Drupal\system\Tests\Plugin;

use Exception;
use Drupal\simpletest\WebTestBase;
use Drupal\Core\Language\Language;

/**
 * Tests that the AlterDecorator fires and respects the alter hook.
 */
class CacheDecoratorLanguageTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('plugin_test', 'locale', 'language');

  /**
   * Stores a plugin manager which uses the AlterDecorator.
   *
   * @var Drupal\plugin_test\Plugin\AlterDecoratorTestPluginManager;
   */
  protected $alterTestPluginManager;

  public static function getInfo() {
    return array(
      'name' => 'CacheDecoratorLanguage',
      'description' => 'Tests that the CacheDecorator stores definitions by language appropriately.',
      'group' => 'Plugin API',
    );
  }

  public function setUp() {
    // Manually setup German as an additional language.
    parent::setUp();
    $this->languages = array('de');
    require_once DRUPAL_ROOT .'/core/includes/language.inc';
    $admin_user = $this->drupalCreateUser(array('administer languages', 'access administration pages', 'view the administration theme'));
    $this->drupalLogin($admin_user);
    $this->drupalPost('admin/config/regional/language/add', array('predefined_langcode' => 'de'), t('Add language'));
    // Add a default locale storage for all these tests.
    $this->storage = locale_storage();

    // Populate sample definitions.
    $this->mockBlockExpectedDefinitions = array(
      'user_login' => array(
        'label' => 'User login',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\mock_block\MockUserLoginBlock',
      ),
      'menu:main_menu' => array(
        'label' => 'Main menu',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\mock_block\MockMenuBlock',
      ),
      'menu:navigation' => array(
        'label' => 'Navigation',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\mock_block\MockMenuBlock',
      ),
      'layout' => array(
        'label' => 'Layout',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\mock_block\MockLayoutBlock',
      ),
      'layout:foo' => array(
        'label' => 'Layout Foo',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\mock_block\MockLayoutBlock',
      ),
    );
  }

  /**
   * Check the translations of the cached plugin definitions.
   */
  public function testCacheDecoratorLanguage() {
    $languages = $this->languages;
    // Visit our destination first to prime any relevant strings.
    $this->drupalGet('plugin_definition_test');
    // Check for the expected block labels on the page.
    $custom_strings = array();
    foreach ($this->mockBlockExpectedDefinitions as $plugin_id => $definition) {
      $this->assertText($definition['label']);
      $custom_strings[$definition['label']] = 'de ' . $definition['label'];
    }
    variable_set('locale_custom_strings_de', array('' => $custom_strings));
    foreach ($languages as $langcode) {
      $url = $langcode . '/plugin_definition_test';
      // Foreach language visit the language specific version of the page again.
      $this->drupalGet($url);
      $cache = cache()->get('mock_block:' . $langcode);
      debug($cache);
      foreach ($this->mockBlockExpectedDefinitions as $plugin_id => $definition) {
        // Find our provided translations.
        $label = $langcode . ' ' . $definition['label'];
        $this->assertText($label);
      }
    }
  }

}
