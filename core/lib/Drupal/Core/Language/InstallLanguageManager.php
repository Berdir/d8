<?php

/**
 * @file
 * Contains \Drupal\Core\Language\InstallLanguageManager.
 */

namespace Drupal\Core\Language;

use Drupal\Component\Plugin\PluginManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;

/**
 * Class responsible for initializing each language type.
 */
class InstallLanguageManager extends LanguageManager {

  /**
   * Constructs a new LanguageManager object.
   *
   * @param array $config
   *   An array of configuration.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $negotiator_manager
   *   The language negotiation methods plugin manager
   */
  public function __construct(array $config, PluginManagerInterface $negotiator_manager) {
    $this->config = $config;
    $this->negotiatorManager = $negotiator_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function isMultilingual() {
    // No state service in install time.
    return FALSE;
  }

}
