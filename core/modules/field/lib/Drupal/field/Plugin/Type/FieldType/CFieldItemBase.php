<?php

/**
 * @file
 * Contains \Drupal\field\Plugin\Type\FieldType\CFieldItemBase.
 */

namespace Drupal\field\Plugin\Type\FieldType;

use Drupal\Core\Entity\Field\FieldItemBase;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\field\Plugin\Core\Entity\Field;
use Drupal\field\Field as FieldAPI;

/**
 * Base class for 'field type' plugin implementations.
 */
abstract class CFieldItemBase extends FieldItemBase implements CFieldItemInterface {

  /**
   * The Field instance definition.
   *
   * @var \Drupal\field\Plugin\Core\Entity\FieldInstance
   */
  protected $instance;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\field\Plugin\Core\Entity\Field $field
   *   The field definition.
   */
  public function __construct(array $definition, $plugin_id, array $plugin_definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $plugin_id, $plugin_definition, $name, $parent);
    if (isset($definition['instance'])) {
      $this->instance = $definition['instance'];
    }
    else {
      $entity = $parent->getParent();
      $instances = FieldAPI::fieldInfo()->getBundleInstances($entity->entityType(), $entity->bundle());
      $this->instance = $instances[$parent->name];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state, $has_data) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function instanceSettingsForm(array $form, array &$form_state) {
    return array();
  }

}
