<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Schema\ContentEntitySchemaHandler.
 */

namespace Drupal\Core\Entity\Schema;

use Drupal\Component\Utility\String;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityDatabaseStorage;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\Exception\FieldStorageDefinitionUpdateForbiddenException;
use Drupal\Core\Field\FieldException;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines a schema handler that supports revisionable, translatable entities.
 */
class ContentEntitySchemaHandler implements ContentEntitySchemaHandlerInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The entity type this schema builder is responsible for.
   *
   * @var \Drupal\Core\Entity\ContentEntityTypeInterface
   */
  protected $entityType;

  /**
   * The storage field definitions for this entity type.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface[]
   */
  protected $fieldStorageDefinitions;

  /**
   * The storage object for the given entity type.
   *
   * @var \Drupal\Core\Entity\ContentEntityDatabaseStorage
   */
  protected $storage;

  /**
   * A static cache of the generated schema array.
   *
   * @var array
   */
  protected $schema;

  /**
   * The database connection to be used.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a ContentEntitySchemaHandler.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   * @param \Drupal\Core\Entity\ContentEntityDatabaseStorage $storage
   *   The storage of the entity type. This must be an SQL-based storage.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   */
  public function __construct(EntityManagerInterface $entity_manager, ContentEntityTypeInterface $entity_type, ContentEntityDatabaseStorage $storage, Connection $database) {
    $this->entityManager = $entity_manager;
    $this->entityType = $entity_type;
    $this->fieldStorageDefinitions = $entity_manager->getFieldStorageDefinitions($entity_type->id());
    $this->storage = $storage;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function createEntitySchema(ContentEntityTypeInterface $entity_type) {
    $this->checkEntityType($entity_type);
    $schema_handler = $this->database->schema();
    foreach ($this->getEntitySchema($entity_type) as $table_name => $table_schema) {
      if (!$schema_handler->tableExists($table_name)) {
        $schema_handler->createTable($table_name, $table_schema);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function dropEntitySchema(ContentEntityTypeInterface $entity_type) {
    $this->checkEntityType($entity_type);
    $schema_handler = $this->database->schema();
    foreach ($this->getEntitySchema($entity_type) as $table_name => $table_schema) {
      if ($schema_handler->tableExists($table_name)) {
        $schema_handler->dropTable($table_name, $table_schema);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateEntitySchema(ContentEntityTypeInterface $entity_type, ContentEntityTypeInterface $original) {
    $this->checkEntityType($entity_type);
    $this->checkEntityType($original);

    if ($entity_type->getStorageClass() != $original->getStorageClass() || $entity_type->isRevisionable() != $original->isRevisionable() || $entity_type->isTranslatable() != $original->isTranslatable()) {
      if ($this->database->supportsTransactionalDDL()) {
        // If the database supports transactional DDL, we can go ahead and rely
        // on it. If not, we will have to rollback manually if something fails.
        $transaction = $this->database->startTransaction();
      }
      try {
        // @todo Instead of switching the wrapped entity type, we should be able
        //   to instantiate a new table mapping for each entity type definition.
        //   See https://www.drupal.org/node/2274017.
        $this->storage->setEntityType($original);
        $this->dropEntitySchema($original);
        $this->storage->setEntityType($entity_type);
        $entity_type_id = $entity_type->id();
        unset($this->schema[$entity_type_id]);
        $this->createEntitySchema($entity_type);
      }
      catch (\Exception $e) {
        if ($this->database->supportsTransactionalDDL()) {
          $transaction->rollback();
        }
        else {
          // Recreate original schema.
          $this->createEntitySchema($original);
        }
        throw $e;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type) {
    $this->checkEntityType($entity_type);

    // Prepare basic information about the entity type.
    $tables = $this->getTables();
    $entity_type_id = $entity_type->id();

    if (!isset($this->schema[$entity_type_id])) {
      // Initialize the table schema.
      $schema[$tables['base_table']] = $this->initializeBaseTable($entity_type);
      if (isset($tables['revision_table'])) {
        $schema[$tables['revision_table']] = $this->initializeRevisionTable($entity_type);
      }
      if (isset($tables['data_table'])) {
        $schema[$tables['data_table']] = $this->initializeDataTable($entity_type);
      }
      if (isset($tables['revision_data_table'])) {
        $schema[$tables['revision_data_table']] = $this->initializeRevisionDataTable($entity_type);
      }

      // We need to act only on entity schema tables.
      $table_mapping = $this->storage->getTableMapping();
      $table_names = array_diff($table_mapping->getTableNames(), $table_mapping->getDedicatedTableNames());
      $storage_definitions = $this->entityManager->getFieldStorageDefinitions($entity_type_id);
      foreach ($table_names as $table_name) {
        if (!isset($schema[$table_name])) {
          $schema[$table_name] = array();
        }
        foreach ($table_mapping->getFieldNames($table_name) as $field_name) {
          if (!isset($storage_definitions[$field_name])) {
            throw new FieldException(String::format('Fieled storage definition for "@field_name" could not be found.', array('@field_name' => $field_name)));
          }
          // Add the schema for base field definitions.
          elseif ($table_mapping->allowsSharedTableStorage($storage_definitions[$field_name])) {
            $column_names = $table_mapping->getColumnNames($field_name);
            $storage_definition = $storage_definitions[$field_name];
            $schema[$table_name] = array_merge_recursive($schema[$table_name], $this->getSharedTableFieldSchema($storage_definition, $column_names));
          }
        }

        // Add the schema for extra fields.
        foreach ($table_mapping->getExtraColumns($table_name) as $column_name) {
          if ($column_name == 'default_langcode') {
            $this->addDefaultLangcodeSchema($schema[$table_name]);
          }
        }
      }

      // Process tables after having gathered field information.
      $this->processBaseTable($entity_type, $schema[$tables['base_table']]);
      if (isset($tables['revision_table'])) {
        $this->processRevisionTable($entity_type, $schema[$tables['revision_table']]);
      }
      if (isset($tables['data_table'])) {
        $this->processDataTable($entity_type, $schema[$tables['data_table']]);
      }
      if (isset($tables['revision_data_table'])) {
        $this->processRevisionDataTable($entity_type, $schema[$tables['revision_data_table']]);
      }

      $this->schema[$entity_type_id] = $schema;
    }

    return $this->schema[$entity_type_id];
  }

  /**
   * Checks that we are dealing with the correct entity type.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type to be checked.
   *
   * @return bool
   *   TRUE if the entity type matches the current one.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function checkEntityType(ContentEntityTypeInterface $entity_type) {
    if ($entity_type->id() != $this->entityType->id()) {
      throw new EntityStorageException(String::format('Unsupported entity type @id', array('@id' => $entity_type->id())));
    }
    return TRUE;
  }

  /**
   * Gets a list of entity type tables.
   *
   * @return array
   *   A list of entity type tables, keyed by table key.
   */
  protected function getTables() {
    return array_filter(array(
      'base_table' => $this->storage->getBaseTable(),
      'revision_table' => $this->storage->getRevisionTable(),
      'data_table' => $this->storage->getDataTable(),
      'revision_data_table' => $this->storage->getRevisionDataTable(),
    ));
  }

  /**
   * Initializes common information for a base table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return array
   *   A partial schema array for the base table.
   */
  protected function initializeBaseTable(ContentEntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();

    $schema = array(
      'description' => "The base table for $entity_type_id entities.",
      'primary key' => array($entity_type->getKey('id')),
      'indexes' => array(),
      'foreign keys' => array(),
    );

    if ($entity_type->hasKey('revision')) {
      $revision_key = $entity_type->getKey('revision');
      $key_name = $this->getEntityIndexName($entity_type, $revision_key);
      $schema['unique keys'][$key_name] = array($revision_key);
      $schema['foreign keys'][$entity_type_id . '__revision'] = array(
        'table' => $this->storage->getRevisionTable(),
        'columns' => array($revision_key => $revision_key),
      );
    }

    $this->addTableDefaults($schema);

    return $schema;
  }

  /**
   * Initializes common information for a revision table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return array
   *   A partial schema array for the revision table.
   */
  protected function initializeRevisionTable(ContentEntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    $id_key = $entity_type->getKey('id');
    $revision_key = $entity_type->getKey('revision');

    $schema = array(
      'description' => "The revision table for $entity_type_id entities.",
      'primary key' => array($revision_key),
      'indexes' => array(),
      'foreign keys' => array(
        $entity_type_id . '__revisioned' => array(
          'table' => $this->storage->getBaseTable(),
          'columns' => array($id_key => $id_key),
        ),
      ),
    );

    $schema['indexes'][$this->getEntityIndexName($entity_type, $id_key)] = array($id_key);

    $this->addTableDefaults($schema);

    return $schema;
  }

  /**
   * Initializes common information for a data table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return array
   *   A partial schema array for the data table.
   */
  protected function initializeDataTable(ContentEntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    $id_key = $entity_type->getKey('id');

    $schema = array(
      'description' => "The data table for $entity_type_id entities.",
      // @todo Use the language entity key when https://drupal.org/node/2143729
      //   is in.
      'primary key' => array($id_key, 'langcode'),
      'indexes' => array(),
      'foreign keys' => array(
        $entity_type_id => array(
          'table' => $this->storage->getBaseTable(),
          'columns' => array($id_key => $id_key),
        ),
      ),
    );

    if ($entity_type->hasKey('revision')) {
      $key = $entity_type->getKey('revision');
      $schema['indexes'][$this->getEntityIndexName($entity_type, $key)] = array($key);
    }

    $this->addTableDefaults($schema);

    return $schema;
  }

  /**
   * Initializes common information for a revision data table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return array
   *   A partial schema array for the revision data table.
   */
  protected function initializeRevisionDataTable(ContentEntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    $id_key = $entity_type->getKey('id');
    $revision_key = $entity_type->getKey('revision');

    $schema = array(
      'description' => "The revision data table for $entity_type_id entities.",
      // @todo Use the language entity key when https://drupal.org/node/2143729
      //   is in.
      'primary key' => array($revision_key, 'langcode'),
      'indexes' => array(),
      'foreign keys' => array(
        $entity_type_id => array(
          'table' => $this->storage->getBaseTable(),
          'columns' => array($id_key => $id_key),
        ),
        $entity_type_id . '__revision' => array(
          'table' => $this->storage->getRevisionTable(),
          'columns' => array($revision_key => $revision_key),
        )
      ),
    );

    $this->addTableDefaults($schema);

    return $schema;
  }

  /**
   * Processes the gathered schema for a base table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   * @param array $schema
   *   The table schema, passed by reference.
   *
   * @return array
   *   A partial schema array for the base table.
   */
  protected function processBaseTable(ContentEntityTypeInterface $entity_type, array &$schema) {
    $this->processIdentifierSchema($schema, $entity_type->getKey('id'));
  }

  /**
   * Processes the gathered schema for a base table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   * @param array $schema
   *   The table schema, passed by reference.
   *
   * @return array
   *   A partial schema array for the base table.
   */
  protected function processRevisionTable(ContentEntityTypeInterface $entity_type, array &$schema) {
    $this->processIdentifierSchema($schema, $entity_type->getKey('revision'));
  }

  /**
   * Processes the gathered schema for a base table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   * @param array $schema
   *   The table schema, passed by reference.
   *
   * @return array
   *   A partial schema array for the base table.
   */
  protected function processDataTable(ContentEntityTypeInterface $entity_type, array &$schema) {
  }

  /**
   * Processes the gathered schema for a base table.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   * @param array $schema
   *   The table schema, passed by reference.
   *
   * @return array
   *   A partial schema array for the base table.
   */
  protected function processRevisionDataTable(ContentEntityTypeInterface $entity_type, array &$schema) {
  }

  /**
   * Performs the specified operation on a field.
   *
   * This figures out whether the field is stored in a dedicated or shared table
   * and forwards the call to the proper handler.
   *
   * @param string $operation
   *   The name of the operation to be performed.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage definition
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $original
   *   (optional) The original field storage definition. This is relevant (and
   *   required) only for updates. Defaults to NULL.
   */
  protected function performFieldSchemaOperation($operation, FieldStorageDefinitionInterface $storage_definition, FieldStorageDefinitionInterface $original = NULL) {
    $table_mapping = $this->storage->getTableMapping();
    if ($table_mapping->requiresDedicatedTableStorage($storage_definition)) {
      $this->{$operation . 'DedicatedTableSchema'}($storage_definition, $original);
    }
    elseif ($table_mapping->allowsSharedTableStorage($storage_definition)) {
      $this->{$operation . 'SharedTableSchema'}($storage_definition, $original);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createFieldSchema(FieldStorageDefinitionInterface $storage_definition) {
    $this->performFieldSchemaOperation('create', $storage_definition);
  }

  /**
   * Creates the schema for a field stored in a dedicated table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being created.
   */
  protected function createDedicatedTableSchema(FieldStorageDefinitionInterface $storage_definition) {
    $schema = $this->getDedicatedTableSchema($storage_definition);
    foreach ($schema as $name => $table) {
      $this->database->schema()->createTable($name, $table);
    }
  }

  /**
   * Creates the schema for a field stored in a shared table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being created.
   */
  protected function createSharedTableSchema(FieldStorageDefinitionInterface $storage_definition) {
    $created_field_name = $storage_definition->getName();
    $table_mapping = $this->storage->getTableMapping();
    $column_names = $table_mapping->getColumnNames($created_field_name);
    $schema = $this->getSharedTableFieldSchema($storage_definition, $column_names);
    $keys = array_diff_key($schema, array('fields' => FALSE));

    // Iterate over the mapped table to find the ones that will host the created
    // field schema.
    foreach ($table_mapping->getTableNames() as $table_name) {
      foreach ($table_mapping->getFieldNames($table_name) as $field_name) {
        if ($field_name == $created_field_name) {
          foreach ($schema['fields'] as $column_name => $spec) {
            // Support an initial value for new fields.
            if ($initial = $storage_definition->getSetting('initial')) {
              $spec['initial'] = $initial;
            }
            $this->database->schema()->addField($table_name, $column_name, $spec, $keys);
          }
          // After creating the field schema skip to the next table.
          break;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function prepareFieldSchemaDeletion(FieldStorageDefinitionInterface $storage_definition) {
    $table_mapping = $this->storage->getTableMapping();
    // @todo Implement this also for shared table storage. See
    //   https://www.drupal.org/node/2282119.
    if ($table_mapping->requiresDedicatedTableStorage($storage_definition)) {
      // Move the table to a unique name while the table contents are being
      // deleted.
      $table = $table_mapping->getDedicatedDataTableName($storage_definition);
      $revision_table = $table_mapping->getDedicatedRevisionTableName($storage_definition);
      $new_table = $table_mapping->getDedicatedDataTableName($storage_definition, TRUE);
      $revision_new_table = $table_mapping->getDedicatedRevisionTableName($storage_definition, TRUE);
      $this->database->schema()->renameTable($table, $new_table);
      $this->database->schema()->renameTable($revision_table, $revision_new_table);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteFieldSchema(FieldStorageDefinitionInterface $storage_definition) {
    $this->performFieldSchemaOperation('delete', $storage_definition);
  }

  /**
   * Deletes the schema for a field stored in a dedicated table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being deleted.
   */
  protected function deleteDedicatedTableSchema(FieldStorageDefinitionInterface $storage_definition) {
    $table_mapping = $this->storage->getTableMapping();
    $table_name = $table_mapping->getDedicatedDataTableName($storage_definition, TRUE);
    $revision_name = $table_mapping->getDedicatedRevisionTableName($storage_definition, TRUE);
    $this->database->schema()->dropTable($table_name);
    $this->database->schema()->dropTable($revision_name);
  }

  /**
   * Deletes the schema for a field stored in a shared table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being deleted.
   */
  protected function deleteSharedTableSchema(FieldStorageDefinitionInterface $storage_definition) {
    $deleted_field_name = $storage_definition->getName();
    $table_mapping = $this->storage->getTableMapping();
    $column_names = $table_mapping->getColumnNames($deleted_field_name);
    $schema = $this->getSharedTableFieldSchema($storage_definition, $column_names);
    $schema_handler = $this->database->schema();

    // Iterate over the mapped table to find the ones that host the deleted
    // field schema.
    foreach ($table_mapping->getTableNames() as $table_name) {
      foreach ($table_mapping->getFieldNames($table_name) as $field_name) {
        if ($field_name == $deleted_field_name) {
          // Drop indexes and unique keys first.
          if (!empty($schema['indexes'])) {
            foreach ($schema['indexes'] as $name => $specifier) {
              $schema_handler->dropIndex($table_name, $name);
            }
          }
          if (!empty($schema['unique keys'])) {
            foreach ($schema['unique keys'] as $name => $specifier) {
              $schema_handler->dropUniqueKey($table_name, $name);
            }
          }
          // Drop columns.
          foreach ($column_names as $column_name) {
            $schema_handler->dropField($table_name, $column_name);
          }
          // After deleting the field schema skip to the next table.
          break;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateFieldSchema(FieldStorageDefinitionInterface $storage_definition, FieldStorageDefinitionInterface $original) {
    $this->performFieldSchemaOperation('update', $storage_definition, $original);
  }

  /**
   * Updates the schema for a field stored in a shared table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being updated.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $original
   *   The original storage definition; i.e., the definition before the update.
   *
   * @throws \Drupal\Core\Entity\Exception\FieldStorageDefinitionUpdateForbiddenException
   *   Thrown when the update to the field is forbidden.
   * @throws \Exception
   *   Rethrown exception if the table recreation fails.
   */
  protected function updateDedicatedTableSchema(FieldStorageDefinitionInterface $storage_definition, FieldStorageDefinitionInterface $original) {
    if (!$storage_definition->hasData()) {
      // There is no data. Re-create the tables completely.
      if ($this->database->supportsTransactionalDDL()) {
        // If the database supports transactional DDL, we can go ahead and rely
        // on it. If not, we will have to rollback manually if something fails.
        $transaction = $this->database->startTransaction();
      }
      try {
        $original_schema = $this->getDedicatedTableSchema($original);
        foreach ($original_schema as $name => $table) {
          $this->database->schema()->dropTable($name, $table);
        }
        $schema = $this->getDedicatedTableSchema($storage_definition);
        foreach ($schema as $name => $table) {
          $this->database->schema()->createTable($name, $table);
        }
      }
      catch (\Exception $e) {
        if ($this->database->supportsTransactionalDDL()) {
          $transaction->rollback();
        }
        else {
          // Recreate tables.
          $original_schema = $this->getDedicatedTableSchema($original);
          foreach ($original_schema as $name => $table) {
            if (!$this->database->schema()->tableExists($name)) {
              $this->database->schema()->createTable($name, $table);
            }
          }
        }
        throw $e;
      }
    }
    else {
      if ($storage_definition->getColumns() != $original->getColumns()) {
        throw new FieldStorageDefinitionUpdateForbiddenException("The SQL storage cannot change the schema for an existing field with data.");
      }
      // There is data, so there are no column changes. Drop all the prior
      // indexes and create all the new ones, except for all the priors that
      // exist unchanged.
      $table_mapping = $this->storage->getTableMapping();
      $table = $table_mapping->getDedicatedDataTableName($original);
      $revision_table = $table_mapping->getDedicatedRevisionTableName($original);

      $schema = $storage_definition->getSchema();
      $original_schema = $original->getSchema();

      foreach ($original_schema['indexes'] as $name => $columns) {
        if (!isset($schema['indexes'][$name]) || $columns != $schema['indexes'][$name]) {
          $real_name = $this->getFieldIndexName($storage_definition, $name);
          $this->database->schema()->dropIndex($table, $real_name);
          $this->database->schema()->dropIndex($revision_table, $real_name);
        }
      }
      $table = $table_mapping->getDedicatedDataTableName($storage_definition);
      $revision_table = $table_mapping->getDedicatedRevisionTableName($storage_definition);
      foreach ($schema['indexes'] as $name => $columns) {
        if (!isset($original_schema['indexes'][$name]) || $columns != $original_schema['indexes'][$name]) {
          $real_name = $this->getFieldIndexName($storage_definition, $name);
          $real_columns = array();
          foreach ($columns as $column_name) {
            // Indexes can be specified as either a column name or an array with
            // column name and length. Allow for either case.
            if (is_array($column_name)) {
              $real_columns[] = array(
                $table_mapping->getFieldColumnName($storage_definition, $column_name[0]),
                $column_name[1],
              );
            }
            else {
              $real_columns[] = $table_mapping->getFieldColumnName($storage_definition, $column_name);
            }
          }
          $this->database->schema()->addIndex($table, $real_name, $real_columns);
          $this->database->schema()->addIndex($revision_table, $real_name, $real_columns);
        }
      }
    }
  }

  /**
   * Updates the schema for a field stored in a shared table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field being updated.
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $original
   *   The original storage definition; i.e., the definition before the update.
   *
   * @throws \Drupal\Core\Entity\Exception\FieldStorageDefinitionUpdateForbiddenException
   *   Thrown when the update to the field is forbidden.
   * @throws \Exception
   *   Rethrown exception if the table recreation fails.
   */
  protected function updateSharedTableSchema(FieldStorageDefinitionInterface $storage_definition, FieldStorageDefinitionInterface $original) {
    if (!$this->storage->countFieldData($storage_definition, TRUE)) {
      if ($this->database->supportsTransactionalDDL()) {
        // If the database supports transactional DDL, we can go ahead and rely
        // on it. If not, we will have to rollback manually if something fails.
        $transaction = $this->database->startTransaction();
      }
      try {
        $this->deleteSharedTableSchema($original);
        $this->createSharedTableSchema($storage_definition);
      }
      catch (\Exception $e) {
        if ($this->database->supportsTransactionalDDL()) {
          $transaction->rollback();
        }
        else {
          // Recreate original schema.
          $this->createSharedTableSchema($original);
        }
        throw $e;
      }
    }
    else {
      if ($storage_definition->getColumns() != $original->getColumns()) {
        throw new FieldStorageDefinitionUpdateForbiddenException("The SQL storage cannot change the schema for an existing field with data.");
      }

      $updated_field_name = $storage_definition->getName();
      $table_mapping = $this->storage->getTableMapping();
      $column_names = $table_mapping->getColumnNames($updated_field_name);
      $original_schema = $this->getSharedTableFieldSchema($original, $column_names);
      $schema = $this->getSharedTableFieldSchema($storage_definition, $column_names);
      $schema_handler = $this->database->schema();

      // Iterate over the mapped table to find the ones that host the deleted
      // field schema.
      foreach ($table_mapping->getTableNames() as $table_name) {
        foreach ($table_mapping->getFieldNames($table_name) as $field_name) {
          if ($field_name == $updated_field_name) {
            // Drop original indexes and unique keys.
            if (!empty($original_schema['indexes'])) {
              foreach ($original_schema['indexes'] as $name => $specifier) {
                $schema_handler->dropIndex($table_name, $name);
              }
            }
            if (!empty($original_schema['unique keys'])) {
              foreach ($original_schema['unique keys'] as $name => $specifier) {
                $schema_handler->dropUniqueKey($table_name, $name);
              }
            }
            // Create new indexes and unique keys.
            if (!empty($schema['indexes'])) {
              foreach ($schema['indexes'] as $name => $specifier) {
                $schema_handler->addIndex($table_name, $name, $specifier);
              }
            }
            if (!empty($schema['unique keys'])) {
              foreach ($schema['unique keys'] as $name => $specifier) {
                $schema_handler->addUniqueKey($table_name, $name, $specifier);
              }
            }
            // After deleting the field schema skip to the next table.
            break;
          }
        }
      }
    }
  }

  /**
   * Returns the schema for a single field definition.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The storage definition of the field whose schema has to be returned.
   * @param string[] $column_mapping
   *   A mapping of field column names to database column names.
   *
   * @return array
   *   The schema definition for the field with the following keys:
   *   - fields: The schema definition for the each field columns.
   *   - indexes: The schema definition for the indexes.
   *   - unique keys: The schema definition for the unique keys.
   *   - foreign keys: The schema definition for the foreign keys.
   *
   * @throws \Drupal\Core\Field\FieldException
   *   Exception thrown if the schema contains reserved column names.
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, array $column_mapping) {
    $schema = array();
    $field_schema = $storage_definition->getSchema();

    // Check that the schema does not include forbidden column names.
    if (array_intersect(array_keys($field_schema['columns']), $this->storage->getTableMapping()->getReservedColumns())) {
      throw new FieldException(format_string('Illegal field column names on @field_name', array('@field_name' => $storage_definition->getName())));
    }

    $field_name = $storage_definition->getName();
    $field_description = $storage_definition->getDescription();

    foreach ($column_mapping as $field_column_name => $schema_field_name) {
      $column_schema = $field_schema['columns'][$field_column_name];

      $schema['fields'][$schema_field_name] = $column_schema;
      $schema['fields'][$schema_field_name]['description'] = $field_description;
      // Only entity keys are required.
      $keys = $this->entityType->getKeys() + array('langcode' => 'langcode');
      // The label is an entity key, but label fields are not necessarily
      // required.
      // Because entity ID and revision ID are both serial fields in the base
      // and revision table respectively, the revision ID is not known yet, when
      // inserting data into the base table. Instead the revision ID in the base
      // table is updated after the data has been inserted into the revision
      // table. For this reason the revision ID field cannot be marked as NOT
      // NULL.
      unset($keys['label'], $keys['revision']);
      // Key fields may not be NULL.
      if (in_array($field_name, $keys)) {
        $schema['fields'][$schema_field_name]['not null'] = TRUE;
      }
    }

    if (!empty($field_schema['indexes'])) {
      $schema['indexes'] = $this->getFieldIndexes($field_name, $field_schema, $column_mapping);
    }

    if (!empty($field_schema['unique keys'])) {
      $schema['unique keys'] = $this->getFieldUniqueKeys($field_name, $field_schema, $column_mapping);
    }

    if (!empty($field_schema['foreign keys'])) {
      $schema['foreign keys'] = $this->getFieldForeignKeys($field_name, $field_schema, $column_mapping);
    }

    return $schema;
  }

  /**
   * Returns an index schema array for a given field.
   *
   * @param string $field_name
   *   The name of the field.
   * @param array $field_schema
   *   The schema of the field.
   * @param string[] $column_mapping
   *   A mapping of field column names to database column names.
   *
   * @return array
   *   The schema definition for the indexes.
   */
  protected function getFieldIndexes($field_name, array $field_schema, array $column_mapping) {
    return $this->getFieldSchemaData($field_name, $field_schema, $column_mapping, 'indexes');
  }

  /**
   * Returns a unique key schema array for a given field.
   *
   * @param string $field_name
   *   The name of the field.
   * @param array $field_schema
   *   The schema of the field.
   * @param string[] $column_mapping
   *   A mapping of field column names to database column names.
   *
   * @return array
   *   The schema definition for the unique keys.
   */
  protected function getFieldUniqueKeys($field_name, array $field_schema, array $column_mapping) {
    return $this->getFieldSchemaData($field_name, $field_schema, $column_mapping, 'unique keys');
  }

  /**
   * Returns field schema data for the given key.
   *
   * @param string $field_name
   *   The name of the field.
   * @param array $field_schema
   *   The schema of the field.
   * @param string[] $column_mapping
   *   A mapping of field column names to database column names.
   * @param string $schema_key
   *   The type of schema data. Either 'indexes' or 'unique keys'.
   *
   * @return array
   *   The schema definition for the specified key.
   */
  protected function getFieldSchemaData($field_name, array $field_schema, array $column_mapping, $schema_key) {
    $data = array();

    foreach ($field_schema[$schema_key] as $key => $columns) {
      // To avoid clashes with entity-level indexes or unique keys we use
      // "{$entity_type_id}_field__" as a prefix instead of just
      // "{$entity_type_id}__". We additionally namespace the specifier by the
      // field name to avoid clashes when multiple fields of the same type are
      // added to an entity type.
      $entity_type_id = $this->entityType->id();
      $real_key = $this->getFieldSchemaIdentifierName($entity_type_id, $field_name, $key);
      foreach ($columns as $column) {
        // Allow for indexes and unique keys to specified as an array of column
        // name and length.
        if (is_array($column)) {
          list($column_name, $length) = $column;
          $data[$real_key][] = array($column_mapping[$column_name], $length);
        }
        else {
          $data[$real_key][] = $column_mapping[$column];
        }
      }
    }

    return $data;
  }

  /**
   * Generates a safe schema identifier (name of an index, column name etc.).
   *
   * @param string $entity_type_id
   *   The ID of the entity type.
   * @param string $field_name
   *   The name of the field.
   * @param string $key
   *   The key of the field.
   *
   * @return string
   *   The field identifier name.
   */
  protected function getFieldSchemaIdentifierName($entity_type_id, $field_name, $key) {
    $real_key = "{$entity_type_id}_field__{$field_name}__{$key}";
    // Limit the string to 48 characters, keeping a 16 characters margin for db
    // prefixes.
    if (strlen($real_key) > 48) {
      // Use a shorter separator, a truncated entity_type, and a hash of the
      // field name.
      // Truncate to the same length for the current and revision tables.
      $entity_type = substr($entity_type_id, 0, 36);
      $field_hash = substr(hash('sha256', $real_key), 0, 10);
      $real_key = $entity_type . '__' . $field_hash;
    }
    return $real_key;
  }

  /**
   * Returns field foreign keys.
   *
   * @param string $field_name
   *   The name of the field.
   * @param array $field_schema
   *   The schema of the field.
   * @param string[] $column_mapping
   *   A mapping of field column names to database column names.
   *
   * @return array
   *   The schema definition for the foreign keys.
   */
  protected function getFieldForeignKeys($field_name, array $field_schema, array $column_mapping) {
    $foreign_keys = array();

    foreach ($field_schema['foreign keys'] as $specifier => $specification) {
      // To avoid clashes with entity-level foreign keys we use
      // "{$entity_type_id}_field__" as a prefix instead of just
      // "{$entity_type_id}__". We additionally namespace the specifier by the
      // field name to avoid clashes when multiple fields of the same type are
      // added to an entity type.
      $entity_type_id = $this->entityType->id();
      $real_specifier = "{$entity_type_id}_field__{$field_name}__{$specifier}";
      $foreign_keys[$real_specifier]['table'] = $specification['table'];
      foreach ($specification['columns'] as $column => $referenced) {
        $foreign_keys[$real_specifier]['columns'][$column_mapping[$column]] = $referenced;
      }
    }

    return $foreign_keys;
  }

  /**
   * Returns the schema for the 'default_langcode' metadata field.
   *
   * @param array $schema
   *   The table schema to add the field schema to, passed by reference.
   *
   * @return array
   *   A schema field array for the 'default_langcode' metadata field.
   */
  protected function addDefaultLangcodeSchema(&$schema) {
    $schema['fields']['default_langcode'] =  array(
      'description' => 'Boolean indicating whether field values are in the default entity language.',
      'type' => 'int',
      'size' => 'tiny',
      'not null' => TRUE,
      'default' => 1,
    );
  }


  /**
   * Returns the SQL schema for a dedicated table.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage definition.
   *
   * @return array
   *   The same as a hook_schema() implementation for the data and the
   *   revision tables.
   *
   * @throws \Drupal\Core\Field\FieldException
   *   Exception thrown if the schema contains reserved column names.
   *
   * @see hook_schema()
   */
  protected function getDedicatedTableSchema(FieldStorageDefinitionInterface $storage_definition) {
    $description_current = "Data storage for {$storage_definition->getTargetEntityTypeId()} field {$storage_definition->getName()}.";
    $description_revision = "Revision archive storage for {$storage_definition->getTargetEntityTypeId()} field {$storage_definition->getName()}.";

    $id_definition = $this->fieldStorageDefinitions[$this->entityType->getKey('id')];
    if ($id_definition->getType() == 'integer') {
      $id_schema = array(
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'description' => 'The entity id this data is attached to',
      );
    }
    else {
      $id_schema = array(
        'type' => 'varchar',
        'length' => 128,
        'not null' => TRUE,
        'description' => 'The entity id this data is attached to',
      );
    }

    // Define the revision ID schema, default to integer if there is no revision
    // ID.
    // @todo Revisit this code: the revision id should match the entity id type
    //   if revisions are not supported.
    $revision_id_definition = $this->entityType->isRevisionable() ? $this->fieldStorageDefinitions[$this->entityType->getKey('revision')] : NULL;
    if (!$revision_id_definition || $revision_id_definition->getType() == 'integer') {
      $revision_id_schema = array(
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => FALSE,
        'description' => 'The entity revision id this data is attached to, or NULL if the entity type is not versioned',
      );
    }
    else {
      $revision_id_schema = array(
        'type' => 'varchar',
        'length' => 128,
        'not null' => FALSE,
        'description' => 'The entity revision id this data is attached to, or NULL if the entity type is not versioned',
      );
    }

    $data_schema = array(
      'description' => $description_current,
      'fields' => array(
        'bundle' => array(
          'type' => 'varchar',
          'length' => 128,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The field instance bundle to which this row belongs, used when deleting a field instance',
        ),
        'deleted' => array(
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
          'description' => 'A boolean indicating whether this data item has been deleted'
        ),
        'entity_id' => $id_schema,
        'revision_id' => $revision_id_schema,
        'langcode' => array(
          'type' => 'varchar',
          'length' => 32,
          'not null' => TRUE,
          'default' => '',
          'description' => 'The language code for this data item.',
        ),
        'delta' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'description' => 'The sequence number for this data item, used for multi-value fields',
        ),
      ),
      'primary key' => array('entity_id', 'deleted', 'delta', 'langcode'),
      'indexes' => array(
        'bundle' => array('bundle'),
        'deleted' => array('deleted'),
        'entity_id' => array('entity_id'),
        'revision_id' => array('revision_id'),
        'langcode' => array('langcode'),
      ),
    );

    // Check that the schema does not include forbidden column names.
    $schema = $storage_definition->getSchema();
    $table_mapping = $this->storage->getTableMapping();
    if (array_intersect(array_keys($schema['columns']), $table_mapping->getReservedColumns())) {
      throw new FieldException(format_string('Illegal field column names on @field_name', array('@field_name' => $storage_definition->getName())));
    }

    // Add field columns.
    foreach ($schema['columns'] as $column_name => $attributes) {
      $real_name = $table_mapping->getFieldColumnName($storage_definition, $column_name);
      $data_schema['fields'][$real_name] = $attributes;
    }

    // Add indexes.
    foreach ($schema['indexes'] as $index_name => $columns) {
      $real_name = $this->getFieldIndexName($storage_definition, $index_name);
      foreach ($columns as $column_name) {
        // Indexes can be specified as either a column name or an array with
        // column name and length. Allow for either case.
        if (is_array($column_name)) {
          $data_schema['indexes'][$real_name][] = array(
            $table_mapping->getFieldColumnName($storage_definition, $column_name[0]),
            $column_name[1],
          );
        }
        else {
          $data_schema['indexes'][$real_name][] = $table_mapping->getFieldColumnName($storage_definition, $column_name);
        }
      }
    }

    // Add foreign keys.
    foreach ($schema['foreign keys'] as $specifier => $specification) {
      $real_name = $this->getFieldIndexName($storage_definition, $specifier);
      $data_schema['foreign keys'][$real_name]['table'] = $specification['table'];
      foreach ($specification['columns'] as $column_name => $referenced) {
        $sql_storage_column = $table_mapping->getFieldColumnName($storage_definition, $column_name);
        $data_schema['foreign keys'][$real_name]['columns'][$sql_storage_column] = $referenced;
      }
    }

    // Construct the revision table.
    $revision_schema = $data_schema;
    $revision_schema['description'] = $description_revision;
    $revision_schema['primary key'] = array('entity_id', 'revision_id', 'deleted', 'delta', 'langcode');
    $revision_schema['fields']['revision_id']['not null'] = TRUE;
    $revision_schema['fields']['revision_id']['description'] = 'The entity revision id this data is attached to';

    return array(
      $table_mapping->getDedicatedDataTableName($storage_definition) => $data_schema,
      $table_mapping->getDedicatedRevisionTableName($storage_definition) => $revision_schema,
    );
  }

  /**
   * Adds defaults to a table schema definition.
   *
   * @param $schema
   *   The schema definition array for a single table, passed by reference.
   */
  protected function addTableDefaults(&$schema) {
    $schema += array(
      'fields' => array(),
      'unique keys' => array(),
      'indexes' => array(),
      'foreign keys' => array(),
    );
  }

  /**
   * Returns the name to be used for the given entity index.
   *
   * @param \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type
   *   The entity type.
   * @param string $index
   *   The index column name.
   *
   * @return string
   *   The index name.
   */
  protected function getEntityIndexName(ContentEntityTypeInterface $entity_type, $index) {
    return $entity_type->id() . '__' . $index;
  }

  /**
   * Generates an index name for a field data table.
   *
   * @private Calling this function circumvents the entity system and is
   * strongly discouraged. This function is not considered part of the public
   * API and modules relying on it might break even in minor releases.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition
   *   The field storage definition.
   * @param string $index
   *   The name of the index.
   *
   * @return string
   *   A string containing a generated index name for a field data table that is
   *   unique among all other fields.
   */
  protected function getFieldIndexName(FieldStorageDefinitionInterface $storage_definition, $index) {
    return $storage_definition->getName() . '_' . $index;
  }

  /**
   * Processes the specified entity key.
   *
   * @param array $schema
   *   The table schema, passed by reference.
   * @param string $key
   *   The entity key name.
   */
  protected function processIdentifierSchema(&$schema, $key) {
    if ($schema['fields'][$key]['type'] == 'int') {
      $schema['fields'][$key]['type'] = 'serial';
    }
    unset($schema['fields'][$key]['default']);
  }

}
