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
    // @TODO: add field handling. https://drupal.org/node/2164451
    // @TODO: add validateion https://drupal.org/node/2164457
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
