<?php

/**
 * @file
 * Definition of Drupal\text\PropertyProcessedText.
 */

namespace Drupal\text;
use Drupal\Core\Property\PropertyInterface;
use Drupal\Core\Property\PropertyReadOnlyException;

/**
 * The string property type.
 */
class PropertyProcessedText extends \Drupal\Core\Property\Type\String {

  /**
   * The text item holding this computed property.
   *
   * @var \Drupal\Core\Property\PropertyInterface
   */
  protected $textItem;

  /**
   * Implements PropertyInterface::__construct().
   */
  public function __construct(array $definition, $value = NULL, $context = array()) {
    $this->definition = $definition;
    if (isset($value)) {
      $this->setValue($value);
    }
  }

  /**
   * Implements PropertyInterface::getValue().
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Implements PropertyInterface::setValue().
   */
  public function setValue($value) {
    throw new PropertyReadOnlyException('Unable to set a computed property.');
  }
}
