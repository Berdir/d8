<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Plugin;


interface MigrateMapInterface {
  // Status of each processed row
  const STATUS_IMPORTED = 0;
  const STATUS_NEEDS_UPDATE = 1;
  const STATUS_IGNORED = 2;
  const STATUS_FAILED = 3;
  // Whether to delete or preserve the destination data on rollback.
  const ROLLBACK_DELETE = 0;
  const ROLLBACK_PRESERVE = 1;
  public function __construct(array $options);
  public function getSourceKey();
  public function getDestinationKey();
  // WTF: Why the full row and not the key?
  public function saveIDMapping(stdClass $row, array $dest_ids,
    $status = MigrateMapInterface::STATUS_IMPORTED,
    $rollback_action = MigrateMapInterface::ROLLBACK_DELETE, $hash = NULL);
  public function saveMessage($source_key, $message,
    $level = MigrationInterface::MESSAGE_ERROR);
  public function prepareUpdate(); // Contrib?
  public function delete(array $source_key, $messages_only = FALSE);
  public function deleteByDestination(array $destination_key);
  public function deleteBulk(array $source_keys);
  public function clearMessages();
  public function getMapBySource(array $source_id);
  public function getMapByDestination(array $destination_id);
  public function getRowsNeedingUpdate($count);
  public function lookupSourceID($destination_id);
  public function lookupDestinationID($source_id);
  public function destroy();
  // Statistics
  public function processedCount();
  public function importedCount();
  public function errorCount();
  public function messageCount();
}
