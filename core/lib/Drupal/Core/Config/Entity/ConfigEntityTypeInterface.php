<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityTypeInterface.
 */

namespace Drupal\Core\Config\Entity;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides an interface for an configuration entity type and its metadata.
 */
interface ConfigEntityTypeInterface extends EntityTypeInterface {

  /**
   * Gets the config entity properties to export if declared on the annotation.
   *
   * @return array|bool
   *   The properties to export or FALSE if they can not be determine from the
   *   config entity type annotation.
   */
  public function getPropertiesToExport();

}
