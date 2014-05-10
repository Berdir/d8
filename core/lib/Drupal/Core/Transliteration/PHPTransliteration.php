<?php

/**
 * @file
 * Contains \Drupal\Core\Transliteration\PHPTransliteration.
 */

namespace Drupal\Core\Transliteration;

use Drupal\Component\Transliteration\PHPTransliteration as BaseTransliteration;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Enhances PHPTransliteration with an alter hook.
 *
 * @ingroup transliteration
 * @see hook_transliteration_overrides_alter()
 */
class PHPTransliteration extends BaseTransliteration {

  /**
   * The module handler to execute the transliteration_overrides alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a transliteration object.
   *
   * @param string $data_directory
   *   (optional) The directory where data files reside. If omitted, defaults
   *   to subdirectory 'data' underneath the directory where the class's PHP
   *   file resides.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to execute the transliteration_overrides alter hook.
   */
  public function __construct($data_directory = NULL, ModuleHandlerInterface $module_handler) {
    parent::__construct($data_directory);

    $this->moduleHandler = $module_handler;
  }

  /**
   * Overrides \Drupal\Component\Transliteration\PHPTransliteration::readLanguageOverrides().
   *
   * Allows modules to alter the language-specific $overrides array by invoking
   * hook_transliteration_overrides_alter().
   */
  protected function readLanguageOverrides($langcode) {
    parent::readLanguageOverrides($langcode);

    // Let modules alter the language-specific overrides.
    $this->moduleHandler->alter('transliteration_overrides', $this->languageOverrides[$langcode], $langcode);
  }

}
