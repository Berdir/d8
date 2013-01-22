<?php

/**
 * @file
 * Contains \Drupal\views\Tests\ViewExecutableTest.
 */

namespace Drupal\views\Tests;

use Symfony\Component\HttpFoundation\Response;
use Drupal\views\ViewExecutable;
use Drupal\views\DisplayBag;
use Drupal\views\Plugin\views\display\DefaultDisplay;
use Drupal\views\Plugin\views\display\Page;
use Drupal\views\Plugin\views\style\DefaultStyle;
use Drupal\views\Plugin\views\query\Sql;

/**
 * Tests the ViewExecutable class.
 *
 * @see Drupal\views\ViewExecutable
 */
class ViewExecutableTest extends ViewUnitTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = array('test_destroy', 'test_executable_displays');

  /**
   * Properties that should be stored in the configuration.
   *
   * @var array
   */
  protected $configProperties = array(
    'disabled',
    'name',
    'description',
    'tag',
    'base_table',
    'human_name',
    'core',
    'display',
  );

  /**
   * Properties that should be stored in the executable.
   *
   * @var array
   */
  protected $executableProperties = array(
    'storage',
    'built',
    'executed',
    'args',
    'build_info',
    'use_ajax',
    'result',
    'attachment_before',
    'attachment_after',
    'exposed_data',
    'exposed_input',
    'exposed_raw_input',
    'old_view',
    'parent_views',
  );

  public static function getInfo() {
    return array(
      'name' => 'View executable tests',
      'description' => 'Tests the ViewExecutable class.',
      'group' => 'Views'
    );
  }

  protected function setUp() {
    parent::setUp();

    $this->enableModules(array('system', 'node', 'comment', 'user', 'filter'));
  }

  /**
   * Tests the initDisplay() and initHandlers() methods.
   */
  public function testInitMethods() {
    $view = views_get_view('test_destroy');
    $view->initDisplay();

    $this->assertTrue($view->display_handler instanceof DefaultDisplay, 'Make sure a reference to the current display handler is set.');
    $this->assertTrue($view->displayHandlers->get('default') instanceof DefaultDisplay, 'Make sure a display handler is created for each display.');

    $view->destroy();
    $view->initHandlers();

    // Check for all handler types.
    $handler_types = array_keys(ViewExecutable::viewsHandlerTypes());
    foreach ($handler_types as $type) {
      // The views_test integration doesn't have relationships.
      if ($type == 'relationship') {
        continue;
      }
      $this->assertTrue(count($view->$type), format_string('Make sure a %type instance got instantiated.', array('%type' => $type)));
    }

    // initHandlers() should create display handlers automatically as well.
    $this->assertTrue($view->display_handler instanceof DefaultDisplay, 'Make sure a reference to the current display handler is set.');
    $this->assertTrue($view->displayHandlers->get('default') instanceof DefaultDisplay, 'Make sure a display handler is created for each display.');

    $view_hash = spl_object_hash($view);
    $display_hash = spl_object_hash($view->display_handler);

    // Test the initStyle() method.
    $view->initStyle();
    $this->assertTrue($view->style_plugin instanceof DefaultStyle, 'Make sure a reference to the style plugin is set.');
    // Test the plugin has been inited and view have references to the view and
    // display handler.
    $this->assertEqual(spl_object_hash($view->style_plugin->view), $view_hash);
    $this->assertEqual(spl_object_hash($view->style_plugin->displayHandler), $display_hash);

    // Test the initQuery method().
    $view->initQuery();
    $this->assertTrue($view->query instanceof Sql, 'Make sure a reference to the query is set');
    $this->assertEqual(spl_object_hash($view->query->view), $view_hash);
    $this->assertEqual(spl_object_hash($view->query->displayHandler), $display_hash);
  }

  /**
   * Tests the generation of the executable object.
   */
  public function testConstructing() {
    views_get_view('test_destroy');
  }

  /**
   * Tests the accessing of values on the object.
   */
  public function testProperties() {
    $view = views_get_view('test_destroy');
    foreach ($this->executableProperties as $property) {
      $this->assertTrue(isset($view->{$property}));
    }
  }

  /**
   * Tests the display related methods and properties.
   */
  public function testDisplays() {
    $view = views_get_view('test_executable_displays');

    // Tests Drupal\views\ViewExecutable::initDisplay().
    $view->initDisplay();
    $this->assertTrue($view->displayHandlers instanceof DisplayBag, 'The displayHandlers property has the right class.');
    // Tests the classes of the instances.
    $this->assertTrue($view->displayHandlers->get('default') instanceof DefaultDisplay);
    $this->assertTrue($view->displayHandlers->get('page_1') instanceof Page);
    $this->assertTrue($view->displayHandlers->get('page_2') instanceof Page);

    // After initializing the default display is the current used display.
    $this->assertEqual($view->current_display, 'default');
    $this->assertEqual(spl_object_hash($view->display_handler), spl_object_hash($view->displayHandlers->get('default')));

    // All handlers should have a reference to the default display.
    $this->assertEqual(spl_object_hash($view->displayHandlers->get('page_1')->default_display), spl_object_hash($view->displayHandlers->get('default')));
    $this->assertEqual(spl_object_hash($view->displayHandlers->get('page_2')->default_display), spl_object_hash($view->displayHandlers->get('default')));

    // Tests Drupal\views\ViewExecutable::setDisplay().
    $view->setDisplay();
    $this->assertEqual($view->current_display, 'default', 'If setDisplay is called with no parameter the default display should be used.');
    $this->assertEqual(spl_object_hash($view->display_handler), spl_object_hash($view->displayHandlers->get('default')));

    // Set two different valid displays.
    $view->setDisplay('page_1');
    $this->assertEqual($view->current_display, 'page_1', 'If setDisplay is called with a valid display id the appropriate display should be used.');
    $this->assertEqual(spl_object_hash($view->display_handler), spl_object_hash($view->displayHandlers->get('page_1')));

    $view->setDisplay('page_2');
    $this->assertEqual($view->current_display, 'page_2', 'If setDisplay is called with a valid display id the appropriate display should be used.');
    $this->assertEqual(spl_object_hash($view->display_handler), spl_object_hash($view->displayHandlers->get('page_2')));

    $view->setDisplay('invalid');
    $this->assertEqual($view->current_display, 'default', 'If setDisplay is called with an invalid display id the default display should be used.');
    $this->assertEqual(spl_object_hash($view->display_handler), spl_object_hash($view->displayHandlers->get('default')));
  }

  /**
   * Tests the setting/getting of properties.
   */
  public function testPropertyMethods() {
    $view = views_get_view('test_executable_displays');

    // Test the setUseAJAX() method.
    $this->assertFalse($view->use_ajax);
    $view->setUseAJAX(TRUE);
    $this->assertTrue($view->use_ajax);

    $view->setDisplay();
    // There should be no pager set initially.
    $this->assertNull($view->usePager());

    // Add a pager, initialize, and test.
    $view->displayHandlers->get('default')->overrideOption('pager', array(
      'type' => 'full',
      'options' => array('items_per_page' => 10),
    ));
    $view->initPager();
    $this->assertTrue($view->usePager());

    // Test setting and getting the offset.
    $rand = rand();
    $view->setOffset($rand);
    $this->assertEqual($view->getOffset(), $rand);

    // Test the getBaseTable() method.
    $expected = array(
      'views_test_data' => TRUE,
      '#global' => TRUE,
    );
    $this->assertIdentical($view->getBaseTables(), $expected);

    // Test response methods.
    $this->assertTrue($view->getResponse() instanceof Response, 'New response object returned.');
    $new_response = new Response();
    $view->setResponse($new_response);
    $this->assertIdentical(spl_object_hash($view->getResponse()), spl_object_hash($new_response), 'New response object correctly set.');

    // Test the generateItemId() method.
    $test_ids = drupal_map_assoc(array('test', 'test_1'));
    $this->assertEqual($view->generateItemId('new', $test_ids), 'new');
    $this->assertEqual($view->generateItemId('test', $test_ids), 'test_2');

    // Test the getPath() method.
    $path = $this->randomName();
    $view->displayHandlers->get('page_1')->overrideOption('path', $path);
    $view->setDisplay('page_1');
    $this->assertEqual($view->getPath(), $path);
    // Test the override_path property override.
    $override_path = $this->randomName();
    $view->override_path = $override_path;
    $this->assertEqual($view->getPath(), $override_path);

    // Test the getUrl method().
    $url = $this->randomString();
    $this->assertEqual($view->getUrl(NULL, $url), $url);
    // Test with arguments.
    $arg1 = $this->randomString();
    $arg2 = rand();
    $this->assertEqual($view->getUrl(array($arg1, $arg2), $url), "$url/$arg1/$arg2");
    // Test the override_url property override.
    $override_url = $this->randomString();
    $view->override_url = $override_url;
    $this->assertEqual($view->getUrl(NULL, $url), $override_url);

    // Test the title methods.
    $title = $this->randomString();
    $view->setTitle($title);
    $this->assertEqual($view->getTitle(), $title);
  }

  /**
   * Tests the deconstructor to be sure that necessary objects are removed.
   */
  public function testDestroy() {
    $view = views_get_view('test_destroy');

    $view->preview();
    $view->destroy();

    $this->assertViewDestroy($view);
  }

  /**
   * Asserts that expected view properties have been unset by destroy().
   *
   * @param \Drupal\views\ViewExecutable $view
   */
  protected function assertViewDestroy($view) {
    $this->assertFalse(isset($view->displayHandlers), 'Make sure all displays are destroyed');
    $this->assertFalse(isset($view->filter), 'Make sure all filter handlers are destroyed');
    $this->assertFalse(isset($view->field), 'Make sure all field handlers are destroyed');
    $this->assertFalse(isset($view->argument), 'Make sure all argument handlers are destroyed');
    $this->assertFalse(isset($view->relationship), 'Make sure all relationship handlers are destroyed');
    $this->assertFalse(isset($view->sort), 'Make sure all sort handlers are destroyed');
    $this->assertFalse(isset($view->area), 'Make sure all area handlers are destroyed');

    $keys = array('current_display', 'display_handler', 'field', 'argument', 'filter', 'sort', 'relationship', 'header', 'footer', 'empty', 'query', 'result', 'inited', 'style_plugin', 'plugin_name', 'exposed_data', 'exposed_input', 'many_to_one_tables');
    foreach ($keys as $key) {
      $this->assertFalse(isset($view->{$key}), $key);
    }
    $this->assertEqual($view->built, FALSE);
    $this->assertEqual($view->executed, FALSE);
    $this->assertEqual($view->build_info, array());
    $this->assertEqual($view->attachment_before, '');
    $this->assertEqual($view->attachment_after, '');
  }

  /**
   * Tests ViewExecutable::viewsHandlerTypes().
   */
  public function testViewsHandlerTypes() {
    $types = ViewExecutable::viewsHandlerTypes();
    foreach (array('field', 'filter', 'argument', 'sort', 'header', 'footer', 'empty') as $type) {
      $this->assertTrue(isset($types[$type]));
      // @todo The key on the display should be footers, headers and empties
      //   or something similar instead of the singular, but so long check for
      //   this special case.
      if (isset($types[$type]['type']) && $types[$type]['type'] == 'area') {
        $this->assertEqual($types[$type]['plural'], $type);
      }
      else {
        $this->assertEqual($types[$type]['plural'], $type . 's');
      }
    }
  }

  /**
   * Tests the validation of display handlers.
   */
  public function testValidate() {
    $view = views_get_view('test_executable_displays');
    $view->setDisplay('page_1');

    $validate = $view->validate();

    // Validating a view shouldn't change the active display.
    $this->assertEqual('page_1', $view->current_display, "The display should be constant while validating");

    $count = 0;
    foreach ($view->displayHandlers as $id => $display) {
      $match = function($value) use ($display) {
        return strpos($value, $display->display['display_title']) !== false;
      };
      $this->assertTrue(array_filter($validate, $match), format_string('Error message found for @id display', array('@id' => $id)));
      $count++;
    }

    $this->assertEqual(count($view->displayHandlers), $count, 'Error messages from all handlers merged.');

    // Test that a deleted display is not included.
    $view->displayHandlers->get('default')->deleted = TRUE;
    $validate_deleted = $view->validate();

    $this->assertNotEqual(count($validate), count($validate_deleted));
    // The first item was the default validation error originally.
    $this->assertNotIdentical($validate[0], $validate_deleted[0], 'Master display has not been validated.');
  }

}
