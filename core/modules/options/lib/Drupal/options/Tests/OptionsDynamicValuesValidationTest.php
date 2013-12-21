<?php

/**
 * @file
 * Definition of Drupal\options\Tests\OptionsDynamicValuesValidationTest.
 */

namespace Drupal\options\Tests;

/**
 * Tests the Options field allowed values function.
 */
class OptionsDynamicValuesValidationTest extends OptionsDynamicValuesTest {
  public static function getInfo() {
    return array(
      'name' => 'Options field dynamic values',
      'description' => 'Test the Options field allowed values function.',
      'group' => 'Field types',
    );
  }

  /**
   * Test that allowed values function gets the entity.
   */
  function testDynamicAllowedValues() {
    // Verify that validation passes against every value we had.
    foreach ($this->test as $key => $value) {
      // @todo OptionsDynamicValuesTest::setUp() creates a 'entity_test_rev'
      //   entity such that the label is NULL, which is not an allowed value,
      //   because the field instance is defined as required. Figure out what
      //   was really intended there.
      if ($key === 'label') {
        continue;
      }

      $this->entity->test_options->value = $value;
      $violations = $this->entity->test_options->validate();
      $this->assertEqual(count($violations), 0, "$key is a valid value");
    }

    // Now verify that validation does not pass against anything else.
    foreach ($this->test as $key => $value) {
      $this->entity->test_options->value = is_numeric($value) ? (100 - $value) : ('X' . $value);
      $violations = $this->entity->test_options->validate();
      $this->assertEqual(count($violations), 1, "$key is not a valid value");
    }
  }

}
