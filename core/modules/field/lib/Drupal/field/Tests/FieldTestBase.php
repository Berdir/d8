<?php

/**
 * @file
 * Definition of Drupal\field\Tests\FieldTestBase.
 */

namespace Drupal\field\Tests;

use Drupal\Core\Entity\EntityInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Parent class for Field API tests.
 */
abstract class FieldTestBase extends WebTestBase {
  var $default_storage = 'field_sql_storage';

  /**
   * Set the default field storage backend for fields created during tests.
   */
  function setUp() {
    parent::setUp();

    // Set default storage backend.
    variable_set('field_storage_default', $this->default_storage);
  }

  /**
   * Generate random values for a field_test field.
   *
   * @param $cardinality
   *   Number of values to generate.
   * @return
   *  An array of random values, in the format expected for field values.
   */
  function _generateTestFieldValues($cardinality) {
    $values = array();
    for ($i = 0; $i < $cardinality; $i++) {
      // field_test fields treat 0 as 'empty value'.
      $values[$i]['value'] = mt_rand(1, 127);
    }
    return $values;
  }

  /**
   * Assert that a field has the expected values in an entity.
   *
   * This function only checks a single column in the field values.
   *
   * @param EntityInterface $entity
   *   The entity to test.
   * @param $field_name
   *   The name of the field to test
   * @param $langcode
   *   The language code for the values.
   * @param $expected_values
   *   The array of expected values.
   * @param $column
   *   (Optional) the name of the column to check.
   */
  function assertFieldValues(EntityInterface $entity, $field_name, $langcode, $expected_values, $column = 'value') {
    $e = clone $entity;
    field_attach_load('test_entity', array($e->ftid => $e));
    $values = isset($e->{$field_name}[$langcode]) ? $e->{$field_name}[$langcode] : array();
    $this->assertEqual(count($values), count($expected_values), 'Expected number of values were saved.');
    foreach ($expected_values as $key => $value) {
      $this->assertEqual($values[$key][$column], $value, format_string('Value @value was saved correctly.', array('@value' => $value)));
    }
  }
}
