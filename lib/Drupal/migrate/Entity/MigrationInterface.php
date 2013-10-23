<?php

/**
 * @file
 * Contains \Drupal\migrate\Entity\MigrationInterface.
 */

namespace Drupal\migrate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Interface for migrations.
 */
interface MigrationInterface extends ConfigEntityInterface {
  const SOURCE = 'source';
  const DESTINATION = 'destination';
  /**
   * Codes representing the current status of a migration, and stored in the
   * migrate_status table.
   */
  const STATUS_IDLE = 0;
  const STATUS_IMPORTING = 1;
  const STATUS_ROLLING_BACK = 2;
  const STATUS_STOPPING = 3;
  const STATUS_DISABLED = 4;

  const MESSAGE_ERROR = 1;

  /**
   * @return \Drupal\migrate\Plugin\MigrateSourceInterface
   */
  public function getSource();

  /**
   * @return \Drupal\migrate\Plugin\MigrateProcessBag
   */
  public function getProcess();

  /**
   * @return \Drupal\migrate\Plugin\MigrateDestinationInterface
   */
  public function getDestination();

  /**
   *@return \Drupal\migrate\Plugin\MigrateIdMapInterface
   */
  public function getIdMap();
}
