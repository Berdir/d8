<?php

/**
 * @file
 * Contains \Drupal\language\Config\LanguageConfigFactoryOverride.
 */

namespace Drupal\language\Config;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageDefault;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides language overrides for the configuration factory.
 */
class LanguageConfigFactoryOverride implements LanguageConfigFactoryOverrideInterface {

  /**
   * The configuration storage.
   *
   * @var \Drupal\language\Config\LanguageOverrideStorageInterface
   */
  protected $storage;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManager
   */
  protected $typedConfigManager;

  /**
   * An event dispatcher instance to use for configuration events.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The language object used to override configuration data.
   *
   * @var \Drupal\Core\Language\Language
   */
  protected $language;

  /**
   * Constructs the LanguageConfigFactoryOverride object.
   *
   * @param \Drupal\language\Config\LanguageOverrideStorageInterface $storage
   *   The configuration storage engine.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   An event dispatcher instance to use for configuration events.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config
   *   The typed configuration manager.
   */
  public function __construct(LanguageOverrideStorageInterface $storage, EventDispatcherInterface $event_dispatcher, TypedConfigManagerInterface $typed_config) {
    $this->storage = $storage;
    $this->eventDispatcher = $event_dispatcher;
    $this->typedConfigManager = $typed_config;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    if ($this->language) {
      return $this->storage->readMultiple($names);
    }
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getOverride($langcode, $name) {
    $storage = clone $this->storage;
    $data = $storage->setLangcode($langcode)->read($name);
    $override = new LanguageConfigOverride($name, $storage, $this->typedConfigManager);
    if (!empty($data)) {
      $override->initWithData($data);
    }
    return $override;
  }

  /**
   * {@inheritdoc}
   */
  public function getStorage() {
    return $this->storage;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return $this->language ? $this->language->id : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguage() {
    return $this->language;
  }

  /**
   * {@inheritdoc}
   */
  public function setLanguage(Language $language = NULL) {
    $this->language = $language;
    $this->storage->setLangcode($this->language ? $this->language->id : NULL);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLanguageFromDefault(LanguageDefault $language_default = NULL) {
    $this->language = $language_default ? $language_default->get() : NULL;
    $this->storage->setLangcode($this->language->id);
    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * @todo maybe this should be done somewhere else?
   */
  public function install($type, $name) {
    // Work out if this extension provides default language overrides.
    $config_dir = drupal_get_path($type, $name) . '/config/language';
    if (is_dir($config_dir)) {
      // List all the directories.
      // \DirectoryIterator on Windows requires an absolute path.
      $it  = new \DirectoryIterator(realpath($config_dir));
      foreach ($it as $dir) {
        if (!$dir->isDot() && $dir->isDir() ) {
          $default_language_config = new FileStorage($dir->getPathname());
          $this->storage->setLangcode($dir->getFilename());
          foreach ($default_language_config->listAll() as $config_name) {
            $data = $default_language_config->read($config_name);
            $config = new LanguageConfigOverride($config_name, $this->storage, $this->typedConfigManager);
            $config->setData($data)->save();
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @todo maybe this should be done somewhere else?
   */
  public function uninstall($type, $name) {
    // Can not use ConfigurableLanguageManager::getLanguages() since that would
    // create a circular dependency.
    $language_directory = config_get_config_directory() .'/language';
    if (is_dir(($language_directory))) {
      $it  = new \DirectoryIterator(realpath($language_directory));
      foreach ($it as $dir) {
        if (!$dir->isDot() && $dir->isDir() ) {
          $this->storage->setLangcode($dir->getFilename());
          $config_names = $this->storage->listAll($name . '.');
          foreach ($config_names as $config_name) {
            $config = new LanguageConfigOverride($config_name, $this->storage, $this->typedConfigManager);
            $config->delete();
          }
        }
      }
    }
  }

}
