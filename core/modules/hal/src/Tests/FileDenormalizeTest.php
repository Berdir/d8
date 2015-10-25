<?php

/**
 * @file
 * Contains \Drupal\hal\Tests\FileDenormalizeTest.
 */

namespace Drupal\hal\Tests;

use Drupal\file\Entity\File;
use Drupal\simpletest\WebTestBase;

/**
 * Tests that file entities can be denormalized in HAL.
 *
 * @group hal
 * @see \Drupal\hal\Normalizer\FileEntityNormalizer
 */
class FileDenormalizeTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('hal', 'file', 'node');

  /**
   * Tests file entity denormalization.
   */
  public function testFileDenormalize() {
    $file_params = array(
      'filename' => 'test_1.txt',
      'uri' => 'public://test_1.txt',
      'filemime' => 'text/plain',
      'status' => FILE_STATUS_PERMANENT,
    );
    // Create a new file entity.
    $file = entity_create('file', $file_params);
    file_put_contents($file->getFileUri(), 'hello world');
    $file->save();
    $data = file_get_contents($file_params['uri']);
    $data = base64_encode($data);

    $serializer = \Drupal::service('serializer');
    $normalized_data = $serializer->normalize($file, 'hal_json');
    // Adding data to the entity.
    $normalized_data['data'][0]['value'] = $data;
    // Use 'patch' to avoid trying to recreate the file.
    $denormalized = $serializer->denormalize($normalized_data, 'Drupal\file\Entity\File', 'hal_json', array('request_method' => 'patch'));
    $this->assertTrue($denormalized instanceof File, 'A File instance was created.');

    $this->assertIdentical('public://' . $file->getFilename(), $denormalized->getFileUri());
    $this->assertTrue(file_exists($denormalized->getFileUri()), 'The temporary file was found.');

    $this->assertIdentical($file->uuid(), $denormalized->uuid(), 'The expected UUID was found');
    $this->assertIdentical($file->getMimeType(), $denormalized->getMimeType(), 'The expected mime type was found.');
    $this->assertIdentical($file->getFilename(), $denormalized->getFilename(), 'The expected filename was found.');
    $this->assertTrue($denormalized->isPermanent(), 'The file has a permanent status.');

  }

}
