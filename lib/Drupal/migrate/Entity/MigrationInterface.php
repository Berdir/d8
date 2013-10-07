<?php
/**
 * @file
 * Contains
 */

namespace Drupal\migrate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

interface MigrationInterface extends ConfigEntityInterface {

  /**
   * @return \Drupal\migrate\Plugin\MigrateSourceInterface
   */
  public function getSource();

  /**
   * @return \Drupal\migrate\Plugin\MigrateColumnMappingBag
   */
  public function getColumnMappings();

  /**
   * @return \Drupal\migrate\Plugin\MigrateDestinationInterface
   */
  public function getDestination();

}
