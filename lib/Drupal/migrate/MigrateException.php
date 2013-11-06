<?php

/**
 * @file
 * Contains \Drupal\migrate\MigrateException.
 */

namespace Drupal\migrate;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\MigrateIdMapInterface;

/**
 * Defines the migrate exception class.
 */
class MigrateException extends \Exception {

  /**
   * The level of the error being reported.
   *
   * The value is a Migration::MESSAGE_* constant.
   *
   * @var int
   */
  protected $level;

  /**
   * Gets the level.
   *
   * @return int
   */
  public function getLevel() {
    return $this->level;
  }

  /**
   * The status to record in the map table for the current item.
   *
   * The value is a MigrateMap::STATUS_* constant.
   *
   * @var int
   */
  protected $status;

  /**
   * Gets the status of the current item.
   *
   * @return \Exception|int
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Constructs a MigrateException object.
   *
   * @param string $message
   *   The message for the exception.
   * @param int $level
   *   The level of the error, a Migration::MESSAGE_* constant.
   * @param \Exception|int $status
   *   The status of the item for the map table, a MigrateMap::STATUS_*
   *   constant.
   */
  public function __construct($message, $level = MigrationInterface::MESSAGE_ERROR, $status = MigrateIdMapInterface::STATUS_FAILED) {
    $this->level = $level;
    $this->status = $status;
    parent::__construct($message);
  }

}
