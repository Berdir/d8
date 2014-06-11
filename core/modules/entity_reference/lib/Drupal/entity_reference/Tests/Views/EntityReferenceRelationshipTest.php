<?php

/**
 * @file
 * Contains \Drupal\entity_reference\Tests\Views\EntityReferenceRelationshipTest.
 */

namespace Drupal\entity_reference\Tests\Views;

use Drupal\views\Tests\ViewTestBase;
use Drupal\views\Tests\ViewTestData;

/**
 * Defines a test for the entity_reference views relationship.
 *
 * @see entity_reference_field_views_data
 */
class EntityReferenceRelationshipTest extends ViewTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = array('test_entity_reference_view');

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('field', 'entity_test', 'options', 'entity_reference', 'views', 'entity_reference_test_views');

  /**
   * The entity_test entities used by the test.
   *
   * @var array
   */
  protected $entities = array();

  public static function getInfo() {
    return array(
      'name' => 'Entity Reference: Relationship handler',
      'description' => 'Tests entity reference relationship handler.',
      'group' => 'Views module integration',
    );
  }

  protected function setUp() {
    parent::setUp();

    ViewTestData::importTestViews(get_class($this), array('entity_reference_test_views'));

    $field = array(
      'translatable' => FALSE,
      'entity_types' => array(),
      'settings' => array(
        'target_type' => 'entity_test',
      ),
      'field_name' => 'field_test',
      'type' => 'entity_reference',
      'cardinality' => FIELD_CARDINALITY_UNLIMITED,
    );

    field_create_field($field);

    $instance = array(
      'entity_type' => 'entity_test',
      'field_name' => 'field_test',
      'bundle' => 'entity_test',
      'widget' => array(
        'type' => 'options_select',
      ),
      'settings' => array(
        'handler' => 'default',
        'handler_settings' => array(),
      ),
    );
    field_create_instance($instance);

    // Create some test entities which link each other.
    $storage_controller= $this->container->get('plugin.manager.entity')->getStorageController('entity_test');
    $entity_0 = $storage_controller->create(array());
    // @todo Add a value in order to avoid: "Column 'field_test_target_id'
    // cannot be null".
    $entity_0->field_test->target_id = 1000;
    $entity_0->save();
    $this->entities[$entity_0->id()] = $entity_0;

    $entity = $storage_controller->create(array());
    $entity->field_test->target_id = $entity_0->id();
    $entity->save();
    $this->entities[$entity->id()] = $entity;

    $entity = $storage_controller->create(array('field_test' => $entity->id()));
    $entity->field_test->target_id = $entity_0->id();
    $entity->save();
    $this->entities[$entity->id()] = $entity;
  }

  /**
   * Tests using the views relationship.
   */
  public function testRelationship() {
    // Check just the generated views data.
    $views_data_field_test = $this->container->get('views.views_data')->get('field_data_field_test');
    $this->assertEqual($views_data_field_test['field_test']['relationship']['id'], 'standard');
    $this->assertEqual($views_data_field_test['field_test']['relationship']['base'], 'entity_test');
    $this->assertEqual($views_data_field_test['field_test']['relationship']['base field'], 'id');
    $this->assertEqual($views_data_field_test['field_test']['relationship']['relationship field'], 'field_test_target_id');

    // Check the backwards reference.
    $views_data_entity_test = $this->container->get('views.views_data')->get('entity_test');
    $this->assertEqual($views_data_entity_test['reverse_field_test']['relationship']['id'], 'entity_reverse');
    $this->assertEqual($views_data_entity_test['reverse_field_test']['relationship']['base'], 'entity_test');
    $this->assertEqual($views_data_entity_test['reverse_field_test']['relationship']['base field'], 'id');
    $this->assertEqual($views_data_entity_test['reverse_field_test']['relationship']['field table'], 'field_data_field_test');
    $this->assertEqual($views_data_entity_test['reverse_field_test']['relationship']['field field'], 'field_test_target_id');


    // Check an actually test view.
    $view = views_get_view('test_entity_reference_view');
    $this->executeView($view, 'default');
    $view->initStyle();

    foreach (array_keys($view->result) as $index) {
      // Just check that the actual ID of the entity is the expected one.
      $this->assertEqual($view->style_plugin->get_field($index, 'id'), $this->entities[$index + 1]->id());
      // Test the foward relationsnip.
      // The second and third entity refer to the first one.
      $this->assertEqual($view->style_plugin->get_field($index, 'id_1'), $index == 0 ? 0 : 1);
    }

    $view->destroy();
    $this->executeView($view, 'embed_1');
    $view->initStyle();

    foreach (array_keys($view->result) as $index) {
      debug($view->style_plugin->get_field($index, 'id'));
      debug($view->style_plugin->get_field($index, 'id_1'));
//      $this->assertEqual($view->style_plugin->get_field($index, 'id'), $this->entities[$index + 1]->id());
//       The second and third entity refer to the first one.
//      $this->assertEqual($view->style_plugin->get_field($index, 'id_1'), $index == 0 ? 0 : 1);
    }
  }

}
