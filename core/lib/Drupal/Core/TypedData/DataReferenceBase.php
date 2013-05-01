<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\DataReferenceInterface.
 */

namespace Drupal\Core\TypedData;

/**
 * Base class for typed data references.
 *
 * Implementing classes have to implement at least
 * \Drupal\Core\TypedData\DataReferenceInterface::getTargetDefinition() and
 * \Drupal\Core\TypedData\DataReferenceInterface::getTargetIdentifier().
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - source: The langcode property used to load the language object.
 */
abstract class DataReferenceBase extends ContextAwareTypedData implements DataReferenceInterface  {

  /**
   * The referenced data.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface
   */
  protected $target;

  /**
   * Implements \Drupal\Core\TypedData\DataReferenceInterface::getTarget().
   */
  public function getTarget() {
    if (!isset($this->target)) {
      $this->target = typed_data()->create($this->getTargetDefinition(), $this->getSource()->getValue());
    }
    return $this->target;
  }

  /**
   * Overrides TypedData::getValue().
   */
  public function getValue() {
    return $this->getTarget()->getValue();
  }

  /**
   * Helper to get the typed data object holding the source value.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   */
  protected function getSource() {
    if (empty($this->definition['settings']['source'])) {
      throw new DataDefinitionException("Missing 'source' setting.");
    }
    return $this->parent->get($this->definition['settings']['source']);
  }

  /**
   * Overrides TypedData::setValue().
   *
   * Both the langcode and the language object may be passed as value.
   */
  public function setValue($value) {
    // If we have already a typed data object for the target, clear it and start
    // with a new object. That way we do not change the value of a object which
    // might be referenced elsewhere also.
    unset($this->target);
    $target = $this->getTarget();
    // Set the value on the target so we can retrieve its identifier.
    $target->setValue($value);
    $this->getSource()->setValue($this->getTargetIdentifier());
  }

  /**
   * Overrides TypedData::getString().
   */
  public function getString() {
    return (string) $this->getTargetIdentifier();
  }
}
