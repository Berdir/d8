<?php

/**
 * @file
 * Definition of \Drupal\ckeditor\Tests\CKEditorLoadingTest.
 */

namespace Drupal\ckeditor\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests loading of CKEditor.
 */
class CKEditorLoadingTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('filter', 'editor', 'ckeditor', 'node');

  public static function getInfo() {
    return array(
      'name' => 'CKEditor loading',
      'description' => 'Tests loading of CKEditor.',
      'group' => 'CKEditor',
    );
  }

  function setUp() {
    parent::setUp();

    // Create text format, associate CKEditor.
    $filtered_html_format = entity_create('filter_format', array(
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'weight' => 0,
      'filters' => array(),
    ));
    $filtered_html_format->save();
    $editor = entity_create('editor', array(
      'format' => 'filtered_html',
      'editor' => 'ckeditor',
    ));
    $editor->save();

    // Create a second format without an associated editor so a drop down select
    // list is created when selecting formats.
    $full_html_format = entity_create('filter_format', array(
      'format' => 'full_html',
      'name' => 'Full HTML',
      'weight' => 1,
      'filters' => array(),
    ));
    $full_html_format->save();

    // Create node type.
    $this->drupalCreateContentType(array(
      'type' => 'article',
      'name' => 'Article',
    ));

    // Create 2 users, each with access to different text formats:
    //   - "untrusted": plain_text
    //   - "normal": plain_text, filtered_html
    $this->untrusted_user = $this->drupalCreateUser(array('create article content', 'edit any article content'));
    $this->normal_user = $this->drupalCreateUser(array('create article content', 'edit any article content', 'use text format filtered_html', 'use text format full_html'));
  }

  /**
   * Tests loading of CKEditor CSS, JS and JS settings.
   */
  function testLoading() {
    // The untrusted user:
    // - has access to 1 text format (plain_text);
    // - doesn't have access to the filtered_html text format, so: no text editor.
    $this->drupalLogin($this->untrusted_user);
    $this->drupalGet('node/add/article');
    list($settings, $editor_settings_present, $editor_js_present, $body, $format_selector) = $this->getThingsToCheck();
    $this->assertFalse($editor_settings_present, 'No Text Editor module settings.');
    $this->assertFalse($editor_js_present, 'No Text Editor JavaScript.');
    $this->assertTrue(count($body) === 1, 'A body field exists.');
    $this->assertTrue(count($format_selector) === 0, 'No text format selector exists on the page.');
    $hidden_input = $this->xpath('//input[@type="hidden" and contains(@class, "editor")]');
    $this->assertTrue(count($hidden_input) === 0, 'A single text format hidden input does not exist on the page.');
    $this->assertNoRaw(drupal_get_path('module', 'ckeditor') . '/js/ckeditor.js', 'CKEditor glue JS is absent.');

    // On pages where there would never be a text editor, CKEditor JS is absent.
    $this->drupalGet('user');
    $this->assertNoRaw(drupal_get_path('module', 'ckeditor') . '/js/ckeditor.js', 'CKEditor glue JS is absent.');

    // The normal user:
    // - has access to 2 text formats;
    // - does have access to the filtered_html text format, so: CKEditor.
    $this->drupalLogin($this->normal_user);
    $this->drupalGet('node/add/article');
    list($settings, $editor_settings_present, $editor_js_present, $body, $format_selector) = $this->getThingsToCheck();
    $ckeditor_plugin = drupal_container()->get('plugin.manager.editor')->createInstance('ckeditor');
    $editor = entity_load('editor', 'filtered_html');
    $expected = array('formats' => array('filtered_html' => array(
      'editor' => 'ckeditor',
      'editorSettings' => $ckeditor_plugin->getJSSettings($editor),
    )));
    $this->assertTrue($editor_settings_present, "Text Editor module's JavaScript settings are on the page.");
    $this->assertIdentical($expected, $settings['editor'], "Text Editor module's JavaScript settings on the page are correct.");
    $this->assertTrue($editor_js_present, 'Text Editor JavaScript is present.');
    $this->assertTrue(count($body) === 1, 'A body field exists.');
    $this->assertTrue(count($format_selector) === 1, 'A single text format selector exists on the page.');
    $specific_format_selector = $this->xpath('//select[contains(@class, "filter-list") and contains(@class, "editor") and @data-editor-for="edit-body-und-0-value"]');
    $this->assertTrue(count($specific_format_selector) === 1, 'A single text format selector exists on the page and has the "editor" class and a "data-editor-for" attribute with the correct value.');
    $this->assertTrue(isset($settings['ajaxPageState']['js']['core/modules/ckeditor/js/ckeditor.js']), 'CKEditor glue JS is present.');
    $this->assertTrue(isset($settings['ajaxPageState']['js']['core/misc/ckeditor/ckeditor.js']), 'CKEditor lib JS is present.');

    // Enable the ckeditor_test module, customize configuration. In this case,
    // there is additional CSS and JS to be loaded.
    // NOTE: the tests in CKEditorTest already ensure that changing the
    // configuration also results in modified CKEditor configuration, so we
    // don't test that here.
    module_enable(array('ckeditor_test'));
    drupal_container()->get('plugin.manager.ckeditor.plugin')->clearCachedDefinitions();
    $editor->settings['toolbar']['buttons'][0][] = 'Llama';
    $editor->settings['plugins']['internal']['link_shortcut'] = 'CTRL+K';
    $editor->save();
    $this->drupalGet('node/add/article');
    list($settings, $editor_settings_present, $editor_js_present, $body, $format_selector) = $this->getThingsToCheck();
    $expected = array('formats' => array('filtered_html' => array(
      'editor' => 'ckeditor',
      'editorSettings' => $ckeditor_plugin->getJSSettings($editor),
    )));
    $this->assertTrue($editor_settings_present, "Text Editor module's JavaScript settings are on the page.");
    $this->assertIdentical($expected, $settings['editor'], "Text Editor module's JavaScript settings on the page are correct.");
    $this->assertTrue($editor_js_present, 'Text Editor JavaScript is present.');
    $this->assertTrue(isset($settings['ajaxPageState']['js']['core/modules/ckeditor/js/ckeditor.js']), 'CKEditor glue JS is present.');
    $this->assertTrue(isset($settings['ajaxPageState']['js']['core/misc/ckeditor/ckeditor.js']), 'CKEditor lib JS is present.');
  }

  protected function getThingsToCheck() {
    $settings = $this->drupalGetSettings();
    return array(
      // JavaScript settings.
      $settings,
      // Editor.module's JS settings present.
      isset($settings['editor']),
      // Editor.module's JS present.
      isset($settings['ajaxPageState']['js']['core/modules/editor/js/editor.js']),
      // Body field.
      $this->xpath('//textarea[@id="edit-body-und-0-value"]'),
      // Format selector.
      $this->xpath('//select[contains(@class, "filter-list")]'),
    );
  }
}
