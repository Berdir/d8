<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\DataType\Deriver\FieldItemDeriver.
 */

namespace Drupal\Core\Entity\Plugin\DataType\Deriver;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides data type plugins for each existing field type plugin.
 */
class FieldItemDeriver implements ContainerDerivativeInterface {

  /**
   * List of derivative definitions.
   *
   * @var array
   */
  protected $derivatives = array();

  /**
   * The base plugin ID this derivative is for.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * The field type plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $fieldTypePluginManager;

  /**
   * Constructs a FieldItemDeriver object.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $field_type_plugin_manager
   *   The field type plugin manager.
   */
  public function __construct($base_plugin_id, PluginManagerInterface $field_type_plugin_manager) {
    $this->basePluginId = $base_plugin_id;
    $this->fieldTypePluginManager = $field_type_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('plugin.manager.entity.field.field_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinition($derivative_id, array $base_plugin_definition) {
    if (!isset($this->derivatives)) {
      $this->getDerivativeDefinitions($base_plugin_definition);
    }
    if (isset($this->derivatives[$derivative_id])) {
      return $this->derivatives[$derivative_id];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions(array $base_plugin_definition) {
    foreach ($this->fieldTypePluginManager->getDefinitions() as $plugin_id => $definition) {
      // Provide easy access to the field type without requiring consuming code
      // to parse it from the full data type.
      $definition['field_type'] = $plugin_id;

      // The distinction between 'settings' and 'instance_settings' is only
      // meaningful at the field type plugin level. At the Typed data API level,
      // merge them.
      $definition['settings'] = $definition['instance_settings'] + $definition['settings'];
      unset($definition['instance_settings']);

      $this->derivatives[$plugin_id] = $definition;
    }
    return $this->derivatives;
  }

}
