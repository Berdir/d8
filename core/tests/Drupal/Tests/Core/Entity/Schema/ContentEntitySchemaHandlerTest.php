<?php
/**
 * @file
 * Contains \Drupal\Tests\Core\Entity\Schema\ContentEntitySchemaHandlerTest.
 */

namespace Drupal\Tests\Core\Entity\Schema;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\Schema\ContentEntitySchemaHandler;
use Drupal\Core\Entity\Sql\DefaultTableMapping;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the content entity schema handler.
 *
 * @coversDefaultClass \Drupal\Core\Entity\Schema\ContentEntitySchemaHandler
 *
 * @group Drupal
 * @group Entity
 */
class ContentEntitySchemaHandlerTest extends UnitTestCase {

  /**
   * The mocked entity manager used in this test.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The mocked entity type used in this test.
   *
   * @var \Drupal\Core\Entity\ContentEntityTypeInterface
   */
  protected $entityType;

  /**
   * The mocked SQL storage used in this test.
   *
   * @var \Drupal\Core\Entity\ContentEntityDatabaseStorage|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $storage;

  /**
   * The mocked field definitions used in this test.
   *
   * @var \Drupal\Core\Field\FieldStorageDefinitionInterface[]|\PHPUnit_Framework_MockObject_MockObject[]
   */
  protected $storageDefinitions;

  /**
   * The content entity schema handler used in this test.
   *
   * @var \Drupal\Core\Entity\Schema\ContentEntitySchemaHandler.
   */
  protected $schemaHandler;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Content entity schema handler',
      'description' => 'Tests the schema generation for content entities.',
      'group' => 'Entity',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->entityManager = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $this->storage = $this->getMockBuilder('Drupal\Core\Entity\ContentEntityDatabaseStorage')
      ->disableOriginalConstructor()
      ->getMock();

    $this->storage->expects($this->any())
      ->method('getBaseTable')
      ->will($this->returnValue('entity_test'));

    // Add an ID field. This also acts as a test for a simple, single-column
    // field.
    $this->setUpStorageDefinition('id', array(
      'columns' => array(
        'value' => array(
          'type' => 'int',
        ),
      ),
    ));
  }

  /**
   * Tests the schema for non-revisionable, non-translatable entities.
   *
   * @param bool $uuid_key
   *   Whether or not the tested entity type should have a UUID key.
   *
   * @covers ::__construct
   * @covers ::getSchema
   * @covers ::getTables
   * @covers ::initializeBaseTable
   * @covers ::getEntityIndexName
   * @covers ::addFieldSchema
   * @covers ::getFieldIndexes
   * @covers ::getFieldUniqueKeys
   * @covers ::getFieldForeignKeys
   * @covers ::getFieldSchemaData
   * @covers ::addDefaultLangcodeSchema
   * @covers ::processBaseTable
   * @covers ::processIdentifierSchema
   *
   * @dataProvider providerTestGetSchemaLayoutBase
   */
  public function testGetSchemaBase($uuid_key) {
    $this->entityType = new ContentEntityType(array(
      'id' => 'entity_test',
      'entity_keys' => array(
        'id' => 'id',
        'uuid' => $uuid_key ? 'uuid' : NULL,
      ),
    ));

    // Add a field with a 'length' constraint.
    $this->setUpStorageDefinition('name', array(
      'columns' => array(
        'value' => array(
          'type' => 'varchar',
          'length' => 255,
        ),
      ),
    ));
    if ($uuid_key) {
      $this->setUpStorageDefinition('uuid', array(
        'columns' => array(
          'value' => array(
            'type' => 'varchar',
            'length' => 128,
          ),
        ),
      ));
    }
    // Add a multi-column field.
    $this->setUpStorageDefinition('description', array(
      'columns' => array(
        'value' => array(
          'type' => 'text',
          'description' => 'The text value',
        ),
        'format' => array(
          'type' => 'varchar',
          'description' => 'The text description',
        ),
      ),
    ));
    // Add a field with an index.
    $this->setUpStorageDefinition('owner', array(
      'columns' => array(
        'target_id' => array(
          'description' => 'The ID of the target entity.',
          'type' => 'int',
        ),
      ),
      'indexes' => array(
        'target_id' => array('target_id'),
      ),
    ));
    // Add a field with an index, specified as column name and length.
    $this->setUpStorageDefinition('translator', array(
      'columns' => array(
        'target_id' => array(
          'description' => 'The ID of the target entity.',
          'type' => 'int',
        ),
      ),
      'indexes' => array(
        'target_id' => array(array('target_id', 10)),
      ),
    ));
    // Add a field with a multi-column index.
    $this->setUpStorageDefinition('location', array(
      'columns' => array(
        'country' => array(
          'type' => 'varchar',
        ),
        'state' => array(
          'type' => 'varchar',
        ),
        'city' => array(
          'type' => 'varchar',
        )
      ),
      'indexes' => array(
        'country_state_city' => array('country', 'state', array('city', 10)),
      ),
    ));
    // Add a field with a foreign key.
    $this->setUpStorageDefinition('editor', array(
      'columns' => array(
        'target_id' => array(
          'type' => 'int',
        ),
      ),
      'foreign keys' => array(
        'user_id' => array(
          'table' => 'users',
          'columns' => array('target_id' => 'uid'),
        ),
      ),
    ));
    // Add a multi-column field with a foreign key.
    $this->setUpStorageDefinition('editor_revision', array(
      'columns' => array(
        'target_id' => array(
          'type' => 'int',
        ),
        'target_revision_id' => array(
          'type' => 'int',
        ),
      ),
      'foreign keys' => array(
        'user_id' => array(
          'table' => 'users',
          'columns' => array('target_id' => 'uid'),
        ),
      ),
    ));

    $this->setUpSchemaHandler();

    $table_mapping = new DefaultTableMapping($this->storageDefinitions);
    $table_mapping->addFieldColumns('entity_test', array_keys($this->storageDefinitions));
    $table_mapping->addExtraColumns('entity_test', array('default_langcode'));

    $this->storage->expects($this->once())
      ->method('getTableMapping')
      ->will($this->returnValue($table_mapping));

    $expected = array(
      'entity_test' => array(
        'description' => 'The base table for entity_test entities.',
        'fields' => array(
          'id' => array(
            'description' => 'The id field.',
            'type' => 'serial',
            'not null' => TRUE,
          ),
          'name' => array(
            'description' => 'The name field.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => FALSE,
          ),
          'description__value' => array(
            'description' => 'The description field.',
            'type' => 'text',
            'not null' => FALSE,
          ),
          'description__format' => array(
            'description' => 'The description field.',
            'type' => 'varchar',
            'not null' => FALSE,
          ),
          'owner' => array(
            'description' => 'The owner field.',
            'type' => 'int',
            'not null' => FALSE,
          ),
          'translator' => array(
            'description' => 'The translator field.',
            'type' => 'int',
            'not null' => FALSE,
          ),
          'location__country' => array(
            'description' => 'The location field.',
            'type' => 'varchar',
            'not null' => FALSE,
          ),
          'location__state' => array(
            'description' => 'The location field.',
            'type' => 'varchar',
            'not null' => FALSE,
          ),
          'location__city' => array(
            'description' => 'The location field.',
            'type' => 'varchar',
            'not null' => FALSE,
          ),
          'editor' => array(
            'description' => 'The editor field.',
            'type' => 'int',
            'not null' => FALSE,
          ),
          'editor_revision__target_id' => array(
            'description' => 'The editor_revision field.',
            'type' => 'int',
            'not null' => FALSE,
          ),
          'editor_revision__target_revision_id' => array(
            'description' => 'The editor_revision field.',
            'type' => 'int',
            'not null' => FALSE,
          ),
          'default_langcode' => array(
            'description' => 'Boolean indicating whether field values are in the default entity language.',
            'type' => 'int',
            'size' => 'tiny',
            'not null' => TRUE,
            'default' => 1,
          ),
        ),
        'primary key' => array('id'),
        'indexes' => array(
          'entity_test_field__owner__target_id' => array('owner'),
          'entity_test_field__translator__target_id' => array(
            array('translator', 10),
          ),
          'entity_test_field__location__country_state_city' => array(
            'location__country',
            'location__state',
            array('location__city', 10),
          ),
        ),
        'foreign keys' => array(
          'entity_test_field__editor__user_id' => array(
            'table' => 'users',
            'columns' => array('editor' => 'uid'),
          ),
          'entity_test_field__editor_revision__user_id' => array(
            'table' => 'users',
            'columns' => array('editor_revision__target_id' => 'uid'),
          ),
        ),
      ),
    );
    if ($uuid_key) {
      $expected['entity_test']['fields']['uuid'] = array(
        'type' => 'varchar',
        'length' => 128,
        'description' => 'The uuid field.',
        'not null' => TRUE,
      );
      $expected['entity_test']['unique keys']['entity_test__uuid'] = array('uuid');
    }
    $actual = $this->schemaHandler->getSchema();

    $this->assertEquals($expected, $actual);
  }

  /**
   * Provides data for testGetSchemaLayoutBase().
   *
   * @return array
   *   Returns a nested array where each inner array returns a boolean,
   *   indicating whether or not the tested entity type should include a UUID
   *   key.
   */
  public function providerTestGetSchemaLayoutBase() {
    return array(
      array(FALSE),
      array(TRUE),
    );
  }

  /**
   * Tests the schema for revisionable, non-translatable entities.
   *
   * @covers ::__construct
   * @covers ::getSchema
   * @covers ::getTables
   * @covers ::initializeBaseTable
   * @covers ::initializeRevisionTable
   * @covers ::getEntityIndexName
   * @covers ::processRevisionTable
   * @covers ::processIdentifierSchema
   */
  public function testGetSchemaRevisionable() {
    $this->entityType = new ContentEntityType(array(
      'id' => 'entity_test',
      'entity_keys' => array(
        'id' => 'id',
        'revision' => 'revision_id',
      ),
    ));

    $this->storage->expects($this->exactly(2))
      ->method('getRevisionTable')
      ->will($this->returnValue('entity_test_revision'));

    $this->setUpStorageDefinition('revision_id', array(
      'columns' => array(
        'value' => array(
          'type' => 'int',
        ),
      ),
    ));

    $this->setUpSchemaHandler();

    $table_mapping = new DefaultTableMapping($this->storageDefinitions);
    $table_mapping->addFieldColumns('entity_test', array_keys($this->storageDefinitions));
    $table_mapping->addFieldColumns('entity_test_revision', array_keys($this->storageDefinitions));

    $this->storage->expects($this->once())
      ->method('getTableMapping')
      ->will($this->returnValue($table_mapping));

    $expected = array(
      'entity_test' => array(
        'description' => 'The base table for entity_test entities.',
        'fields' => array(
          'id' => array(
            'description' => 'The id field.',
            'type' => 'serial',
            'not null' => TRUE,
          ),
          'revision_id' => array(
            'description' => 'The revision_id field.',
            'type' => 'int',
            'not null' => FALSE,
          )
        ),
        'primary key' => array('id'),
        'indexes' => array(),
        'foreign keys' => array(
          'entity_test__revision' => array(
            'table' => 'entity_test_revision',
            'columns' => array('revision_id' => 'revision_id'),
          )
        ),
        'unique keys' => array(
          'entity_test__revision_id' => array('revision_id'),
        ),
      ),
      'entity_test_revision' => array(
        'description' => 'The revision table for entity_test entities.',
        'fields' => array(
          'id' => array(
            'description' => 'The id field.',
            'type' => 'int',
            'not null' => TRUE,
          ),
          'revision_id' => array(
            'description' => 'The revision_id field.',
            'type' => 'serial',
            'not null' => FALSE,
          ),
        ),
        'primary key' => array('revision_id'),
        'indexes' => array(
          'entity_test__id' => array('id'),
        ),
        'foreign keys' => array(
          'entity_test__revisioned' => array(
            'table' => 'entity_test',
            'columns' => array('id' => 'id'),
          ),
        ),
      ),
    );

    $actual = $this->schemaHandler->getSchema();

    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests the schema for non-revisionable, translatable entities.
   *
   * @covers ::__construct
   * @covers ::getSchema
   * @covers ::getTables
   * @covers ::initializeDataTable
   * @covers ::getEntityIndexName
   * @covers ::processDataTable
   */
  public function testGetSchemaTranslatable() {
    $this->entityType = new ContentEntityType(array(
      'id' => 'entity_test',
      'entity_keys' => array(
        'id' => 'id',
      ),
    ));

    $this->storage->expects($this->once())
      ->method('getDataTable')
      ->will($this->returnValue('entity_test_field_data'));

    $this->setUpStorageDefinition('langcode', array(
      'columns' => array(
        'value' => array(
          'type' => 'varchar',
        ),
      ),
    ));

    $this->setUpSchemaHandler();

    $table_mapping = new DefaultTableMapping($this->storageDefinitions);
    $table_mapping->addFieldColumns('entity_test', array_keys($this->storageDefinitions));
    $table_mapping->addFieldColumns('entity_test_field_data', array_keys($this->storageDefinitions));

    $this->storage->expects($this->once())
      ->method('getTableMapping')
      ->will($this->returnValue($table_mapping));

    $expected = array(
      'entity_test' => array(
        'description' => 'The base table for entity_test entities.',
        'fields' => array(
          'id' => array(
            'description' => 'The id field.',
            'type' => 'serial',
            'not null' => TRUE,
          ),
          'langcode' => array(
            'description' => 'The langcode field.',
            'type' => 'varchar',
            'not null' => TRUE,
          )
        ),
        'primary key' => array('id'),
        'indexes' => array(),
        'foreign keys' => array(),
      ),
      'entity_test_field_data' => array(
        'description' => 'The data table for entity_test entities.',
        'fields' => array(
          'id' => array(
            'description' => 'The id field.',
            'type' => 'int',
            'not null' => TRUE,
          ),
          'langcode' => array(
            'description' => 'The langcode field.',
            'type' => 'varchar',
            'not null' => TRUE,
          ),
        ),
        'primary key' => array('id', 'langcode'),
        'indexes' => array(),
        'foreign keys' => array(
          'entity_test' => array(
            'table' => 'entity_test',
            'columns' => array('id' => 'id'),
          ),
        ),
      ),
    );

    $actual = $this->schemaHandler->getSchema();

    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests the schema for revisionable, translatable entities.
   *
   * @covers ::__construct
   * @covers ::getSchema
   * @covers ::getTables
   * @covers ::initializeDataTable
   * @covers ::getEntityIndexName
   * @covers ::initializeRevisionDataTable
   * @covers ::processRevisionDataTable
   */
  public function testGetSchemaRevisionableTranslatable() {
    $this->entityType = new ContentEntityType(array(
      'id' => 'entity_test',
      'entity_keys' => array(
        'id' => 'id',
        'revision' => 'revision_id',
      ),
    ));

    $this->storage->expects($this->exactly(3))
      ->method('getRevisionTable')
      ->will($this->returnValue('entity_test_revision'));
    $this->storage->expects($this->once())
      ->method('getDataTable')
      ->will($this->returnValue('entity_test_field_data'));
    $this->storage->expects($this->once())
      ->method('getRevisionDataTable')
      ->will($this->returnValue('entity_test_revision_field_data'));

    $this->setUpStorageDefinition('revision_id', array(
      'columns' => array(
        'value' => array(
          'type' => 'int',
        ),
      ),
    ));
    $this->setUpStorageDefinition('langcode', array(
      'columns' => array(
        'value' => array(
          'type' => 'varchar',
        ),
      ),
    ));

    $this->setUpSchemaHandler();

    $table_mapping = new DefaultTableMapping($this->storageDefinitions);
    $table_mapping->addFieldColumns('entity_test', array_keys($this->storageDefinitions));
    $table_mapping->addFieldColumns('entity_test_revision', array_keys($this->storageDefinitions));
    $table_mapping->addFieldColumns('entity_test_field_data', array_keys($this->storageDefinitions));
    $table_mapping->addFieldColumns('entity_test_revision_field_data', array_keys($this->storageDefinitions));

    $this->storage->expects($this->once())
      ->method('getTableMapping')
      ->will($this->returnValue($table_mapping));

    $expected = array(
      'entity_test' => array(
        'description' => 'The base table for entity_test entities.',
        'fields' => array(
          'id' => array(
            'description' => 'The id field.',
            'type' => 'serial',
            'not null' => TRUE,
          ),
          'revision_id' => array(
            'description' => 'The revision_id field.',
            'type' => 'int',
            'not null' => FALSE,
          ),
          'langcode' => array(
            'description' => 'The langcode field.',
            'type' => 'varchar',
            'not null' => TRUE,
          )
        ),
        'primary key' => array('id'),
        'indexes' => array(),
        'unique keys' => array(
          'entity_test__revision_id' => array('revision_id'),
        ),
        'foreign keys' => array(
          'entity_test__revision' => array(
            'table' => 'entity_test_revision',
            'columns' => array('revision_id' => 'revision_id'),
          ),
        ),
      ),
      'entity_test_revision' => array(
        'description' => 'The revision table for entity_test entities.',
        'fields' => array(
          'id' => array(
            'description' => 'The id field.',
            'type' => 'int',
            'not null' => TRUE,
          ),
          'revision_id' => array(
            'description' => 'The revision_id field.',
            'type' => 'serial',
            'not null' => FALSE,
          ),
          'langcode' => array(
            'description' => 'The langcode field.',
            'type' => 'varchar',
            'not null' => TRUE,
          ),
        ),
        'primary key' => array('revision_id'),
        'indexes' => array(
          'entity_test__id' => array('id'),
        ),
        'foreign keys' => array(
          'entity_test__revisioned' => array(
            'table' => 'entity_test',
            'columns' => array('id' => 'id'),
          ),
        ),
      ),
      'entity_test_field_data' => array(
        'description' => 'The data table for entity_test entities.',
        'fields' => array(
          'id' => array(
            'description' => 'The id field.',
            'type' => 'int',
            'not null' => TRUE,
          ),
          'revision_id' => array(
            'description' => 'The revision_id field.',
            'type' => 'int',
            'not null' => FALSE,
          ),
          'langcode' => array(
            'description' => 'The langcode field.',
            'type' => 'varchar',
            'not null' => TRUE,
          ),
        ),
        'primary key' => array('id', 'langcode'),
        'indexes' => array(
          'entity_test__revision_id' => array('revision_id'),
        ),
        'foreign keys' => array(
          'entity_test' => array(
            'table' => 'entity_test',
            'columns' => array('id' => 'id'),
          ),
        ),
      ),
      'entity_test_revision_field_data' => array(
        'description' => 'The revision data table for entity_test entities.',
        'fields' => array(
          'id' => array(
            'description' => 'The id field.',
            'type' => 'int',
            'not null' => TRUE,
          ),
          'revision_id' => array(
            'description' => 'The revision_id field.',
            'type' => 'int',
            'not null' => FALSE,
          ),
          'langcode' => array(
            'description' => 'The langcode field.',
            'type' => 'varchar',
            'not null' => TRUE,
          ),
        ),
        'primary key' => array('revision_id', 'langcode'),
        'indexes' => array(),
        'foreign keys' => array(
          'entity_test' => array(
            'table' => 'entity_test',
            'columns' => array('id' => 'id'),
          ),
          'entity_test__revision' => array(
            'table' => 'entity_test_revision',
            'columns' => array('revision_id' => 'revision_id'),
          ),
        ),
      ),
    );

    $actual = $this->schemaHandler->getSchema();

    $this->assertEquals($expected, $actual);
  }

  /**
   * Sets up the schema handler.
   *
   * This uses the field definitions set in $this->fieldDefinitions.
   */
  protected function setUpSchemaHandler() {
    $this->entityManager->expects($this->once())
      ->method('getFieldStorageDefinitions')
      ->with($this->entityType->id())
      ->will($this->returnValue($this->storageDefinitions));
    $this->schemaHandler = new ContentEntitySchemaHandler(
      $this->entityManager,
      $this->entityType,
      $this->storage
    );
  }

  /**
   * Sets up a field definition.
   *
   * @param string $field_name
   *   The field name.
   * @param array $schema
   *   The schema array of the field definition, as returned from
   *   FieldDefinitionInterface::schema().
   */
  public function setUpStorageDefinition($field_name, array $schema) {
    $this->storageDefinitions[$field_name] = $this->getMock('Drupal\Core\Field\FieldStorageDefinitionInterface');
    // getDescription() is called once for each table.
    $this->storageDefinitions[$field_name]->expects($this->any())
      ->method('getDescription')
      ->will($this->returnValue("The $field_name field."));
    // getSchema() is called once for each table.
    $this->storageDefinitions[$field_name]->expects($this->any())
      ->method('getSchema')
      ->will($this->returnValue($schema));
    $this->storageDefinitions[$field_name]->expects($this->once())
      ->method('getColumns')
      ->will($this->returnValue($schema['columns']));
  }

}

