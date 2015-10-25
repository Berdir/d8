<?php
/**
 * @file
 * Contains \Drupal\hal\Tests\FileFieldNormalizeTest.
 */

namespace Drupal\hal\Tests;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the File entity normalizer.
 *
 * @group hal
 */
class FileFieldNormalizeTest extends NormalizerTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = array(
    'entity_test',
    'field',
    'image',
    'hal',
    'system',
    'file',
  );

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('file');
    $this->installSchema('file', array('file_usage'));
  }

  /**
   * Tests that file field is identical before and after de/serialization.
   */
  public function testFileFieldNormalize() {
    // Create a file.
    $file_name = $this->randomMachineName() . '.txt';
    file_put_contents("public://$file_name", $this->randomString());
    $file = entity_create('file', array(
      'uri' => "public://$file_name",
    ));
    $file->save();

    // Attach a file field to the bundle.
    FieldStorageConfig::create(array(
      'type' => 'file',
      'entity_type' => 'entity_test',
      'field_name' => 'field_file',
    ))->save();
    FieldConfig::create(array(
      'field_name' => 'field_file',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ))->save();

    // Create an entity referencing the file.
    $entity = entity_create('entity_test', array(
      'field_file' => array(
        'target_id' => $file->id(),
        'display' => 0,
        'description' => 'An attached file',
      ),
    ));

    $serialized = $this->container->get('serializer')->serialize($entity, $this->format);
    $deserialized = $this->container->get('serializer')->deserialize($serialized, 'Drupal\entity_test\Entity\EntityTest', $this->format);
    $this->assertEqual($entity->toArray()['field_file'], $deserialized->toArray()['field_file'], "File field is preserved.");
  }

  /**
   * Tests that image field is identical before and after de/serialization.
   */
  public function testImageFieldNormalize() {
    // Create a file.
    $file_name = $this->randomMachineName() . '.png';
    file_put_contents("public://$file_name", $this->randomString());
    $file = entity_create('file', array(
      'uri' => "public://$file_name",
    ));
    $file->save();

    // Attach an image field to the bundle.
    FieldStorageConfig::create(array(
      'type' => 'image',
      'entity_type' => 'entity_test',
      'field_name' => 'field_image',
    ))->save();
    FieldConfig::create(array(
      'field_name' => 'field_image',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ))->save();

    // Create an entity referencing the file.
    $entity = entity_create('entity_test', array(
      'field_image' => array(
        'target_id' => $file->id(),
        'title' => $this->randomString(),
        'alt' => $this->randomString(),
        'width' => 400,
        'height' => 300,
      ),
    ));

    $serialized = $this->container->get('serializer')->serialize($entity, $this->format);
    $deserialized = $this->container->get('serializer')->deserialize($serialized, 'Drupal\entity_test\Entity\EntityTest', $this->format);
    $this->assertEqual($entity->toArray()['field_image'], $deserialized->toArray()['field_image'], "Image field is preserved.");
  }
}
