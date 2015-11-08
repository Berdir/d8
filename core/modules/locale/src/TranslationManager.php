<?php

/**
 * @file
 * Contains \Drupal\locale\TranslationManager.
 */

namespace Drupal\locale;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\Translator\TranslatorInterface;

/**
 * Defines a translation manager that is Locale aware.
 *
 * @see \Drupal\Core\StringTranslation\TranslationManager
 */
class TranslationManager implements TranslationInterface {
  use DependencySerializationTrait;

  /**
   * Constructs a TranslationManager object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The object that implements TranslationInterface.
   */
  public function __construct(TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function translate($string, array $args = array(), array $options = array()) {
    $safe = TRUE;
    $wrapper = new TranslatableMarkup($string, $args, $options, $this);
    return $safe ? $wrapper : (string) $wrapper;
  }

  /**
   * {@inheritdoc}
   */
  public function translateString(TranslatableMarkup $translated_string) {
    return $this->stringTranslation->translateString($translated_string);
  }

  /**
   * {@inheritdoc}
   */
  public function formatPlural($count, $singular, $plural, array $args = array(), array $options = array()) {
    $safe = TRUE;
    $plural = new PluralTranslatableMarkup($count, $singular, $plural, $args, $options, $this);
    return $safe ? $plural : (string) $plural;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluralIndex($count, $langcode = NULL) {
    return locale_get_plural($count, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function getStringTranslation($langcode, $string, $context) {
    return $this->stringTranslation->getStringTranslation($langcode, $string, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->stringTranslation->reset();
  }

  /**
   * Sets the default langcode.
   *
   * @param string $langcode
   *   A language code.
   */
  public function setDefaultLangcode($langcode) {
    $this->stringTranslation->setDefaultLangcode($langcode);
  }

  /**
   * Appends a translation system to the translation chain.
   *
   * @param \Drupal\Core\StringTranslation\Translator\TranslatorInterface $translator
   *   The translation interface to be appended to the translation chain.
   * @param int $priority
   *   The priority of the logger being added.
   *
   * @return \Drupal\Core\StringTranslation\TranslationManager
   *   The called object.
   */
  public function addTranslator(TranslatorInterface $translator, $priority = 0) {
    $this->stringTranslation->addTranslator($translator, $priority);
    return $this;
  }

}
