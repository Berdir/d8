<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\Entity.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateDestinationPlugin(
 *   id = "entity",
 *   derivative = "Drupal\migrate\Plugin\Derivative\MigrateEntity"
 * )
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
   *
   * When creating a generic entity destination plugin, an instance of
   * EntityConfigBase or EntityContentBase will be returned instead of
   * Entity as the two are too different to be handled by the same class.
   * EntityContentBase relies on TypeData while EntityConfigBase doesn't.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration = NULL) {
    $entity_type = static::getEntityType($plugin_id);
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')->getStorageController($entity_type),
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
  protected static function getEntityType($plugin_id) {
    // Remove entity:
    return substr($plugin_id, 7);
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
