<?php

/**
 * @file
 * Contains \Drupal\Core\Config\ConfigFactoryOverrideInterface.
 */

namespace Drupal\Core\Config;

/**
 * Defines the interface for a configuration factory override object.
 */
interface ConfigFactoryOverrideInterface {

  /**
   * Returns config overrides.
   *
   * @param array $names
   *   A list of configuration names that are being loaded.
   *
   * @return array
   *   An array keyed by configuration name of override data. Override data
   *   contains a nested array structure of overrides.
   */
  public function loadOverrides($names);

  /**
   * The string to append to the configuration static cache name.
   *
   * @return string
   *   A string to append to the configuration static cache name.
   */
  public function getCacheSuffix();

  /**
   * Reacts to default configuration installation during extension install.
   *
   * @param string $type
   *   The type of extension being installed. Either 'module' or 'theme'.
   * @param string $name
   *   The name of the extension.
   */
  public function install($type, $name);

  /**
   * Reacts to configuration removal during extension uninstallation.
   *
   * @param string $type
   *   The type of extension being uninstalled. Either 'module' or 'theme'.
   * @param string $name
   *   The name of the extension.
   */
  public function uninstall($type, $name);

}
