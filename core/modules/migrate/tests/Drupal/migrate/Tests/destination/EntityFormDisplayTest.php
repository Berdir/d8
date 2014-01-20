<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\destination\EntityFormDisplayTest.
 */

namespace Drupal\migrate\Tests\destination;

use Drupal\migrate\Plugin\migrate\destination\EntityFormDisplay;
use Drupal\migrate\Row;
use Drupal\migrate\Tests\MigrateTestCase;

/**
 * Tests the entity display destination plugin.
 *
 * @group Drupal
 * @group migrate
 */
class EntityFormDisplayTest extends MigrateTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Entity display destination plugin',
      'description' => 'Tests the entity display destination plugin.',
      'group' => 'Migrate',
    );
  }

  /**
   * Tests the entity display import method.
   */
  public function testImport() {
    $values = array(
      'entity_type' => 'entity_type_test',
      'bundle' => 'bundle_test',
      'form_mode' => 'form_mode_test',
      'field_name' => 'field_name_test',
      'options' => array('test setting'),
    );
    $row = new Row(array(), array());
    foreach ($values as $key => $value) {
      $row->setDestinationProperty($key, $value);
    }
    $entity = $this->getMock('Drupal\Core\Entity\Display\EntityFormDisplayInterface');
    $entity->expects($this->once())
      ->method('setComponent')
      ->with('field_name_test', array('test setting'))
      ->will($this->returnSelf());
    $entity->expects($this->once())
      ->method('save')
      ->with();
    $entity->expects($this->once())
      ->method('id')
      ->with()
      ->will($this->returnValue('testid'));
    $plugin = new TestEntityFormDisplay($entity);
    $this->assertSame($plugin->import($row), array('testid', 'field_name_test'));
    $this->assertSame($plugin->getTestValues(), array('entity_type_test', 'bundle_test', 'form_mode_test'));
  }

}

class TestEntityFormDisplay extends EntityFormDisplay {
  protected $testValues;
  function __construct($entity) {
    $this->entity = $entity;
  }
  protected function getEntity($entity_type, $bundle, $form_mode) {
    $this->testValues = func_get_args();
    return $this->entity;
  }
  public function getTestValues() {
    return $this->testValues;
  }
}
