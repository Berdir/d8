<?php

/**
 * @file
 * Contains \Drupal\file\Tests\FileValidationConstraintTest.
 */

namespace Drupal\file\Tests;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;

/**
 * Tests the file validation constraint.
 *
 * @group file
 */
class FileValidationConstraintTest extends FileManagedUnitTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('entity_test', 'field');

  /**
   * Tests basic file field validations when attaching to entities.
   */
  public function testFileValidation() {
    // Create a file which should be secret.

    // Create a text and a PDF field.
    $this->createFileField('field_text_file', 'entity_test', 'entity_test', [], ['file_extensions' => 'txt']);
    $this->createFileField('field_pdf_file', 'entity_test', 'entity_test', [], ['file_extensions' => 'pdf']);

    // Create a text file try to use it in both fields.
    $file = $this->createFile('test.txt');

    $entity_test = EntityTest::create([
      'name' => 'Test',
      'uid' => 1,
      'field_text_file' => [
        0 => [
          'target_id' => $file->id(),
        ],
      ],
      'field_pdf_file' => [
        0 => [
          'target_id' => $file->id(),
        ],
      ],
    ]);
    $violations = $entity_test->validate();
    $this->assertNotEqual(0, count($violations));
  }

  /**
   * Creates a new file field.
   *
   * @param string $name
   *   The name of the new field (all lowercase), exclude the "field_" prefix.
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle that this field will be added to.
   * @param array $storage_settings
   *   A list of field storage settings that will be added to the defaults.
   * @param array $field_settings
   *   A list of instance settings that will be added to the instance defaults.
   * @param array $widget_settings
   *   A list of widget settings that will be added to the widget defaults.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  protected function createFileField($name, $entity_type, $bundle, $storage_settings = [], $field_settings = [], $widget_settings = []) {
    $field_storage = FieldStorageConfig::create([
      'entity_type' => $entity_type,
      'field_name' => $name,
      'type' => 'file',
      'settings' => $storage_settings,
      'cardinality' => !empty($storage_settings['cardinality']) ? $storage_settings['cardinality'] : 1,
    ]);
    $field_storage->save();

    $this->attachFileField($name, $entity_type, $bundle, $field_settings, $widget_settings);
    return $field_storage;
  }

  /**
   * Attaches a file field to an entity.
   *
   * @param string $name
   *   The name of the new field (all lowercase), exclude the "field_" prefix.
   * @param string $entity_type
   *   The entity type this field will be added to.
   * @param string $bundle
   *   The bundle this field will be added to.
   * @param array $field_settings
   *   A list of field settings that will be added to the defaults.
   * @param array $widget_settings
   *   A list of widget settings that will be added to the widget defaults.
   */
  protected function attachFileField($name, $entity_type, $bundle, $field_settings = [], $widget_settings = []) {
    FieldConfig::create([
      'field_name' => $name,
      'label' => $name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'required' => !empty($field_settings['required']),
      'settings' => $field_settings,
    ])->save();

    entity_get_form_display($entity_type, $bundle, 'default')
      ->setComponent($name, [
        'type' => 'file_generic',
        'settings' => $widget_settings,
      ])
      ->save();
    // Assign display settings.
    entity_get_display($entity_type, $bundle, 'default')
      ->setComponent($name, [
        'label' => 'hidden',
        'type' => 'file_default',
      ])
      ->save();
  }

}
