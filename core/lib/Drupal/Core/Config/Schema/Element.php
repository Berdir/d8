<?php

/**
 * @file
 * Contains \Drupal\Core\Config\Schema\Element.
 */

namespace Drupal\Core\Config\Schema;

use Drupal\Core\TypedData\TypedData;

/**
 * Defines a generic configuration element.
 */
abstract class Element extends TypedData {

  /**
   * The configuration value.
   *
   * @var mixed
   */
  protected $value;

  /**
   * The parent element object.
   *
   * @var Element
   */
  protected $parent;

  /**
   * Create typed config object.
   */
  protected function parseElement($key, $data, $definition) {
    return \Drupal::service('config.typed')->create($definition, $data, $key, $this);
  }

  /**
   * Build data definition object for contained elements.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface
   */
  protected function buildDataDefinition($definition, $value, $key) {
    return  \Drupal::service('config.typed')->buildDataDefinition($definition, $value, $key, $this);
  }

  /**
   * Get the full config path of the element.
   *
   * @return string
   *   The full config path of the element starting with the top element key
   *   followed by a colon followed by element names separated with dots.
   *   For example: views.view.content:display.default.display_options.
   */
  public function getFullName() {
    if (isset($this->parent)) {
      // Ensure if the parent was the root element, we do not add a dot after.
      return str_replace(':.', ':', $this->parent->getFullName() . '.' . $this->getName());
    }
    else {
      // If there is no parent, this is the root element, add a colon.
      return $this->getName() . ':';
    }
  }

}
