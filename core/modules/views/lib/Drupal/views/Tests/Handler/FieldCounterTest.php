<?php

/**
 * @file
 * Definition of Drupal\views\Tests\Handler\FieldCounterTest.
 */

namespace Drupal\views\Tests\Handler;

use Drupal\views\Tests\ViewUnitTestBase;

/**
 * Tests the Drupal\views\Plugin\views\field\Counter handler.
 */
class FieldCounterTest extends ViewUnitTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = array('test_view');

  public static function getInfo() {
    return array(
      'name' => 'Field: Counter',
      'description' => 'Tests the Drupal\views\Plugin\views\field\Counter handler.',
      'group' => 'Views Handlers',
    );
  }

  protected function setUp() {
    parent::setUp();

    $this->enableModules(array('user'));
  }

  function testSimple() {
    $view = views_get_view('test_view');
    $view->setDisplay();
    $view->displayHandlers->get('default')->overrideOption('fields', array(
      'counter' => array(
        'id' => 'counter',
        'table' => 'views',
        'field' => 'counter',
        'relationship' => 'none',
      ),
      'name' => array(
        'id' => 'name',
        'table' => 'views_test_data',
        'field' => 'name',
        'relationship' => 'none',
      ),
    ));
    $view->preview();

    $counter = $view->style_plugin->get_field(0, 'counter');
    $this->assertEqual($counter, 1, format_string('Make sure the expected number (@expected) patches with the rendered number (@counter)', array('@expected' => 1, '@counter' => $counter)));
    $counter = $view->style_plugin->get_field(1, 'counter');
    $this->assertEqual($counter, 2, format_string('Make sure the expected number (@expected) patches with the rendered number (@counter)', array('@expected' => 2, '@counter' => $counter)));
    $counter = $view->style_plugin->get_field(2, 'counter');
    $this->assertEqual($counter, 3, format_string('Make sure the expected number (@expected) patches with the rendered number (@counter)', array('@expected' => 3, '@counter' => $counter)));
    $view->destroy();

    $view->setDisplay();
    $rand_start = rand(5, 10);
    $view->displayHandlers->get('default')->overrideOption('fields', array(
      'counter' => array(
        'id' => 'counter',
        'table' => 'views',
        'field' => 'counter',
        'relationship' => 'none',
        'counter_start' => $rand_start
      ),
      'name' => array(
        'id' => 'name',
        'table' => 'views_test_data',
        'field' => 'name',
        'relationship' => 'none',
      ),
    ));
    $view->preview();

    $counter = $view->style_plugin->get_field(0, 'counter');
    $expected_number = 0 + $rand_start;
    $this->assertEqual($counter, $expected_number, format_string('Make sure the expected number (@expected) patches with the rendered number (@counter)', array('@expected' => $expected_number, '@counter' => $counter)));
    $counter = $view->style_plugin->get_field(1, 'counter');
    $expected_number = 1 + $rand_start;
    $this->assertEqual($counter, $expected_number, format_string('Make sure the expected number (@expected) patches with the rendered number (@counter)', array('@expected' => $expected_number, '@counter' => $counter)));
    $counter = $view->style_plugin->get_field(2, 'counter');
    $expected_number = 2 + $rand_start;
    $this->assertEqual($counter, $expected_number, format_string('Make sure the expected number (@expected) patches with the rendered number (@counter)', array('@expected' => $expected_number, '@counter' => $counter)));
  }

  // @TODO: Write tests for pager.
  function testPager() {
  }

}
