<?php
/**
 * @file
 * Definition of Drupal\Core\Property\Type\Language.
 */

namespace Drupal\Core\Property\Type;
use \Drupal\Core\Property\PropertyInterface;

/**
 * Defines the 'language' property type, e.g. the computed 'language' property of language items.
 */
class Language implements PropertyInterface {

  /**
   * The property definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * The property holding the langcode.
   *
   * @var \Drupal\Core\Property\PropertyInterface
   */
  protected $langcode;

  /**
   * Implements PropertyInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, $context = array()) {
    $this->definition = $definition;

    if (isset($context['parent'])) {
      $this->langcode = $context['parent']->get('langcode');
    }
    else {
      // No context given, so just initialize an langcode property for storing
      // the code.
      $this->langcode = drupal_get_property(array('type' => 'string'));
    }

    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Implements PropertyInterface::getType().
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Implements PropertyInterface::getDefinition().
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Implements PropertyInterface::getValue().
   */
  public function getValue() {
    $langcode = $this->langcode->getValue();
    return $langcode ? language_load($langcode) : NULL;
  }

  /**
   * Implements PropertyInterface::setValue().
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
      throw new \InvalidArgumentException('Value is no valid langcode or language object.');
    }
  }

  /**
   * Implements PropertyInterface::getString().
   */
  public function getString() {
    $language = $this->getValue();
    return $language ? $language->name : '';
  }

  /**
   * Implements PropertyInterface::validate().
   */
  public function validate($value = NULL) {
    // TODO: Implement validate() method.
  }
}
