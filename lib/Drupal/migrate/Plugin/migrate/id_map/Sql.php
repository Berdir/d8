<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\id_map\Sql.
 */

namespace Drupal\migrate\Plugin\migrate\id_map;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Row;

/**
 * Defines the sql based ID map implementation.
 *
 * It creates one map and one message table per migration entity to store the
 * relevant information.
 *
 * @PluginID("sql")
 */
class Sql extends PluginBase implements MigrateIdMapInterface {

  /**
   * Names of tables created for tracking the migration.
   *
   * @var string
   */
  protected $mapTable, $messageTable;

  /**
   * The migrate message.
   *
   * @var \Drupal\migrate\MigrateMessageInterface
   */
  protected $message;

  /**
   * The database connection for the map/message tables on the destination.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Database\Query\SelectInterface
   */
  protected $query;

  /**
   * The migration being done.
   *
   * @var \Drupal\migrate\Entity\MigrationInterface
   */
  protected $migration;

  /**
   * The source ID fields.
   *
   * @var array
   */
  protected $sourceIdFields;

  /**
   * The destination ID fields.
   *
   * @var array
   */
  protected $destinationIdFields;

  /**
   * Stores whether the the tables (map/message) already exist.
   *
   * This is determined just once per request/instance of the class.
   *
   * @var boolean
   */
  protected $ensured;

  /**
   * The result.
   *
   * @var null
   */
  protected $result = NULL;

  /**
   * The current row.
   *
   * @var null
   */
  protected $currentRow = NULL;

  /**
   * The current key.
   *
   * @var array
   */
  protected $currentKey = array();

  /**
   * Constructs an SQL object.
   *
   * Sets up the tables and builds the maps,
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin ID for the migration process to do.
   * @param array $plugin_definition
   *   The configuration for the plugin.
   * @param \Drupal\migrate\Entity\MigrationInterface $migration
   *   The migration to do.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->migration = $migration;
    $machine_name = $migration->id();

    // Default generated table names, limited to 63 characters.
    $prefixLength = strlen($this->getDatabase()->tablePrefix()) ;
    $this->mapTable = 'migrate_map_' . Unicode::strtolower($machine_name);
    $this->mapTable = Unicode::substr($this->mapTable, 0, 63 - $prefixLength);
    $this->messageTable = 'migrate_message_' . Unicode::strtolower($machine_name);
    $this->messageTable = Unicode::substr($this->messageTable, 0, 63 - $prefixLength);
    $this->sourceIds = $migration->get('sourceIds');
    $this->destinationIds = $migration->get('destinationIds');

    // Build the source and destination identifier maps.
    $this->sourceIdFields = array();
    $count = 1;
    foreach ($this->sourceIds as $field => $schema) {
      $this->sourceIdFields[$field] = 'sourceid' . $count++;
    }
    $this->destinationIdFields = array();
    $count = 1;
    foreach ($this->destinationIds as $field => $schema) {
      $this->destinationIdFields[$field] = 'destid' . $count++;
    }
    $this->ensureTables();
  }

  /**
   * Gets the name of the map table.
   *
   * @return string
   *   The name of the map table.
   */
  public function getMapTable() {
    return $this->mapTable;
  }

  /**
   * Gets the name of the message table.
   *
   * @return string
   *   The name of the message table.
   */
  public function getMessageTable() {
    return $this->messageTable;
  }

  /**
   * Returns the current key.
   *
   * @return array
   *   The current key.
   */
  public function getCurrentKey() {
    return $this->currentKey;
  }

  /**
   * Qualifying the map table name with the database name makes cross-db joins
   * possible. Note that, because prefixes are applied after we do this (i.e.,
   * it will prefix the string we return), we do not qualify the table if it has
   * a prefix. This will work fine when the source data is in the default
   * (prefixed) database (in particular, for simpletest), but not if the primary
   * query is in an external database.
   *
   * @return string
   */
  public function getQualifiedMapTable() {
    $options = $this->getDatabase()->getConnectionOptions();
    $prefix = $this->getDatabase()->tablePrefix($this->mapTable);
    if ($prefix) {
      return $this->mapTable;
    }
    else {
      return $options['database'] . '.' . $this->mapTable;
    }
  }

  /**
   * Gets the database connection.
   *
   * @return \Drupal\Core\Database\Connection
   */
  protected function getDatabase() {
    return SqlBase::getDatabaseConnection($this->migration->id(), $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function setMessage(MigrateMessageInterface $message) {
    $this->message = $message;
  }

  /**
   * Create the map and message tables if they don't already exist.
   */
  protected function ensureTables() {
    if (!$this->ensured) {
      if (!$this->getDatabase()->schema()->tableExists($this->mapTable)) {
        // Generate appropriate schema info for the map and message tables,
        // and map from the source field names to the map/msg field names.
        $count = 1;
        $source_id_schema = array();
        $pks = array();
        foreach ($this->sourceIds as $field_schema) {
          $mapkey = 'sourceid' . $count++;
          $source_id_schema[$mapkey] = $field_schema;
          $pks[] = $mapkey;
        }

        $fields = $source_id_schema;

        // Add destination identifiers to map table.
        // TODO: How do we discover the destination schema?
        $count = 1;
        foreach ($this->destinationIds as $field_schema) {
          // Allow dest identifier fields to be NULL (for IGNORED/FAILED
          // cases).
          $field_schema['not null'] = FALSE;
          $mapkey = 'destid' . $count++;
          $fields[$mapkey] = $field_schema;
        }
        $fields['needs_update'] = array(
          'type' => 'int',
          'size' => 'tiny',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => MigrateIdMapInterface::STATUS_IMPORTED,
          'description' => 'Indicates current status of the source row',
        );
        $fields['rollback_action'] = array(
          'type' => 'int',
          'size' => 'tiny',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => MigrateIdMapInterface::ROLLBACK_DELETE,
          'description' => 'Flag indicating what to do for this item on rollback',
        );
        $fields['last_imported'] = array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'UNIX timestamp of the last time this row was imported',
        );
        $fields['hash'] = array(
          'type' => 'varchar',
          'length' => '32',
          'not null' => FALSE,
          'description' => 'Hash of source row data, for detecting changes',
        );
        $schema = array(
          'description' => t('Mappings from source identifier value(s) to destination identifier value(s).'),
          'fields' => $fields,
        );
        if ($pks) {
          $schema['primary key'] = $pks;
        }
        $this->getDatabase()->schema()->createTable($this->mapTable, $schema);

        // Now do the message table.
        $fields = array();
        $fields['msgid'] = array(
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        );
        $fields += $source_id_schema;

        $fields['level'] = array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 1,
        );
        $fields['message'] = array(
          'type' => 'text',
          'size' => 'medium',
          'not null' => TRUE,
        );
        $schema = array(
          'description' => t('Messages generated during a migration process'),
          'fields' => $fields,
          'primary key' => array('msgid'),
        );
        if ($pks) {
          $schema['indexes']['sourcekey'] = $pks;
        }
        $this->getDatabase()->schema()->createTable($this->messageTable, $schema);
      }
      else {
        // Add any missing columns to the map table.
        if (!$this->getDatabase()->schema()->fieldExists($this->mapTable,
                                                      'rollback_action')) {
          $this->getDatabase()->schema()->addField($this->mapTable,
                                                'rollback_action', array(
            'type' => 'int',
            'size' => 'tiny',
            'unsigned' => TRUE,
            'not null' => TRUE,
            'default' => 0,
            'description' => 'Flag indicating what to do for this item on rollback',
          ));
        }
        if (!$this->getDatabase()->schema()->fieldExists($this->mapTable, 'hash')) {
          $this->getDatabase()->schema()->addField($this->mapTable, 'hash', array(
            'type' => 'varchar',
            'length' => '32',
            'not null' => FALSE,
            'description' => 'Hash of source row data, for detecting changes',
          ));
        }
      }
      $this->ensured = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRowBySource(array $source_ids) {
    $query = $this->getDatabase()->select($this->mapTable, 'map')
              ->fields('map');
    foreach ($this->sourceIdFields as $id_name) {
      $query = $query->condition("map.$id_name", array_shift($source_ids), '=');
    }
    $result = $query->execute();
    return $result->fetchAssoc();
  }

  /**
   * {@inheritdoc}
   */
  public function getRowByDestination(array $destination_ids) {
    $query = $this->getDatabase()->select($this->mapTable, 'map')
              ->fields('map');
    foreach ($this->destinationIdFields as $id_name) {
      $query = $query->condition("map.$id_name", array_shift($destination_ids), '=');
    }
    $result = $query->execute();
    return $result->fetchAssoc();
  }

  /**
   * {@inheritdoc}
   */
  public function getRowsNeedingUpdate($count) {
    $rows = array();
    $result = $this->getDatabase()->select($this->mapTable, 'map')
                      ->fields('map')
                      ->condition('needs_update', MigrateIdMapInterface::STATUS_NEEDS_UPDATE)
                      ->range(0, $count)
                      ->execute();
    foreach ($result as $row) {
      $rows[] = $row;
    }
    return $rows;
  }

  /**
   * {@inheritdoc}
   */
  public function lookupSourceID(array $destination_id) {
    $query = $this->getDatabase()->select($this->mapTable, 'map')
              ->fields('map', $this->sourceIdFields);
    foreach ($this->destinationIdFields as $key_name) {
      $query = $query->condition("map.$key_name", array_shift($destination_id), '=');
    }
    $result = $query->execute();
    $source_id = $result->fetchAssoc();
    return $source_id;
  }

  /**
   * {@inheritdoc}
   */
  public function lookupDestinationID(array $source_id) {
    $query = $this->getDatabase()->select($this->mapTable, 'map')
              ->fields('map', $this->destinationIdFields);
    foreach ($this->sourceIdFields as $key_name) {
      $query = $query->condition("map.$key_name", array_shift($source_id), '=');
    }
    $result = $query->execute();
    $destination_id = $result->fetchAssoc();
    return $destination_id;
  }

  /**
   * {@inheritdoc}
   */
  public function saveIdMapping(Row $row, array $destination_id_values, $needs_update = MigrateIdMapInterface::STATUS_IMPORTED, $rollback_action = MigrateIdMapInterface::ROLLBACK_DELETE) {
    // Construct the source key.
    $keys = array();
    $destination = $row->getDestination();
    foreach ($this->sourceIdFields as $field_name => $key_name) {
      // A NULL key value will fail.
      if (!isset($destination[$field_name])) {
        $this->message->display(t(
          'Could not save to map table due to NULL value for key field !field',
          array('!field' => $field_name)));
        return;
      }
      $keys[$key_name] = $destination[$field_name];
    }

    $fields = array(
      'needs_update' => (int) $needs_update,
      'rollback_action' => (int) $rollback_action,
      'hash' => $row->getHash(),
    );
    $count = 1;
    foreach ($destination_id_values as $dest_id) {
      $fields['destid' . $count++] = $dest_id;
    }
    if ($this->migration->get('trackLastImported')) {
      $fields['last_imported'] = time();
    }
    if ($keys) {
      $this->getDatabase()->merge($this->mapTable)
        ->key($keys)
        ->fields($fields)
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function saveMessage(array $source_id_values, $message, $level = MigrationInterface::MESSAGE_ERROR) {
    $count = 1;
    foreach ($source_id_values as $id_value) {
      $fields['sourceid' . $count++] = $id_value;
      // If any key value is empty, we can't save - print out and abort.
      if (empty($id_value)) {
        print($message);
        return;
      }
    }
    $fields['level'] = $level;
    $fields['message'] = $message;
    $this->getDatabase()->insert($this->messageTable)
      ->fields($fields)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function prepareUpdate() {
    $this->getDatabase()->update($this->mapTable)
    ->fields(array('needs_update' => MigrateIdMapInterface::STATUS_NEEDS_UPDATE))
    ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function processedCount() {
    $query = $this->getDatabase()->select($this->mapTable);
    $query->addExpression('COUNT(*)', 'count');
    $count = $query->execute()->fetchField();
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function importedCount() {
    $query = $this->getDatabase()->select($this->mapTable);
    $query->addExpression('COUNT(*)', 'count');
    $query->condition('needs_update', array(MigrateIdMapInterface::STATUS_IMPORTED, MigrateIdMapInterface::STATUS_NEEDS_UPDATE), 'IN');
    $count = $query->execute()->fetchField();
    return $count;
  }

  /**
   * Returns a count of items which are marked as needing update.
   *
   * @return int
   *   The number of items which need updating.
   */
  public function updateCount() {
    $query = $this->getDatabase()->select($this->mapTable);
    $query->addExpression('COUNT(*)', 'count');
    $query->condition('needs_update', MigrateIdMapInterface::STATUS_NEEDS_UPDATE);
    $count = $query->execute()->fetchField();
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function errorCount() {
    $query = $this->getDatabase()->select($this->mapTable);
    $query->addExpression('COUNT(*)', 'count');
    $query->condition('needs_update', MigrateIdMapInterface::STATUS_FAILED);
    $count = $query->execute()->fetchField();
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function messageCount() {
    $query = $this->getDatabase()->select($this->messageTable);
    $query->addExpression('COUNT(*)', 'count');
    $count = $query->execute()->fetchField();
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $source_ids, $messages_only = FALSE) {
    if (!$messages_only) {
      $map_query = $this->getDatabase()->delete($this->mapTable);
    }
    $message_query = $this->getDatabase()->delete($this->messageTable);
    $count = 1;
    foreach ($source_ids as $key_value) {
      if (!$messages_only) {
        $map_query->condition('sourceid' . $count, $key_value);
      }
      $message_query->condition('sourceid' . $count, $key_value);
      $count++;
    }

    if (!$messages_only) {
      $map_query->execute();
    }
    $message_query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDestination(array $destination_id) {
    $map_query = $this->getDatabase()->delete($this->mapTable);
    $message_query = $this->getDatabase()->delete($this->messageTable);
    $source_id = $this->lookupSourceID($destination_id);
    if (!empty($source_id)) {
      $count = 1;
      foreach ($destination_id as $key_value) {
        $map_query->condition('destid' . $count, $key_value);
        $count++;
      }
      $map_query->execute();
      $count = 1;
      foreach ($source_id as $key_value) {
        $message_query->condition('sourceid' . $count, $key_value);
        $count++;
      }
      $message_query->execute();
    }
  }

  /**
   * Sets the specified row to be updated, if it exists.
   */
  public function setUpdate(array $source_id) {
    $query = $this->getDatabase()->update($this->mapTable)
                              ->fields(array('needs_update' => MigrateIdMapInterface::STATUS_NEEDS_UPDATE));
    $count = 1;
    foreach ($source_id as $key_value) {
      $query->condition('sourceid' . $count++, $key_value);
    }
    $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteBulk(array $source_ids) {
    // If we have a single-column key, we can shortcut it.
    if (count($this->sourceIds) == 1) {
      $sourceids = array();
      foreach ($source_ids as $source_id) {
        $sourceids[] = $source_id;
      }
      $this->getDatabase()->delete($this->mapTable)
        ->condition('sourceid1', $sourceids, 'IN')
        ->execute();
      $this->getDatabase()->delete($this->messageTable)
        ->condition('sourceid1', $sourceids, 'IN')
        ->execute();
    }
    else {
      foreach ($source_ids as $source_id) {
        $map_query = $this->getDatabase()->delete($this->mapTable);
        $message_query = $this->getDatabase()->delete($this->messageTable);
        $count = 1;
        foreach ($source_id as $key_value) {
          $map_query->condition('sourceid' . $count, $key_value);
          $message_query->condition('sourceid' . $count++, $key_value);
        }
        $map_query->execute();
        $message_query->execute();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearMessages() {
    $this->getDatabase()->truncate($this->messageTable)
                     ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function destroy() {
    $this->getDatabase()->schema()->dropTable($this->mapTable);
    $this->getDatabase()->schema()->dropTable($this->messageTable);
  }

  /**
   * Implementation of Iterator::rewind().
   *
   * This is called before beginning a foreach loop.
   *
   * @todo Support idlist, itemlimit.
   */
  public function rewind() {
    $this->currentRow = NULL;
    $fields = array();
    foreach ($this->sourceIdFields as $field) {
      $fields[] = $field;
    }
    foreach ($this->destinationIdFields as $field) {
      $fields[] = $field;
    }

    // @todo Make this work.
    /*
    if (isset($this->options['itemlimit'])) {
      $query = $query->range(0, $this->options['itemlimit']);
    }
    */
    $this->result = $this->getDatabase()->select($this->mapTable, 'map')
      ->fields('map', $fields)
      ->execute();
    $this->next();
  }

  /**
   * Implementation of Iterator::current().
   *
   * This is called when entering a loop iteration, returning the current row.
   */
  public function current() {
    return $this->currentRow;
  }

  /**
   * Implementation of Iterator::key().
   *
   * This is called when entering a loop iteration, returning the key of the
   * current row. It must be a scalar - we will serialize to fulfill the
   * requirement, but using getCurrentKey() is preferable.
   */
  public function key() {
    return serialize($this->currentKey);
  }

  /**
   * Implementation of Iterator::next().
   *
   * This is called at the bottom of the loop implicitly, as well as explicitly
   * from rewind().
   */
  public function next() {
    $this->currentRow = $this->result->fetchObject();
    $this->currentKey = array();
    if (!is_object($this->currentRow)) {
      $this->currentRow = NULL;
    }
    else {
      foreach ($this->sourceIdFields as $map_field) {
        $this->currentKey[$map_field] = $this->currentRow->$map_field;
        // Leave only destination fields.
        unset($this->currentRow->$map_field);
      }
    }
  }

  /**
   * Implementation of Iterator::valid().
   *
   * This is called at the top of the loop, returning TRUE to process the loop
   * and FALSE to terminate it.
   */
  public function valid() {
    // @todo Check numProcessed against itemlimit.
    return !is_null($this->currentRow);
  }

}
