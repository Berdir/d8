<?php

/**
 * @file
 * Contains \Drupal\language\Config\LanguageOverrideFileStorage.
 */

namespace Drupal\language\Config;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\CachedStorage;
use Drupal\Core\Config\FileStorage;

/**
 * Defines file storage for language configuration overrides.
 */
class LanguageOverrideFileStorage extends FileStorage implements LanguageOverrideStorageInterface {

  /**
   * The directory for the current language.
   *
   * @var string
   */
  protected $langcode;

  /**
   * The directory for the current language.
   *
   * @var string
   */
  protected $directory;

  /**
   * Tracks whether the directory exists to prevent excessive file system reads.
   *
   * @var bool
   */
  protected $directoryExists;

  /**
   * Constructs a new LanguageOverrideFileStorage.
   */
  public function __construct() {
    // Do not call the parent as we do not yet have a directory.
  }

  /**
   * Gets the override configuration directory for a language code.
   *
   * @param string $langcode
   *   The language code to get the directory for.
   *
   * @return string
   *   The directory where the language override configuration is stored.
   */
  protected function getDirectory($langcode) {
    return config_get_config_directory(CONFIG_ACTIVE_DIRECTORY) . '/language/' . $langcode;
  }

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data) {
    $this->ensureDirectory();
    return parent::write($name, $data);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    if ($this->hasDirectory()) {
      return parent::delete($name);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    if ($this->hasDirectory()) {
      return parent::listAll($prefix);
    }
    return array();
  }

  /**
   * Creates a directory if necessary.
   */
  protected function ensureDirectory() {
    if (!$this->hasDirectory()) {
      drupal_mkdir($this->directory, NULL, TRUE);
      $this->directoryExists = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setLangcode($langcode) {
    // Reset directory check.
    $this->directoryExists = NULL;
    $this->langcode = $langcode;
    $this->directory = $this->getDirectory($langcode);
    if (empty($this->storage)) {
      $this->storage = new FileStorage($this->directory);
    }
    else {
      $this->storage->setDirectory($this->directory);
    }
    return $this;
  }

  /**
   * Discovers is the directory for the language exists.
   *
   * @return bool
   *   TRUE if the directory exists, FALSE if not.
   */
  protected function hasDirectory() {
    if (!isset($this->directoryExists)) {
      $this->directoryExists = is_dir($this->directory);
    }
    return $this->directoryExists;
  }

}
