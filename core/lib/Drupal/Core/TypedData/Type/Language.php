<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Language.
 */

namespace Drupal\Core\TypedData\Type;

use Drupal\Core\TypedData\TypedDataInterface;
use InvalidArgumentException;

/**
 * Defines the 'language' data type.
 *
 * The plain value of a language is the language object, i.e. an instance of
 * Drupal\Core\Language\Language. For setting the value the language object or
 * the language code as string may be passed.
 *
 * Optionally, this class may be used as computed property, see the supported
 * settings below. E.g., it is used as 'language' property of language items.
 *
 * Supported settings (below the definition's 'settings' key) are:
 *  - langcode source: If used as computed property, the langcode property used
 *    to load the language object.
 */
class Language extends TypedData implements TypedDataInterface {

  /**
   * The data wrapper holding the langcode value.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface
   */
  protected $langcode;

  /**
   * Implements TypedDataInterface::setContext().
   */
  public function setContext(array $context) {
    if (!empty($this->definition['settings']['langcode source'])) {
      $this->langcode = $context['parent']->get($this->definition['settings']['langcode source']);
    }
  }

  /**
   * Implements TypedDataInterface::getValue().
   */
  public function getValue() {
    $langcode = isset($this->langcode) ? $this->langcode->getValue(): FALSE;
    return $langcode ? language_load($langcode) : NULL;
  }

  /**
   * Implements TypedDataInterface::setValue().
   *
   * Both the langcode and the language object may be passed as value.
   */
  public function setValue($value) {
    // Initialize the langcode property if no context is given.
    if (!isset($this->langcode)) {
      $this->langcode = typed_data()->create(array('type' => 'string'));
    }

    if (!isset($value)) {
      $this->langcode->setValue(NULL);
    }
    elseif (is_scalar($value)) {
      $this->langcode->setValue($value);
    }
    elseif (is_object($value)) {
      $this->langcode->setValue($value->langcode);
    }
    else {
      throw new InvalidArgumentException('Value is no valid langcode or language object.');
    }
  }

  /**
   * Implements TypedDataInterface::getString().
   */
  public function getString() {
    $language = $this->getValue();
    return $language ? $language->name : '';
  }

  /**
   * Implements TypedDataInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
