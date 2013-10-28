<?php

/**
 * @file
 * Contains \Drupal\migrate\MigrateExecutable.
 */

namespace Drupal\migrate;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\MigrateIdMapInterface;

/**
 * @property mixed processed_since_feedback
 */
class MigrateExecutable {

  /**
   * @var \Drupal\migrate\Entity\MigrationInterface
   */
  protected $migration;
  protected $successes_since_feedback;
  protected $total_successes;
  protected $needsUpdate;
  protected $total_processed;

  public function __construct(MigrationInterface $migration) {
    $this->migration = $migration;
  }

  /**
   * @return \Drupal\migrate\Plugin\MigrateSourceInterface
   */
  public function getSource() {
    return $this->migration->getSource();
  }

  /**
   * @return \Drupal\migrate\Plugin\MigrateDestinationInterface
   */
  public function getDestination() {
    return $this->migration->getDestination();
  }

  /**
   * The rollback action to be saved for the current row.
   *
   * @var int
   */
  public $rollbackAction;

  /**
   * An array of counts. Initially used for cache hit/miss tracking.
   *
   * @var array
   */
  protected $counts = array();

  /**
   * When performing a bulkRollback(), the maximum number of items to pass in
   * a single call. Can be overridden in derived class constructor.
   *
   * @var int
   */
  protected $rollbackBatchSize = 50;

  /**
   * If present, an array with keys name and alias (optional). Name refers to
   * the source properties used for tracking highwater marks. alias is an
   * optional table alias.
   *
   * @var array
   */
  protected $highwaterField = array();
  public function getHighwaterField() {
    return $this->highwaterField;
  }
  public function setHighwaterField(array $highwater_field) {
    $this->highwaterField = $highwater_field;
  }

  /**
   * The object currently being constructed
   * @var \stdClass
   */
  protected $destinationValues;

  /**
   * The current data row retrieved from the source.
   * @var \stdClass
   */
  protected $sourceValues;

  /**
   * Perform an import operation - migrate items from source to destination.
   */
  protected function import() {
    $return = MigrationInterface::RESULT_COMPLETED;
    $source = $this->getSource();
    $destination = $this->getDestination();

    try {
      $source->rewind();
    }
    catch (\Exception $e) {
      self::displayMessage(
        t('Migration failed with source plugin exception: !e',
          array('!e' => $e->getMessage())));
      return MigrationInterface::RESULT_FAILED;
    }
    while ($this->getSource()->valid()) {
      /** @var Row $row */
      $row = $source->current();

      // Wipe old messages, and save any new messages.
      $this->migration->getIdMap()->delete($this->currentSourceIds(), TRUE);
      $this->saveQueuedMessages();

      $this->processRow($row);

      try {
        $ids = $this->getDestination()->import($row);
        if ($ids) {
          $this->migration->getIdMap()->saveIDMapping($row->getSource(), $ids,
            $this->needsUpdate, $this->rollbackAction,
            $row->getHash());
          $this->successes_since_feedback++;
          $this->total_successes++;
        }
        else {
          $this->migration->getIdMap()->saveIDMapping($row->getSource(), array(),
            MigrateIdMapInterface::STATUS_FAILED, $this->rollbackAction,
            $row->getHash());
          if ($this->migration->getIdMap()->messageCount() == 0) {
            $message = t('New object was not saved, no error provided');
            $this->saveMessage($message);
            self::displayMessage($message);
          }
        }
      }
      catch (\MigrateException $e) {
        $this->migration->getIdMap()->saveIDMapping($row->getSource(), array(),
          $e->getStatus(), $this->rollbackAction, $row->getHash());
        $this->saveMessage($e->getMessage(), $e->getLevel());
        self::displayMessage($e->getMessage());
      }
      catch (\Exception $e) {
        $this->migration->getIdMap()->saveIDMapping($row->getSource(), array(),
          MigrateIdMapInterface::STATUS_FAILED, $this->rollbackAction,
          $row->getHash());
        $this->handleException($e);
      }
      $this->total_processed++;
      $this->processed_since_feedback++;
      if ($this->highwaterField) {
        $this->saveHighwater($row->getSourceProperty($this->highwaterField['name']));
      }

      // Reset row properties.
      unset($sourceValues, $destinationValues);
      $this->needsUpdate = MigrateIdMapInterface::STATUS_IMPORTED;

      // TODO: Temporary. Remove when http://drupal.org/node/375494 is committed.
      // TODO: Should be done in MigrateDestinationEntity
      if (!empty($destination->entityType)) {
        entity_get_controller($destination->entityType)->resetCache();
      }

      if ($this->timeOptionExceeded()) {
        break;
      }
      if (($return = $this->checkStatus()) != MigrationInterface::RESULT_COMPLETED) {
        break;
      }
      if ($this->itemOptionExceeded()) {
        break;
      }
      try {
        $source->next();
      }
      catch (\Exception $e) {
        self::displayMessage(
          t('Migration failed with source plugin exception: !e',
            array('!e' => $e->getMessage())));
        return MigrationInterface::RESULT_FAILED;
      }
    }

    $this->progressMessage($return);

    return $return;
  }

  /**
   * Fetch the key array for the current source record.
   *
   * @return array
   */
  protected function currentSourceIds() {
    return $this->getSource()->getCurrentIds();
  }

  /**
   * Test whether we've exceeded the designated time limit.
   *
   * @return boolean
   *  TRUE if the threshold is exceeded, FALSE if not.
   */
  protected function timeOptionExceeded() {
    if (!$timelimit = $this->getTimeLimit()) {
      return FALSE;
    }
    $time_elapsed = time() - REQUEST_TIME;
    if ($time_elapsed >= $timelimit) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  public function getTimeLimit() {
    if (isset($this->options['limit']) &&
        ($this->options['limit']['unit'] == 'seconds' || $this->options['limit']['unit'] == 'second')) {
      return $this->options['limit']['value'];
    }
    else {
      return NULL;
    }
  }
}
