<?php

/**
 * @file
 * Contains Drupal\language\HttpKernel\PathProcessorLanguage.
 */

namespace Drupal\language\HttpKernel;

use Drupal\Component\Utility\Settings;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\language\LanguageNegotiatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountInterface;

/**
 * Processes the inbound path using path alias lookups.
 */
class PathProcessorLanguage implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * A config factory for retrieving required config settings.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Whether both secure and insecure session cookies can be used simultaneously.
   *
   * @var bool
   */
  protected $mixedModeSessions;

  /**
   * Language manager for retrieving the url language type.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The language negotiator.
   *
   * @var \Drupal\language\LanguageNegotiatorInterface
   */
  protected $negotiator;

  /**
   * The current active user.
   *
   * @return \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Local cache for language path processors.
   *
   * @var array
   */
  protected $processors;

  /**
   * Flag indicating whether the site is multilingual.
   *
   * @var bool
   */
  protected $multilingual;

  /**
   * Constructs a PathProcessorLanguage object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   A config factory object for retrieving configuration settings.
   * @param \Drupal\Component\Utility\Settings $settings
   *   The settings instance.
   * @param \Drupal\language\ConfigurableLanguageManagerInterface $language_manager
   *   The configurable language manager.
   * @param \Drupal\language\LanguageNegotiatorInterface
   *   The language negotiator.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current active user.
   */
  public function __construct(ConfigFactory $config, Settings $settings, ConfigurableLanguageManagerInterface $language_manager, LanguageNegotiatorInterface $negotiator, AccountInterface $current_user) {
    $this->config = $config;
    $this->mixedModeSessions = $settings->get('mixed_mode_sessions', FALSE);
    $this->languageManager = $language_manager;
    $this->negotiator = $negotiator;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    if (!empty($path)) {
      $scope = 'inbound';
      if (!isset($this->processors[$scope])) {
        $this->initProcessors($scope);
      }
      foreach ($this->processors[$scope] as $instance) {
        $path = $instance->processInbound($path, $request);
      }
    }
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = array(), Request $request = NULL) {
    if (!isset($this->multilingual)) {
      $this->multilingual = $this->languageManager->isMultilingual();
    }
    if ($this->multilingual) {
      $this->negotiator->setContext($this->currentUser, $request);
      $scope = 'outbound';
      if (!isset($this->processors[$scope])) {
        $this->initProcessors($scope);
      }
      // Execute outbound language processors.
      $options['mixed_mode_sessions'] = $this->mixedModeSessions;
      foreach ($this->processors[$scope] as $instance) {
        $path = $instance->processOutbound($path, $options, $request);
      }
      // No language dependent path allowed in this mode.
      if (empty($this->processors[$scope])) {
        unset($options['language']);
      }
    }
    return $path;
  }

  /**
   * Initializes the local cache for language path processors.
   *
   * @param string $scope
   *   The scope of the processors: "inbound" or "outbound".
   */
  protected function initProcessors($scope) {
    $interface = '\Drupal\Core\PathProcessor\\' . Unicode::ucfirst($scope) . 'PathProcessorInterface';
    $this->processors[$scope] = array();
    foreach ($this->languageManager->getLanguageTypes() as $type) {
      foreach ($this->negotiator->getNegotiationMethods($type) as $method_id => $method) {
        if (!isset($this->processors[$scope][$method_id])) {
          $reflector = new \ReflectionClass($method['class']);
          if ($reflector->implementsInterface($interface)) {
            $this->processors[$scope][$method_id] = $this->negotiator->getNegotiationMethodInstance($method_id);
          }
        }
      }
    }
  }

}
