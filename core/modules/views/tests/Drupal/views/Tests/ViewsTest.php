<?php

/**
 * @file
 * Contains \Drupal\views\Tests\ViewsTest.
 */

namespace Drupal\views\Tests;

use Drupal\Tests\UnitTestCase;
use Drupal\views\Views;
use Drupal\views\Entity\View;
use Drupal\views\ViewExecutableFactory;
use Drupal\Core\DependencyInjection\ContainerBuilder;

class ViewsTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'Views test',
      'description' => 'Tests the Drupal\views\Views class.',
      'group' => 'Views',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();
    $user = $this->getMock('Drupal\Core\Session\AccountInterface');
    $container->set('views.executable', new ViewExecutableFactory($user));

    $entity_manager = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $container->set('entity.manager', $entity_manager);

    $entity_type = $this->getMock('Drupal\Core\Entity\EntityTypeInterface');
    $entity_type->expects($this->any())
      ->method('getKey')
      ->with('id')
      ->will($this->returnValue('id'));

    $entity_manager->expects($this->any())
      ->method('getDefinition')
      ->with($this->equalTo('view'))
      ->will($this->returnValue($entity_type));

    \Drupal::setContainer($container);

    $this->view = new View(array('id' => 'test_view'), 'view');

    $view_storage_controller = $this->getMockBuilder('Drupal\views\ViewStorageController')
      ->disableOriginalConstructor()
      ->getMock();
    $view_storage_controller->expects($this->once())
      ->method('load')
      ->with('test_view')
      ->will($this->returnValue($this->view));

    $entity_manager->expects($this->once())
      ->method('getStorageController')
      ->with('view')
      ->will($this->returnValue($view_storage_controller));

  }

  /**
   * Tests the getView() method.
   */
  public function testGetView() {
    $executable = Views::getView('test_view');
    $this->assertInstanceOf('Drupal\views\ViewExecutable', $executable);
    $this->assertEquals($this->view->id(), $executable->storage->id());
    $this->assertEquals(spl_object_hash($this->view), spl_object_hash($executable->storage));
  }

}
