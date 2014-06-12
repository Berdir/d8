<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\TypedData.
 */

namespace Drupal\Core\TypedData;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\Core\DependencyInjection\DependencySerialization;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The abstract base class for typed data.
 *
 * Classes deriving from this base class have to declare $value
 * or override getValue() or setValue().
 */
abstract class TypedData extends DependencySerialization implements TypedDataInterface, PluginInspectionInterface, ContainerFactoryPluginInterface  {

  /**
   * The data definition.
   *
   * @var \Drupal\Core\TypedData\DataDefinitionInterface
   */
  protected $definition;

  /**
   * The property name.
   *
   * @var string
   */
  protected $name;

  /**
   * The parent typed data object.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface
   */
  protected $parent;

  /**
   * The typed data plugin manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * Constructs a TypedData object given its definition and context.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The data definition.
   * @param string $name
   *   (optional) The name of the created property, or NULL if it is the root
   *   of a typed data tree. Defaults to NULL.
   * @param \Drupal\Core\TypedData\TypedDataInterface $parent
   *   (optional) The parent object of the data property, or NULL if it is the
   *   root of a typed data tree. Defaults to NULL.
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data plugin manager.
   *
   * @see \Drupal\Core\TypedData\TypedDataManager::create()
   *
   * @todo When \Drupal\Core\Config\TypedConfigManager has been fixed to use
   *   class-based definitions, type-hint $definition to
   *   DataDefinitionInterface. https://drupal.org/node/1928868
   */
  public function __construct($definition, $name = NULL, TypedDataInterface $parent = NULL, TypedDataManager $typed_data_manager = NULL) {
    $this->definition = $definition;
    $this->parent = $parent;
    $this->name = $name;
    $this->typedDataManager = $typed_data_manager;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\TypedData\TypedDataInterface $parent
   *   (optional) The parent object of the data property, or NULL if it is the
   *   root of a typed data tree. Defaults to NULL.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, TypedDataInterface $parent = NULL) {
    return new static(
      $plugin_definition,
      $plugin_id,
      $parent,
      $container->get('typed_data_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->definition['type'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    return $this->typedDataManager->getDefinition($this->definition->getDataType());
  }

  /**
   * {@inheritdoc}
   */
  public function getDataDefinition() {
    return $this->definition;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    $this->value = $value;
    // Notify the parent of any changes.
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getString() {
    return (string) $this->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = $this->typedDataManager->getValidationConstraintManager();
    $constraints = array();
    foreach ($this->definition->getConstraints() as $name => $options) {
      $constraints[] = $constraint_manager->create($name, $options);
    }
    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    return $this->typedDataManager->getValidator()->validate($this);
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    // Default to no default value.
    $this->setValue(NULL, $notify);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setContext($name = NULL, TypedDataInterface $parent = NULL) {
    $this->parent = $parent;
    $this->name = $name;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoot() {
    if (isset($this->parent)) {
      return $this->parent->getRoot();
    }
    // If no parent is set, this is the root of the data tree.
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyPath() {
    if (isset($this->parent)) {
      // The property path of this data object is the parent's path appended
      // by this object's name.
      $prefix = $this->parent->getPropertyPath();
      return (strlen($prefix) ? $prefix . '.' : '') . $this->name;
    }
    // If no parent is set, this is the root of the data tree. Thus the property
    // path equals the name of this data object.
    elseif (isset($this->name)) {
      return $this->name;
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getParent() {
    return $this->parent;
  }

}
