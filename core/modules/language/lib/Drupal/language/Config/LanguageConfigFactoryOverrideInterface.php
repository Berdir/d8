<?php

/**
 * @file
 * Contains \Drupal\language\Config\LanguageConfigFactoryOverrideInterface.
 */

namespace Drupal\language\Config;

use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Language\Language;

/**
 * Defines the interface for a configuration factory language override object.
 */
interface LanguageConfigFactoryOverrideInterface extends ConfigFactoryOverrideInterface {

  /**
   * Prefix for all language configuration files.
   */
  const LANGUAGE_CONFIG_PREFIX = 'language.config';

  /**
   * Gets the language object used to override configuration data.
   *
   * @return \Drupal\Core\Language\Language
   *   The language object used to override configuration data.
   */
  public function getLanguage();

  /**
   * Sets the language to be used in configuration overrides.
   *
   * @param \Drupal\Core\Language\Language $language
   *   The language object used to override configuration data.
   *
   * @return $this
   */
  public function setLanguage(Language $language = NULL);

  /**
   * Get language override for given language and configuration name.
   *
   * @param string $langcode
   *   Language code.
   * @param string $name
   *   Configuration name.
   *
   * @return \Drupal\Core\Config\Config
   *   Configuration override object.
   */
  public function getOverride($langcode, $name);

}
