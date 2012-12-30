<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Tests\TaxonomyTermReferenceItemTest.
 */

namespace Drupal\taxonomy\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\taxonomy\Type\TaxonomyTermReferenceItem;
use Drupal\Core\Entity\Field\FieldItemInterface;
use Drupal\Core\Entity\Field\FieldInterface;

/**
 * Tests the new entity API for the taxonomy term reference field type.
 */
class TaxonomyTermReferenceItemTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('field', 'field_sql_storage', 'taxonomy', 'entity_test', 'options');

  public static function getInfo() {
    return array(
      'name' => 'Taxonomy reference API',
      'description' => 'Tests using entity fields of the taxonomy term reference field type.',
      'group' => 'Taxonomy',
    );
  }

  public function setUp() {
    parent::setUp();
    $vocabulary = entity_create('taxonomy_vocabulary', array(
      'name' => $this->randomName(),
      'vid' => drupal_strtolower($this->randomName()),
      'langcode' => LANGUAGE_NOT_SPECIFIED,
    ));
    $vocabulary->save();
    $field = array(
      'field_name' => 'field_test_taxonomy',
      'type' => 'taxonomy_term_reference',
      'cardinality' => FIELD_CARDINALITY_UNLIMITED,
      'settings' => array(
        'allowed_values' => array(
          array(
            'vocabulary' => $vocabulary->id(),
            'parent' => 0,
          ),
        ),
      ),
    );
    field_create_field($field);
    $instance = array(
      'entity_type' => 'entity_test',
      'field_name' => 'field_test_taxonomy',
      'bundle' => 'entity_test',
      'widget' => array(
        'type' => 'options_select',
      ),
    );
    field_create_instance($instance);
    $this->term = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'vid' => $vocabulary->id(),
      'langcode' => LANGUAGE_NOT_SPECIFIED,
    ));
    $this->term->save();
  }

  /**
   * Tests using entity fields of the taxonomy term reference field type.
   */
  public function testTaxonomyTermReferenceItem() {
    $tid = $this->term->id();
    // Just being able to create the entity like this verifies a lot of code.
    $entity = entity_create('entity_test', array());
    $entity->field_test_taxonomy->tid = $this->term->tid;
    $entity->name->value = $this->randomName();
    $entity->save();

    $entity = entity_load('entity_test', $entity->id());
    $this->assertTrue($entity->field_test_taxonomy instanceof FieldInterface, 'Field implements interface.');
    $this->assertTrue($entity->field_test_taxonomy[0] instanceof FieldItemInterface, 'Field item implements interface.');
    $this->assertEqual($entity->field_test_taxonomy->tid, $this->term->tid);
    $this->assertEqual($entity->field_test_taxonomy->entity->name, $this->term->name);
    $this->assertEqual($entity->field_test_taxonomy->entity->id(), $tid);
    $this->assertEqual($entity->field_test_taxonomy->entity->uuid(), $this->term->uuid());

    // Change the name of the term via the reference.
    $new_name = $this->randomName();
    $entity->field_test_taxonomy->entity->name = $new_name;
    $entity->field_test_taxonomy->entity->save();
    // Verify it is the correct name.
    $term = entity_load('taxonomy_term', $tid);
    $this->assertEqual($term->name, $new_name);

    // Make sure the computed term reflects updates to the term id.
    $term2 = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'vid' => $this->term->vid,
      'langcode' => LANGUAGE_NOT_SPECIFIED,
    ));
    $term2->save();

    $entity->field_test_taxonomy->tid = $term2->tid;
    $this->assertEqual($entity->field_test_taxonomy->entity->id(), $term2->tid);
    $this->assertEqual($entity->field_test_taxonomy->entity->name, $term2->name);
  }

}
