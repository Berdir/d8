<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\Entity.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\field\FieldInfo;
use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PluginId("entity")
 */
class Entity extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * The entity info definition.
   *
   * @var array
   */
  protected $entityInfo;

  /**
   * The entity storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  protected $storageController;

  /**
   * The plugin manager handling entity_field migrate plugins.
   *
   * @var \Drupal\migrate\Plugin\MigratePluginManager
   */
  protected $migrateEntityFieldPluginManager;

  /**
   * The field info service.
   *
   * @var \Drupal\field\FieldInfo
   */
  protected $fieldInfo;

  /**
   * Constructs an entity destination plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param array $entity_info
   *   The definition of this entity type.
   * @param EntityStorageControllerInterface $storage_controller
   *   The storage controller for this entity type.
   * @param MigratePluginManager $plugin_manager
   *   The plugin manager handling entity_field migrate plugins.
   * @param FieldInfo $field_info
   *   The field info object.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, array $entity_info, EntityStorageControllerInterface $storage_controller, MigratePluginManager $plugin_manager, FieldInfo $field_info) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityInfo = $entity_info;
    $this->storageController = $storage_controller;
    $this->migrateEntityFieldPluginManager = $plugin_manager;
    $this->fieldInfo = $field_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getDefinition($configuration['entity_type']),
      $container->get('entity.manager')->getStorageController($configuration['entity_type']),
      $container->get('plugin.manager.migrate.entity_field'),
      $container->get('field.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    /*
    // Field handling. Untested, unused and unnecessary yet. But it shows how
    // it'll look.
    if ($all_instances = $this->fieldInfo->getInstances($this->configuration['entity_type'])) {
      $instances = reset($all_instances);
      if (isset($this->entityInfo['entity keys']['bundle'])) {
        $bundle = $row->getDestinationProperty($this->entityInfo['entity keys']['bundle']);
        if (isset($all_instances[$bundle])) {
          $instances = $all_instances[$bundle];
        }
      }
      foreach ($instances as $field_name => $instance) {
        $field_type = $instance->getFieldType();
        if ($this->migrateEntityFieldPluginManager->getDefinition($field_type)) {
          $destination_value = $this->pluginManager->createInstance($field_type)->import($instance, $row->getDestinationProperty($field_name));
          // @TODO: check for NULL return? Add an unset to $row? Maybe needed in exception handling? Propagate exception?
          $row->setDestinationProperty($field_name, $destination_value);
        }
      }
    }
    */
    // @TODO: validate! this will fatal if create() fails.
    $entity = $this->storageController->create($row->getDestination());
    $entity->save();
    return array($entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getIdsSchema() {
    // TODO: Implement getIdsSchema() method.
  }

  /**
   * {@inheritdoc}
   */
  public function fields(Migration $migration = NULL) {
    // TODO: Implement fields() method.
  }

}
