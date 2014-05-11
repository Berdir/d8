<?php

/**
 * @file
 * Contains \Drupal\Core\Field\Plugin\Field\FieldType\MapItem.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'map' entity field type.
 *
 * @FieldType(
 *   id = "map",
 *   label = @Translation("Map"),
 *   description = @Translation("An entity field for storing a serialized array of values."),
 *   no_ui = TRUE
 * )
 */
class MapItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Serialized values'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'blob',
          'size' => 'big',
          'serialize' => TRUE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    $this->values = array();
    if (!isset($values)) {
      return;
    }

    if (!is_array($values)) {
      if ($values instanceof MapItem) {
        $values = $values->getValue();
      }
      else {
        $values = unserialize($values);
      }
    }

    $this->values = $values;

    // Notify the parent of any changes.
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    if (!isset($this->values[$name])) {
      $this->values[$name] = array();
    }

    return $this->values[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function __set($name, $value) {
    if (isset($value)) {
      $this->values[$name] = $value;
    }
    else {
      unset($this->values[$name]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getValue($include_computed = FALSE) {
    return isset($this->values['value']) ? $this->values['value'] : array();
  }

  /**
   * Returns all values of a map
   *
   * @return array
   */
  public function getValues() {
    return $this->values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->values);
  }

}
