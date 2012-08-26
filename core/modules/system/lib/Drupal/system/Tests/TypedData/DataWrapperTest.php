<?php

/**
 * @file
 * Definition of Drupal\system\Tests\TypedData\DataWrapperTest.
 */

namespace Drupal\system\Tests\TypedData;

use Drupal\simpletest\WebTestBase;

/**
 * Tests primitive data types.
 */
class DataWrapperTest extends WebTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Test data wrappers',
      'description' => 'Tests the functionality of all core data wrappers.',
      'group' => 'Typed Data API',
    );
  }

  /**
   * Tests the basics around constructing and working with data wrappers.
   */
  public function testGetAndSet() {
    // Boolean type.
    $wrapper = $this->drupalWrapData(array('type' => 'boolean'), TRUE);
    $this->assertTrue($wrapper->getValue() === TRUE, 'Boolean value was fetched.');
    $wrapper->setValue(FALSE);
    $this->assertTrue($wrapper->getValue() === FALSE, 'Boolean value was changed.');
    $this->assertTrue(is_string($wrapper->getString()), 'Boolean value was converted to string');

    // String type.
    $value = $this->randomString();
    $wrapper = $this->drupalWrapData(array('type' => 'string'), $value);
    $this->assertTrue($wrapper->getValue() === $value, 'String value was fetched.');
    $new_value = $this->randomString();
    $wrapper->setValue($new_value);
    $this->assertTrue($wrapper->getValue() === $new_value, 'String value was changed.');
    // Funky test.
    $this->assertTrue(is_string($wrapper->getString()), 'String value was converted to string');

    // Integer type.
    $value = rand();
    $wrapper = $this->drupalWrapData(array('type' => 'integer'), $value);
    $this->assertTrue($wrapper->getValue() === $value, 'Integer value was fetched.');
    $new_value = rand();
    $wrapper->setValue($new_value);
    $this->assertTrue($wrapper->getValue() === $new_value, 'Integer value was changed.');
    $this->assertTrue(is_string($wrapper->getString()), 'Integer value was converted to string');

    // Decimal type.
    $value = 123.45;
    $wrapper = $this->drupalWrapData(array('type' => 'decimal'), $value);
    $this->assertTrue($wrapper->getValue() === $value, 'Decimal value was fetched.');
    $new_value = 678.90;
    $wrapper->setValue($new_value);
    $this->assertTrue($wrapper->getValue() === $new_value, 'Decimal value was changed.');
    $this->assertTrue(is_string($wrapper->getString()), 'Decimal value was converted to string');

    // Date type.
    // TODO

    // Duration type.
    // TODO

    // Generate some files that will be used to test the URI and the binary
    // data types.
    $files = $this->drupalGetTestFiles('image');

    // URI type.
    $wrapper = $this->drupalWrapData(array('type' => 'uri'), $files[0]->uri);
    $this->assertTrue($wrapper->getValue() === $files[0]->uri, 'URI value was fetched.');
    $wrapper->setValue($files[1]->uri);
    $this->assertTrue($wrapper->getValue() === $files[1]->uri, 'URI value was changed.');
    $this->assertTrue(is_string($wrapper->getString()), 'URI value was converted to string');

    // Binary type.
    $wrapper = $this->drupalWrapData(array('type' => 'binary'), $files[0]->uri);
    $this->assertTrue(is_resource($wrapper->getValue()), 'Binary value was fetched.');
    $wrapper->setValue($files[1]->uri);
    // TODO: We don't really know if the binary stream actually changed. We only
    // know that it didn't dissappear when pointing to a new URI. Fix this.
    $this->assertTrue(is_resource($wrapper->getValue()), 'Binary value was changed.');
    $this->assertTrue(is_string($wrapper->getString()), 'Binary value was converted to string');
  }

  public function testEmptyWrappers() {
    // TODO
  }

  public function testValidation() {
    // TODO
  }
}
