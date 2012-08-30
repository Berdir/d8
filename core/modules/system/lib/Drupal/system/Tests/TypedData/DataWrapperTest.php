<?php

/**
 * @file
 * Definition of Drupal\system\Tests\TypedData\DataWrapperTest.
 */

namespace Drupal\system\Tests\TypedData;

use Drupal\simpletest\WebTestBase;
use DateTime;
use DateInterval;

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
    $wrapper->setValue(NULL);
    $this->assertNull($wrapper->getValue(), 'Boolean wrapper is null-able.');

    // String type.
    $value = $this->randomString();
    $wrapper = $this->drupalWrapData(array('type' => 'string'), $value);
    $this->assertTrue($wrapper->getValue() === $value, 'String value was fetched.');
    $new_value = $this->randomString();
    $wrapper->setValue($new_value);
    $this->assertTrue($wrapper->getValue() === $new_value, 'String value was changed.');
    // Funky test.
    $this->assertTrue(is_string($wrapper->getString()), 'String value was converted to string');
    $wrapper->setValue(NULL);
    $this->assertNull($wrapper->getValue(), 'String wrapper is null-able.');

    // Integer type.
    $value = rand();
    $wrapper = $this->drupalWrapData(array('type' => 'integer'), $value);
    $this->assertTrue($wrapper->getValue() === $value, 'Integer value was fetched.');
    $new_value = rand();
    $wrapper->setValue($new_value);
    $this->assertTrue($wrapper->getValue() === $new_value, 'Integer value was changed.');
    $this->assertTrue(is_string($wrapper->getString()), 'Integer value was converted to string');
    $wrapper->setValue(NULL);
    $this->assertNull($wrapper->getValue(), 'Integer wrapper is null-able.');

    // Decimal type.
    $value = 123.45;
    $wrapper = $this->drupalWrapData(array('type' => 'decimal'), $value);
    $this->assertTrue($wrapper->getValue() === $value, 'Decimal value was fetched.');
    $new_value = 678.90;
    $wrapper->setValue($new_value);
    $this->assertTrue($wrapper->getValue() === $new_value, 'Decimal value was changed.');
    $this->assertTrue(is_string($wrapper->getString()), 'Decimal value was converted to string');
    $wrapper->setValue(NULL);
    $this->assertNull($wrapper->getValue(), 'Decimal wrapper is null-able.');

    // Date type.
    $value = new DateTime('@' . REQUEST_TIME);
    $wrapper = $this->drupalWrapData(array('type' => 'date'), $value);
    $this->assertTrue($wrapper->getValue() === $value, 'Date value was fetched.');
    $new_value = REQUEST_TIME + 1;
    $wrapper->setValue($new_value);
    $this->assertTrue($wrapper->getValue()->getTimestamp() === $new_value, 'Date value was changed and set by timestamp.');
    $wrapper->setValue('2000-01-01');
    $this->assertTrue($wrapper->getValue()->format('Y-m-d') == '2000-01-01', 'Date value was changed and set by date string.');
    $this->assertTrue(is_string($wrapper->getString()), 'Date value was converted to string');
    $wrapper->setValue(NULL);
    $this->assertNull($wrapper->getValue(), 'Date wrapper is null-able.');

    // Duration type.
    $value = new DateInterval('PT20S');
    $wrapper = $this->drupalWrapData(array('type' => 'duration'), $value);
    $this->assertTrue($wrapper->getValue() === $value, 'Duration value was fetched.');
    $wrapper->setValue(10);
    $this->assertTrue($wrapper->getValue()->s == 10, 'Duration value was changed and set by time span in seconds.');
    $wrapper->setValue('P40D');
    $this->assertTrue($wrapper->getValue()->d == 40, 'Duration value was changed and set by duration string.');
    $this->assertTrue(is_string($wrapper->getString()), 'Duration value was converted to string');
    // Test getting the string and passing it back as value.
    $duration = $wrapper->getString();
    $wrapper->setValue($duration);
    $this->assertEqual($wrapper->getString(), $duration, 'Duration formatted as string can be used to set the duration value.');
    $wrapper->setValue(NULL);
    $this->assertNull($wrapper->getValue(), 'Duration wrapper is null-able.');

    // Generate some files that will be used to test the URI and the binary
    // data types.
    $files = $this->drupalGetTestFiles('image');

    // URI type.
    $wrapper = $this->drupalWrapData(array('type' => 'uri'), $files[0]->uri);
    $this->assertTrue($wrapper->getValue() === $files[0]->uri, 'URI value was fetched.');
    $wrapper->setValue($files[1]->uri);
    $this->assertTrue($wrapper->getValue() === $files[1]->uri, 'URI value was changed.');
    $this->assertTrue(is_string($wrapper->getString()), 'URI value was converted to string');
    $wrapper->setValue(NULL);
    $this->assertNull($wrapper->getValue(), 'URI wrapper is null-able.');

    // Binary type.
    $wrapper = $this->drupalWrapData(array('type' => 'binary'), $files[0]->uri);
    $this->assertTrue(is_resource($wrapper->getValue()), 'Binary value was fetched.');
    // Try setting by URI.
    $wrapper->setValue($files[1]->uri);
    $this->assertEqual(is_resource($wrapper->getValue()), fopen($files[1]->uri, 'r'), 'Binary value was changed.');
    $this->assertTrue(is_string($wrapper->getString()), 'Binary value was converted to string');
    // Try setting by resource.
    $wrapper->setValue(fopen($files[2]->uri, 'r'));
    $this->assertEqual(is_resource($wrapper->getValue()), fopen($files[2]->uri, 'r'), 'Binary value was changed.');
    $this->assertTrue(is_string($wrapper->getString()), 'Binary value was converted to string');
    $wrapper->setValue(NULL);
    $this->assertNull($wrapper->getValue(), 'Binary wrapper is null-able.');
  }
}
