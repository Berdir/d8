<?php

/**
 * @file
 * Contains \Drupal\field\Tests\FieldEntityCountTest.
 */

namespace Drupal\field\Tests;

use Drupal\Core\Entity\ContentEntityDatabaseStorage;

/**
 * Tests entityCount() and hasData() methods on FieldStorageConfig entity.
 *
 * @see \Drupal\field\Entity\FieldStorageConfig::entityCount()
 * @see \Drupal\field\Entity\FieldStorageConfig::hasData()
 */
class FieldEntityCountTest extends FieldUnitTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Field config entityCount() and hasData() tests.',
      'description' => 'Tests entityCount() and hasData() methods on FieldStorageConfig entity.',
      'group' => 'Field API',
    );
  }

  /**
   * Tests entityCount() and hadData() methods.
   */
  public function testEntityCountAndHasData() {
    // Create a field with a cardinality of 2 to show that we are counting
    // entities and not rows in a table.
    /** @var \Drupal\field\Entity\FieldStorageConfig $field_storage */
    $field_storage = entity_create('field_storage_config', array(
      'name' => 'field_int',
      'entity_type' => 'entity_test',
      'type' => 'integer',
      'cardinality' => 2,
    ));
    $field_storage->save();
    entity_create('field_instance_config', array(
      'field' => $field_storage,
      'bundle' => 'entity_test',
    ))->save();

    $this->assertIdentical($field_storage->hasdata(), FALSE, 'There are no entities with field data.');
    $this->assertIdentical($field_storage->entityCount(), 0, 'There are 0 entities with field data.');

    // Create 1 entity without the field.
    $entity = entity_create('entity_test');
    $entity->name->value = $this->randomName();
    $entity->save();

    $this->assertIdentical($field_storage->hasdata(), FALSE, 'There are no entities with field data.');
    $this->assertIdentical($field_storage->entityCount(), 0, 'There are 0 entities with field data.');

    // Create 12 entities to ensure that the purging works as expected.
    for ($i=0; $i < 12; $i++) {
      $entity = entity_create('entity_test');
      $value = mt_rand(1,99);
      $value2 = mt_rand(1,99);
      $entity->field_int[0]->value = $value;
      $entity->field_int[1]->value = $value2;
      $entity->name->value = $this->randomName();
      $entity->save();
    }

    $storage = \Drupal::entityManager()->getStorage('entity_test');
    if ($storage instanceof ContentEntityDatabaseStorage) {
      // Count the actual number of rows in the field table.
      $field_table_name = $storage->_fieldTableName($field_storage);
      $result = db_select($field_table_name, 't')
        ->fields('t')
        ->countQuery()
        ->execute()
        ->fetchField();
      $this->assertEqual($result, 24, 'The field table has 24 rows.');
    }

    $this->assertIdentical($field_storage->hasdata(), TRUE, 'There are entities with field data.');
    $this->assertEqual($field_storage->entityCount(), 12, 'There are 12 entities with field data.');

    // Ensure the methods work on deleted fields.
    $field_storage->delete();
    $this->assertIdentical($field_storage->hasdata(), TRUE, 'There are entities with deleted field data.');
    $this->assertEqual($field_storage->entityCount(), 12, 'There are 12 entities with deleted field data.');

    field_purge_batch(6);
    $this->assertIdentical($field_storage->hasdata(), TRUE, 'There are entities with deleted field data.');
    $this->assertEqual($field_storage->entityCount(), 6, 'There are 6 entities with deleted field data.');
  }

}
