<?php

/**
 * @file
 * Contains \Drupal\language\ContextProvider\CurrentLanguageContext.
 */

namespace Drupal\language\ContextProvider;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Sets the current language as a context.
 */
class CurrentLanguageContext implements ContextProviderInterface {

  use StringTranslationTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new CurrentLanguageContext.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface
   *   The language manager.
   */
  public function __construct(LanguageManagerInterface $language_manager) {
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getRunTimeContexts(array $context_slot_names) {
    // Add a context for each language type.
    $language_types = $this->languageManager->getLanguageTypes();
    $info = $this->languageManager->getDefinedLanguageTypesInfo();

    if ($context_slot_names) {
      foreach ($context_slot_names as $context_slot_name) {
        if (array_search(str_replace('language.', '', $context_slot_name), $language_types) === FALSE) {
          unset($language_types[str_replace('language.', '', $context_slot_name)]);
        }
      }
    }

    $result = [];
    foreach ($language_types as $type_key) {
      if (isset($info[$type_key]['name'])) {
        $context = new Context(new ContextDefinition('language', $info[$type_key]['name']));
        $context->setContextValue($this->languageManager->getCurrentLanguage($type_key));

        $cacheability = new CacheableMetadata();
        $cacheability->setCacheContexts(['languages:' . $type_key]);
        $context->addCacheableDependency($cacheability);

        $result['language.' . $type_key] = $context;
      }
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationTimeContexts() {
    return $this->getRunTimeContexts([]);
  }

}
