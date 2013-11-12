<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\MigrateIdMapInterface.
 */

namespace Drupal\migrate\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Row;

/**
 * Defines an interface for migrate ID mappings.
 *
 * Migrate ID mappings maintain a relation between source ID and destination ID
 * for audit and rollback purposes.
 */
interface MigrateIdMapInterface extends PluginInspectionInterface {

  /**
   * Codes reflecting the current status of a map row.
   */
  const STATUS_IMPORTED = 0;
  const STATUS_NEEDS_UPDATE = 1;
  const STATUS_IGNORED = 2;
  const STATUS_FAILED = 3;

  /**
   * Codes reflecting how to handle the destination item on rollback.
   */
  const ROLLBACK_DELETE = 0;
  const ROLLBACK_PRESERVE = 1;

  /**
   * Saves a mapping from the source identifiers to the destination identifiers.
   *
   * @param \Drupal\migrate\Row $row
   *   The current row.
   * @param array $destination_id_values
   *   An array of destination identifier values.
   * @param int $status
   *   The status to save for the mapping.
   * @param int $rollback_action
   *   The way to handle a rollback.
   */
  public function saveIdMapping(Row $row, array $destination_id_values, $status = self::STATUS_IMPORTED, $rollback_action = self::ROLLBACK_DELETE);

  /**
   * Records a message related to a source record.
   *
   * @param array $source_id_values
   *   Source ID of the record in error
   * @param string $message
   *   The message to record.
   * @param int $level
   *   Optional message severity (defaults to MESSAGE_ERROR).
   */
  public function saveMessage(array $source_id_values, $message, $level = MigrationInterface::MESSAGE_ERROR);

  /**
   * Prepares to run a full update.
   *
   * Mark all previously-imported content as ready to be re-imported.
   */
  public function prepareUpdate();

  /**
   * Reports the number of processed items in the map.
   */
  public function processedCount();

  /**
   * Reports the number of imported items in the map.
   */
  public function importedCount();

  /**
   * Reports the number of items that failed to import.
   */
  public function errorCount();

  /**
   * Reports the number of messages.
   */
  public function messageCount();

  /**
   * Deletes the map and message entries for a given source record.
   *
   * @param array $source_id
   *   The ID of the source we should do the delete for.
   * @param bool $messages_only
   *   Flag to only delete the messages.
   */
  public function delete(array $source_id, $messages_only = FALSE);

  /**
   * Deletes the map and message entries for a given destination record.
   *
   * @param array $destination_id
   *   The ID of the destination we should do the deletes for.
   */
  public function deleteDestination(array $destination_id);

  /**
   * Deletes the map and message entries for a set of given source records.
   *
   * @param array $source_ids
   *   The IDs of the sources we should do the deletes for.
   */
  public function deleteBulk(array $source_ids);

  /**
   * Clears all messages from the map.
   */
  public function clearMessages();

  /**
   * Retrieves map data for a given source or destination item.
   */
  public function getRowBySource(array $source_ids);

  /**
   * Retrieves a row by the destination identifiers.
   *
   * @param array $destination_id_values
   *   A list of destination IDs, even there is just one destination ID.
   *
   * @return array
   *   The row(s) of data.
   */
  public function getRowByDestination(array $destination_id_values);

  /**
   * Retrieves an array of map rows marked as needing update.
   *
   * @param int $count
   *   The maximum number of rows to get in the next batch.
   */
  public function getRowsNeedingUpdate($count);

  /**
   * Looks up the source identifier.
   *
   * Given a (possibly multi-field) destination identifier value, return the
   * (possibly multi-field) source identifier value mapped to it.
   *
   * @param array $destination_ids
   *   Array of destination identifier values.
   *
   * @return array
   *   Array of source identifier values, or NULL on failure.
   */
  public function lookupSourceID(array $destination_ids);

  /**
   * Looks up the destination identifier.
   *
   * Given a (possibly multi-field) source identifier value, return the
   * (possibly multi-field) destination identifier value it is mapped to.
   *
   * @param array $source_id_values
   *   Array of source identifier values.
   *
   * @return array
   *   Array of destination identifier values, or NULL on failure.
   */
  public function lookupDestinationID(array $source_id_values);

  /**
   * Removes any persistent storage used by this map.
   *
   * For example, remove the map and message tables.
   */
  public function destroy();

  /**
   * Gets the qualified map table.
   *
   * @todo Change this to not be for SQL only.
   */
  public function getQualifiedMapTable();

  /**
   * Sets the migrate message.
   *
   * @param \Drupal\migrate\MigrateMessageInterface $message
   *   The message to display.
   */
  public function setMessage(MigrateMessageInterface $message);

}
