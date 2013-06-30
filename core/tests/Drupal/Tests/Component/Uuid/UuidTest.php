<?php

/**
 * @file
 * Contains \Drupal\Tests\Component\Uuid\UuidTest.
 */

namespace Drupal\Tests\Component\Uuid;

use Drupal\Tests\UnitTestCase;
use Drupal\Core\CoreServiceProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests the Drupal\Component\Uuid\Uuid class.
 *
 * @group UUID
 */
class UuidTest extends UnitTestCase {

  /**
   * An array of uuid classes which can be tested by the current environment.
   *
   * @var array
   */
  protected $uuidInstances = array();

  public static function getInfo() {
    return array(
      'name' => 'UUID handling',
      'description' => "Test the handling of Universally Unique Identifiers (UUIDs).",
      'group' => 'UUID',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // Initiate the generator. We use the CoreServiceProvider to save repeating the
    // logic.
    $container = new ContainerBuilder();
    $class = CoreServiceProvider::registerUuid($container);
    $this->uuidInstances[] = new $class();

    // Add additional uuid implementations when available.
    if ($class != 'Drupal\Component\Uuid\Php') {
      $this->uuidInstances[] = new \Drupal\Component\Uuid\Php();
      // If we are on windows add the com implementation as well.
      if ($class != 'Drupal\Component\Uuid\Com' && function_exists('com_create_guid')) {
        $this->uuidInstances[] = new \Drupal\Component\Uuid\Com();
      }
    }
  }

  /**
   * Tests generating a UUID.
   */
  public function testGenerateUuid() {
    foreach ($this->uuidInstances as $instance) {
      $uuid = $instance->generate();
      $this->assertTrue((bool) \Drupal\Component\Uuid\Uuid::isValid($uuid));
    }
  }

  /**
   * Tests that generated UUIDs are unique.
   */
  public function testUuidIsUnique() {
    foreach ($this->uuidInstances as $instance) {
      $uuid1 = $instance->generate();
      $uuid2 = $instance->generate();
      $this->assertNotEquals($uuid1, $uuid2);
    }
  }

  /**
   * Tests UUID validation.
   */
  public function testUuidValidation() {
    // These valid UUIDs.
    $uuid_fqdn = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    $uuid_min = '00000000-0000-0000-0000-000000000000';
    $uuid_max = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

    foreach ($this->uuidInstances as $instance) {
      $this->assertTrue((bool) \Drupal\Component\Uuid\Uuid::isValid($uuid_fqdn));
      $this->assertTrue((bool) \Drupal\Component\Uuid\Uuid::isValid($uuid_min));
      $this->assertTrue((bool) \Drupal\Component\Uuid\Uuid::isValid($uuid_max));
    }

    // These are invalid UUIDs.
    $invalid_format = '0ab26e6b-f074-4e44-9da-601205fa0e976';
    $invalid_length = '0ab26e6b-f074-4e44-9daf-1205fa0e9761f';

    foreach ($this->uuidInstances as $instance) {
      $this->assertFalse((bool) \Drupal\Component\Uuid\Uuid::isValid($invalid_format));
      $this->assertFalse((bool) \Drupal\Component\Uuid\Uuid::isValid($invalid_length));
    }
  }

}
