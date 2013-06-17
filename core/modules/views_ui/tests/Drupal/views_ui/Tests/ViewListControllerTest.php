<?php

/**
 * @file
 * Contains \Drupal\views_ui\Tests\ViewListControllerTest
 */

namespace Drupal\views_ui\Tests {


use Drupal\Tests\UnitTestCase;
use Drupal\views\Plugin\Core\Entity\View;
use Drupal\views_ui\ViewListController;

class ViewListControllerTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'Views List Controller Unit Test',
      'description' => 'Unit tests the views list controller',
      'group' => 'Views UI',
    );
  }

  /**
   * Tests the listing of displays on a views list.
   *
   * @see \Drupal\views_ui\ViewListController::getDisplaysList().
   */
  public function testBuildRowEntityList() {
    $storage_controller = $this->getMockBuilder('Drupal\views\ViewStorageController')
      ->disableOriginalConstructor()
      ->getMock();
    $entity_info = array();
    $display_manager = $this->getMockBuilder('\Drupal\views\Plugin\ViewsPluginManager')
      ->disableOriginalConstructor()
      ->getMock();
    $display_manager->expects($this->any())
      ->method('getDefinition')
      ->will($this->returnValueMap(array(
        array(
          'default',
          array(
            'id' => 'default',
            'title' => 'Master',
            'theme' => 'views_view',
            'no_ui' => TRUE,
          )
        ),
        array(
          'page',
          array(
            'id' => 'page',
            'title' => 'Page',
            'uses_hook_menu' => TRUE,
            'uses_route' => TRUE,
            'contextual_links_locations' => array('page'),
            'theme' => 'views_view',
            'admin' => 'Page admin label',
          )
        ),
        array(
          'embed',
          array(
            'id' => 'embed',
            'title' => 'embed',
            'theme' => 'views_view',
            'admin' => 'Embed admin label',
          )
        ),
      )));

    // Setup a view list controller with a mocked buildOperations method,
    // because t() is called on there.
    $view_list_controller = $this->getMock('Drupal\views_ui\ViewListController', array('buildOperations'), array('view', $storage_controller, $entity_info, $display_manager));
    $view_list_controller->expects($this->any())
      ->method('buildOperations')
      ->will($this->returnValue(array()));

    $values = array();
    $values['display']['default']['id'] = 'default';
    $values['display']['default']['display_title'] = 'Display';
    $values['display']['default']['display_plugin'] = 'default';

    $values['display']['page_1']['id'] = 'page_1';
    $values['display']['page_1']['display_title'] = 'Page 1';
    $values['display']['page_1']['display_plugin'] = 'page';

    $values['display']['embed']['id'] = 'embed';
    $values['display']['embed']['display_title'] = 'Embedded';
    $values['display']['embed']['display_plugin'] = 'embed';

    $view = new View($values, 'view');

    $row = $view_list_controller->buildRow($view);

    $this->assertEquals(array('Embed admin label', 'Page admin label'), $row['data']['view_name']['data']['#displays'], 'Wrong displays got added to view list');
  }
}

}

// @todo Remove this once t() is converted to a service.
namespace {
  if (!function_exists('t')) {
    function t($string) {
      return $string;
    }
  }
}
