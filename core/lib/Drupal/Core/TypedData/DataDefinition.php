<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\DataDefinition.
 */

namespace Drupal\Core\TypedData;

/**
 * A class for defining data based on defined data types.
 */
class DataDefinition implements DataDefinitionInterface, \ArrayAccess {

  /**
   * The array holding values for all definition keys.
   *
   * @var array
   */
  protected $definition = array();

  /**
   * Constructs a new data definition object.
   *
   * @param array $definition
   *   (optional) If given, a data definition represented as array.
   */
  public function __construct(array $definition = array()) {
    $this->definition = $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->definition['type'];
  }

  /**
   * Sets the data type.
   *
   * @param string $type
   *   The data type to set.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setType($type) {
    $this->definition['type'] = $type;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return isset($this->definition['label']) ? $this->definition['label'] : NULL;
  }

  /**
   * Sets the human-readable label.
   *
   * @param string $label
   *   The label to set.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setLabel($label) {
    $this->definition['label'] = $label;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return isset($this->definition['description']) ? $this->definition['description'] : NULL;
  }

  /**
   * Sets the human-readable description.
   *
   * @param string $description
   *   The description to set.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setDescription($description) {
    $this->definition['description'] = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isList() {
    // For BC, support definitions using the 'list' flag with 'list_class'.
    $type_definition = \Drupal::typedData()->getDefinition($this->getType());
    return is_subclass_of($type_definition['class'], '\Drupal\Core\TypedData\ListInterface') || !empty($this->definition['list']);
  }

  /**
   * {@inheritdoc}
   */
  public function isReadOnly() {
    if (!isset($this->definition['read-only'])) {
      // Default to read-only if the data value is computed.
      return $this->isComputed();
    }
    return $this->definition['read-only'];
  }

  /**
   * Sets whether the data is read-only.
   *
   * @param boolean $read-only
   *   Whether the data is read-only.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setReadOnly($read_only) {
    $this->definition['read-only'] = $read_only;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isComputed() {
    return !empty($this->definition['computed']);
  }

  /**
   * Sets whether the data is computed.
   *
   * @param boolean $computed
   *   Whether the data is computed.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setComputed($computed) {
    $this->definition['computed'] = $computed;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired() {
    return !empty($this->definition['required']);
  }

  /**
   * Sets whether the data is required.
   *
   * @param boolean $required
   *   Whether the data is required.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setRequired($required) {
    $this->definition['required'] = $required;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getClass() {
    return isset($this->definition['class']) ? $this->definition['class'] : NULL;
  }

  /**
   * Sets the class used for creating the typed data object.
   *
   * @param string|null $class
   *   The class to use.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setClass($class) {
    $this->definition['class'] = $class;
    return $this;
  }

  /**
   * Returns the array of settings, as required by the used class.
   *
   * See the documentation of the class for supported or required settings.
   *
   * @return array
   *   The array of settings.
   */
  public function getSettings() {
    return isset($this->definition['settings']) ? $this->definition['settings'] : array();
  }

  /**
   * Sets the array of settings, as required by the used class.
   *
   * @param array $settings
   *   The array of settings.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setSettings(array $settings) {
    $this->definition['settings'] = $settings;
    return $this;
  }

  /**
   * Returns an array of validation constraints.
   *
   * See \Drupal\Core\TypedData\TypedDataManager::getConstraints() for details.
   *
   * @return array
   *   Array of constraints, each being an instance of
   *   \Symfony\Component\Validator\Constraint.
   */
  public function getConstraints() {
    return isset($this->definition['constraints']) ? $this->definition['constraints'] : array();
  }

  /**
   * Sets the array of validation constraints.
   *
   * @param array $constraints
   *   The array of constraints.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setConstraints(array $constraints) {
    $this->definition['constraints'] = $constraints;
    return $this;
  }

  /**
   * Gets
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface|null
   *   If the data is a list, a data definition describing the list items, NULL
   *   otherwise.
   */
  public function getItemDefinition() {
    return isset($this->definition['item_definition']) ? $this->definition['item_definition'] : NULL;
  }

  /**
   * Sets the data definition of an item of the list.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface|null $definition
   *   The data definition of an item of the list, or NULL if the data is no
   *   list.
   *
   * @return \Drupal\Core\TypedData\DataDefinition
   *   The object itself for chaining.
   */
  public function setItemDefinition(DataDefinitionInterface $item_definition = NULL) {
    $this->definition['item_definition'] = $item_definition;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function offsetExists($offset) {
    return array_key_exists($offset, $this->definition);
  }

  /**
   * {@inheritdoc}
   */
  public function offsetGet($offset) {
    return $this->definition[$offset];
  }

  /**
   * {@inheritdoc}
   */
  public function offsetSet($offset, $value) {
    $this->definition[$offset] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function offsetUnset($offset) {
    unset($this->definition[$offset]);
  }

  /**
   * Returns all definition values as array.
   *
   * @return array
   */
  public function toArray() {
    return $this->definition;
  }
}