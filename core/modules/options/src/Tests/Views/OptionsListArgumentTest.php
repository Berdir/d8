<?php

/**
 * @file
 * Contains \Drupal\options\Tests\Views\OptionsListArgumentTest.
 */

namespace Drupal\options\Tests\Views;

use Drupal\views\Views;

/**
 * Tests options list argument for views.
 *
 * @see \Drupal\options\Plugin\views\argument\NumberListField.
 * @group views
 */
class OptionsListArgumentTest extends OptionsTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_options_list_argument'];

  /**
   * Tests the options field argument.
   */
  function testViewsTestOptionsListArgument() {
    $view = Views::getView('test_options_list_argument');
    $this->executeView($view, [1]);

    $resultset = [
      ['nid' => $this->nodes[0]->nid->value],
      ['nid' => $this->nodes[1]->nid->value],
    ];

    $column_map = ['nid' => 'nid'];
    $this->assertIdenticalResultset($view, $resultset, $column_map);
  }

}
