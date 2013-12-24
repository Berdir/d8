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

  public function __construct(array $configuration, $plugin_id, array $plugin_definition, array $entity_info, EntityStorageControllerInterface $storage_controller, MigratePluginManager $plugin_manager, FieldInfo $field_info) {
    $this->entityInfo = $entity_info;
    $this->storageController = $storage_controller;
    $this->pluginManager = $plugin_manager;
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
        if ($this->pluginManager->getDefinition($field_type)) {
          $destination_value = $this->pluginManager->createInstance($field_type)->import($instance, $row->getDestinationProperty($field_name));
          // @TODO: check for NULL return? Add an unset to $row? Maybe needed in exception handling? Propagate exception?
          $row->setDestinationProperty($field_name, $destination_value);
        }
      }
    }
    */
    // @TODO: validate! this will fatal if create() fails.
    $this->storageController->create($row->getDestination())->save();
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
