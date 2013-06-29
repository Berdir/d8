<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\Plugin\DataType\Language.
 */

namespace Drupal\Core\TypedData\Plugin\DataType;

use Drupal\Core\TypedData\Annotation\DataType;
use Drupal\Core\Annotation\Translation;
use InvalidArgumentException;
use Drupal\Core\Language\Language as LanguageObject;
use Drupal\Core\TypedData\IdentifiableInterface;
use Drupal\Core\TypedData\TypedData;

/**
 * Defines the 'language' data type.
 *
 * The plain value of a language is the language object, i.e. an instance of
 * \Drupal\Core\Language\Language. For setting the value the language object or
 * the language code as string may be passed.
 *
 * @DataType(
 *   id = "language",
 *   label = @Translation("Language"),
 *   description = @Translation("A language object.")
 * )
 */
class Language extends TypedData implements IdentifiableInterface {

  /**
   * The language code of the language if no 'langcode source' is used.
   *
   * @var string
   */
  protected $langcode;

  /**
   * @var \Drupal\Core\Language
   */
  protected $language;

  /**
   * Overrides TypedData::getValue().
   *
   * @return \Drupal\Core\Language\Language|null
   */
  public function getValue() {
    if (!isset($this->language) && $this->langcode) {
      $this->language = language_load($this->langcode);
    }
    return $this->language;
  }

  /**
   * Overrides TypedData::setValue().
   *
   * Both the langcode and the language object may be passed as value.
   */
  public function setValue($value, $notify = TRUE) {
    // Support passing language objects.
    if (is_object($value)) {
      $this->language = $value;
    }
    elseif (isset($value) && !is_scalar($value)) {
      throw new InvalidArgumentException('Value is no valid langcode or language object.');
    }
    else {
      $this->langcode = $value;
    }
    // Notify the parent of any changes.
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

  /**
   * Overrides TypedData::getString().
   */
  public function getString() {
    $language = $this->getValue();
    return $language ? $language->name : '';
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    if (isset($this->langcode)) {
      return $this->langcode;
    }
    elseif (isset($this->language)) {
      return $this->language->langcode;
    }
  }

}
