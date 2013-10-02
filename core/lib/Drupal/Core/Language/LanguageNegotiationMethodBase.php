<?php

/**
 * @file
 * Contains \Drupal\Core\Language\LanguageNegotiationMethodBase.
 */

namespace Drupal\Core\Language;

use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\LanguageNegotiationInterface;

/**
 * Base class for language negotiation methods.
 */
abstract class LanguageNegotiationMethodBase implements LanguageNegotiationInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The language negotiation configuration.
   *
   * @var array
   */
  protected $config;

  /**
   * Constructs a new LanguageNegotiationMethodBase object.
   *
   * @param array $config
   */
  public function __construct(array $config) {
    $this->config = $config;
  }

  /**
   * Implements \Drupal\Core\Language\LanguageNegotiationInterface::setLanguageManager().
   */
  public function setLanguageManager(LanguageManager $languageManager) {
    $this->languageManager = $languageManager;
  }

}
