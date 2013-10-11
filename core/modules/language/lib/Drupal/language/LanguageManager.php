<?php

/**
 * @file
 * Contains \Drupal\language\LanguageManager.
 */

namespace Drupal\language;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManager as LanguageManagerBase;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Overrides default LanguageManager to provide configured languages.
 */
class LanguageManager extends LanguageManagerBase {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory;
   */
  protected $configFactory;

  /**
   * The configuration storage.
   *
   * @var \Drupal\Core\Config\StorageInterface;
   */
  protected $configStorage;

  /**
   * Constructs a new LanguageManager object.
   *
   * @param array $config
   *   An array of configuration.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $negotiator_manager
   *   The language negotiation methods plugin manager.
   * @param \Drupal\Core\KeyValueStoreInterface $state
   *   The state key value store.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   *   The config storage service.
   */
  public function __construct(array $config, PluginManagerInterface $negotiator_manager, KeyValueStoreInterface $state, StorageInterface $config_storage) {
    parent::__construct($config, $negotiator_manager, $state);
    $this->configStorage = $config_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageList($flags = Language::STATE_CONFIGURABLE) {
    if (!isset($this->languageList)) {
      // Fill in master language list based on current configuration.
      $default = $this->getLanguageDefault();

      // Use language module configuration if available.
      $language_ids = $this->configStorage->listAll('language.entity');
      foreach (\Drupal::service('config.factory')->loadMultiple($language_ids) as $language_config) {
        $langcode = $language_config->get('id');
        $info = $language_config->get();
        $info['default'] = ($langcode == $default->id);
        $info['name'] = $info['label'];
        $this->languageList[$langcode] = new Language($info);
      }
    }

    return parent::getLanguageList($flags);
  }

}
