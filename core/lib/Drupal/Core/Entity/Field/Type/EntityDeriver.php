<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Field\Type\EntityDeriver.
 */

namespace Drupal\Core\Entity\Field\Type;

use Drupal\Component\Plugin\Derivative\DerivativeInterface;

/**
 * Cares about registering data types for each entity type and entity bundle.
 *
 * @see \Drupal\Core\Entity\Field\Type\AbstractEntity
 */
class EntityDeriver implements DerivativeInterface {

  /**
   * List of derivative definitions.
   *
   * @var array
   */
  protected $derivatives = array();

  /**
   * Implements \Drupal\Component\Plugin\Derivative\DerivativeInterface::getDerivativeDefinition().
   */
  public function getDerivativeDefinition($derivative_id, array $base_plugin_definition) {
    if (!empty($this->derivatives) && !empty($this->derivatives[$derivative_id])) {
      return $this->derivatives[$derivative_id];
    }
    $this->getDerivativeDefinitions($base_plugin_definition);
    if (isset($this->derivatives[$derivative_id])) {
      return $this->derivatives[$derivative_id];
    }
  }

  /**
   * Implements \Drupal\Component\Plugin\Derivative\DerivativeInterface::getDerivativeDefinitions().
   */
  public function getDerivativeDefinitions(array $base_plugin_definition) {
    // Also keep the 'entity' defined as is.
    $this->derivatives[''] = $base_plugin_definition;
    // Add definitions for each entity type and bundle.
    foreach (entity_get_info() as $entity_type => $info) {
      $this->derivatives[$entity_type] = array(
        'label' => $info['label'],
        'class' => $info['class'],
        'constraints' => array('EntityType' => $entity_type),
      ) + $base_plugin_definition;

      // Incorporate the bundles as entity:$entity_type:$bundle, if any.
      foreach (entity_get_bundles($entity_type) as $bundle => $bundle_info) {
        $this->derivatives[$entity_type . ':' . $bundle] = array(
          'label' => $bundle_info['label'],
          'class' => $info['class'],
          'constraints' => array(
            'EntityType' => $entity_type,
            'Bundle' => $bundle,
          ),
        ) + $base_plugin_definition;
      }
    }
    return $this->derivatives;
  }
}
