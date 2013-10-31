<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\IdMapInterface.
 */

namespace Drupal\migrate\Plugin;

use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Row;

/**
 * An interface for migrate id mappings.
 *
 * Migrate id mappings maintain a relation between source ID and
 * destination ID for audit and rollback purposes.
 */
interface MigrateIdMapInterface {

  /**
   * Codes reflecting the current status of a map row.
   */
  const STATUS_IMPORTED = 0;
  const STATUS_NEEDS_UPDATE = 1;
  const STATUS_IGNORED = 2;
  const STATUS_FAILED = 3;

  /**
   * Codes reflecting how to handle the destination item on rollback.
   *
   */
  const ROLLBACK_DELETE = 0;
  const ROLLBACK_PRESERVE = 1;

 /**
   * Save a mapping from the source identifiers to the destination
   * identifiers.
   *
   * @param $row
   *    The row containing the current values of the source identifiers.
   * @param $destination_ids
   *   An array of destination identifiers: the keys are the name of the
   *   properties, the values are dependent on the actual mapping
   *   implementation.
   * @param $status
   * @param $rollback_action
   */
  public function saveIDMapping(Row $row, array $destination_ids, $status = self::STATUS_IMPORTED, $rollback_action = self::ROLLBACK_DELETE);

  /**
   * Record a message related to a source record
   *
   * @param array $source_key
   *  Source ID of the record in error
   * @param string $message
   *  The message to record.
   * @param int $level
   *  Optional message severity (defaults to MESSAGE_ERROR).
   */
  public function saveMessage(array $source_key, $message, $level = MigrationInterface::MESSAGE_ERROR);

  /**
   * Prepare to run a full update - mark all previously-imported content as
   * ready to be re-imported.
   */
  public function prepareUpdate();

  /**
   * Report the number of processed items in the map
   */
  public function processedCount();

  /**
   * Report the number of imported items in the map
   */
  public function importedCount();

  /**
   * Report the number of items that failed to import
   */
  public function errorCount();

  /**
   * Report the number of messages
   */
  public function messageCount();

  /**
   * Delete the map and message entries for a given source record
   *
   * @param array $source_key
   */
  public function delete(array $source_key, $messages_only = FALSE);

  /**
   * Delete the map and message entries for a given destination record
   *
   * @param array $destination_key
   */
  public function deleteDestination(array $destination_key);

  /**
   * Delete the map and message entries for a set of given source records.
   *
   * @param array $source_ids
   */
  public function deleteBulk(array $source_ids);

  /**
   * Clear all messages from the map.
   */
  public function clearMessages();

  /**
   * Retrieve map data for a given source or destination item
   */
  public function getRowBySource(array $source_id);
  public function getRowByDestination(array $destination_id);

  /**
   * Retrieve an array of map rows marked as needing update.
   */
  public function getRowsNeedingUpdate($count);

  /**
   * Given a (possibly multi-field) destination key, return the (possibly multi-field)
   * source key mapped to it.
   *
   * @param array $destination_id
   *  Array of destination key values.
   * @return array
   *  Array of source key values, or NULL on failure.
   */
  public function lookupSourceID(array $destination_id);

  /**
   * Given a (possibly multi-field) source key, return the (possibly multi-field)
   * destination key it is mapped to.
   *
   * @param array $source_id
   *  Array of source key values.
   * @return array
   *  Array of destination key values, or NULL on failure.
   */
  public function lookupDestinationID(array $source_id);

  /**
   * Remove any persistent storage used by this map (e.g., map and message tables)
   */
  public function destroy();

  /**
   * @TODO: YUCK THIS IS SQL BOUND!
   */
  public function getQualifiedMapTable();

  public function setMessage(MigrateMessageInterface $message);
}
