<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Field\Type\EntityWrapper.
 */

namespace Drupal\Core\Entity\Field\Type;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\DataReferenceBase;
use InvalidArgumentException;

/**
 * Defines an 'entity_reference' data type, e.g. the computed 'entity' property of entity references.
 *
 * The plain value of this reference is the entity object, i.e. an instance of
 * Drupal\Core\Entity\EntityInterface. For setting the value the entity object
 * or the entity ID may be passed, whereas passing the ID is only supported if
 * an 'entity type' constraint is specified.
 *
 * Some supported constraints (below the definition's 'constraints' key) are:
 *  - EntityType: The entity type. Required.
 *  - Bundle: (optional) The bundle or an array of possible bundles.
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - source: The ID property used for loading the entity object.
 */
class EntityReference extends DataReferenceBase {

  /**
   * Implements \Drupal\Core\TypedData\DataReferenceInterface::getTargetDefinition().
   */
  public function getTargetDefinition() {
    $definition = array(
      'type' => 'entity',
    );
    if (isset($this->definition['constraints']['EntityType'])) {
      $definition['type'] .= ':' . $this->definition['constraints']['EntityType'];
    }
    if (isset($this->definition['constraints']['Bundle']) && is_string($this->definition['constraints']['Bundle'])) {
      $definition['type'] .= ':' . $this->definition['constraints']['Bundle'];
    }
    return $definition;
  }

  /**
   * Implements \Drupal\Core\TypedData\DataReferenceInterface::getTarget().
   */
  public function getTarget() {
    if (!isset($this->target)) {
      // If we have a valid reference, return the entity object which is typed
      // data itself. If the reference is not valid, use the typed data API to
      // return an abstract type object so that the metadata is still available.
      if ($id = $this->getSource()->getValue()) {
        $this->target = entity_load($this->definition['constraints']['EntityType'], $id);
      }
      if (!$this->target) {
        $this->target = typed_data()->create($this->getTargetDefinition(), NULL);
      }
    }
    return $this->target;
  }

  /**
   * Implements \Drupal\Core\TypedData\DataReferenceInterface::getTargetIdentifier().
   */
  public function getTargetIdentifier() {
    if ($target_value = $this->getValue()) {
      return $target_value->id();
    }
  }

  /**
   * Overrides \Drupal\Core\TypedData\TypedData::getValue().
   */
  public function getValue() {
    // If we have a valid reference, return the entity object, otherwise NULL.
    $target = $this->getTarget();
    return !$target->isempty() ? $target : NULL;
  }

  /**
   * Overrides \Drupal\Core\TypedData\TypedData::setValue().
   *
   * Both the entity ID and the entity object may be passed as value.
   */
  public function setValue($value, $notify = TRUE) {
    // If we have already a typed data object for the target, clear it.
    unset($this->target);

    // Support passing in the entity object.
    if ($value instanceof EntityInterface) {
      $this->target = $value;
      $value = $value->id();
    }
    elseif (isset($value) && !(is_scalar($value) && !empty($this->definition['constraints']['EntityType']))) {
      throw new InvalidArgumentException('Value is not a valid entity.');
    }
    // Now update the value in the source property.
    $this->getSource()->setValue($value, $notify);
  }

  /**
   * Overrides \Drupal\Core\TypedData\TypedData::getString().
   */
  public function getString() {
    if ($entity = $this->getValue()) {
      return $entity->label();
    }
    return '';
  }
}
