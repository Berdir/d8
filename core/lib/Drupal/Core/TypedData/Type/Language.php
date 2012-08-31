<?php
/**
 * @file
 * Definition of Drupal\Core\TypedData\Type\Language.
 */

namespace Drupal\Core\TypedData\Type;
use Drupal\Core\TypedData\WrapperInterface;
use InvalidArgumentException;

/**
 * Defines the 'language' data type, e.g. the computed 'language' property of language items.
 */
class Language extends WrapperBase implements WrapperInterface {

  /**
   * The data wrapper holding the langcode value.
   *
   * @var \Drupal\Core\TypedData\WrapperInterface
   */
  protected $langcode;

  /**
   * Implements WrapperInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, array $context = array()) {
    $this->definition = $definition;

    if (isset($context['parent'])) {
      $this->langcode = $context['parent']->get('value');
    }
    else {
      // No context given, so just initialize an langcode property for storing
      // the code.
      $this->langcode = drupal_wrap_data(array('type' => 'string'));
    }

    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Implements WrapperInterface::getValue().
   */
  public function getValue() {
    $langcode = $this->langcode->getValue();
    return $langcode ? language_load($langcode) : NULL;
  }

  /**
   * Implements WrapperInterface::setValue().
   *
   * Both the langcode and the language object may be passed as value.
   */
  public function setValue($value) {
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
   * Implements WrapperInterface::getString().
   */
  public function getString() {
    $language = $this->getValue();
    return $language ? $language->name : '';
  }

  /**
   * Implements WrapperInterface::validate().
   */
  public function validate() {
    // TODO: Implement validate() method.
  }
}
