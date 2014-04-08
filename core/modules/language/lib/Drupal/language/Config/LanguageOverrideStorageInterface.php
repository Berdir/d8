<?php

/**
 * @file
 * Contains \Drupal\language\Config\LanguageOverrideStorageInterface.
 */

namespace Drupal\language\Config;

use Drupal\Core\Config\StorageCacheInterface;
use Drupal\Core\Config\StorageInterface;

interface LanguageOverrideStorageInterface extends StorageInterface {

  /**
   * Sets the langcode to determine the override configuration directory to use.
   *
   * @param string $langcode
   *   The language langcode to get the directory for.
   *
   * @return $this
   *   The language configuration storage.
   */
  public function setLangcode($langcode);

}
