<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\Entity.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\MigrateException;

/**
 * @PluginID("entity")
 */
abstract class Entity extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  protected $storageController;

  /**
   * The list of the bundles of this entity type.
   *
   * @var array
   */
  protected $bundles;

  /**
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param MigrationInterface $migration
   *   The migration.
   * @param EntityStorageControllerInterface $storage_controller
   *   The storage controller for this entity type.
   * @param array $bundles
   *   The list of bundles this entity type has.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller, array $bundles) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->storageController = $storage_controller;
    $this->bundles = $bundles;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration = NULL) {
    $entity_type = static::getEntityType($configuration, $plugin_id);
    $storage_controller = $container->get('entity.manager')->getStorageController($entity_type);
    $class = get_called_class();
    if ($class == 'Drupal\migrate\Plugin\migrate\destination\Entity') {
      if (is_subclass_of($storage_controller , 'Drupal\Core\Config\Entity\ConfigEntityInterface')) {
        $class = 'Drupal\migrate\Plugin\migrate\destination\EntityConfigBase';
      }
      else {
        $class = 'Drupal\migrate\Plugin\migrate\destination\EntityContentBase';
      }
    }
    return new $class(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $storage_controller,
      array_keys($container->get('entity.manager')->getBundleInfo($entity_type))
    );
  }

  /**
   * Finds the entity type from configuration or plugin id.
   *
   * @param $configuration
   *   The plugin configuration.
   * @param $plugin_id
   *   The plugin id.
   *
   * @return string
   *   The entity type.
   * @throws \Drupal\migrate\MigrateException
   */
  protected static function getEntityType($configuration, $plugin_id) {
    if (isset($configuration['entity_type'])) {
      return $configuration['entity_type'];
    }
    elseif (substr($plugin_id, 0, 7) == 'entity_') {
      return substr($plugin_id, 7);
    }
    throw new MigrateException('No entity type given.');
  }

  /**
   * {@inheritdoc}
   */
  abstract public function import(Row $row);

  /**
   * {@inheritdoc}
   */
  abstract public function getIds();

  /**
   * {@inheritdoc}
   */
  public function fields(MigrationInterface $migration = NULL) {
    // TODO: Implement fields() method.
  }

  /**
   * Creates or loads an entity.
   *
   * @param Row $row
   * @return EntityInterface
   */
  protected function getEntity(Row $row) {
    $entity_id = $row->getDestinationProperty($this->getKey('id'));
    if (!empty($entity_id) && ($entity = $this->storageController->load($entity_id))) {
      $this->updateEntity($entity, $row);
    }
    else {
      $values = $row->getDestination();
      $bundle_key = $this->getKey('bundle');
      if ($bundle_key && !isset($values[$bundle_key])) {
        $values[$bundle_key] = reset($this->bundles);
      }
      $entity = $this->storageController->create($values);
      $entity->enforceIsNew();
    }
    return $entity;
  }

  /**
   * Updates an entity with the contents of a row.
   *
   * @param EntityInterface $entity
   * @param Row $row
   */
  protected function updateEntity(EntityInterface $entity, Row $row) {
    foreach ($row->getRawDestination() as $property => $value) {
      $this->updateEntityProperty($entity, explode(':', $property), $value);
    }
  }

  /**
   * Updates a (possible nested) entity property with a value.
   *
   * @param EntityInterface $entity
   * @param array $parents
   * @param $value
   * @return mixed
   */
  abstract protected function updateEntityProperty(EntityInterface $entity, array $parents, $value);

  /**
   * Returns a specific entity key.
   *
   * @param string $key
   *   The name of the entity key to return.
   *
   * @return string|bool
   *   The entity key, or FALSE if it does not exist.
   *
   * @see \Drupal\Core\Entity\EntityTypeInterface::getKeys()
   */
  protected function getKey($key) {
    return $this->storageController->entityInfo()->getKey($key);
  }

}
