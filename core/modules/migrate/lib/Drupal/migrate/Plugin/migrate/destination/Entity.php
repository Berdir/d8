<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\Entity.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Component\Utility\String;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\MigrateException;

/**
 * @PluginId("entity")
 */
class Entity extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * The migraiton
   *
   * @var \Drupal\migrate\Entity\MigrationInterface
   */

  /**
   * The entity storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  protected $storageController;

  /**
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * Constructs an entity destination plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param EntityStorageControllerInterface $storage_controller
   *   The storage controller for this entity type.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller, EntityTypeInterface $entity_type) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
    $this->storageController = $storage_controller;
    $this->entityType = $entity_type;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration = NULL) {
    if (isset($configuration['entity_type'])) {
      $entity_type = $configuration['entity_type'];
    }
    elseif (substr($plugin_id, 0, 7) == 'entity_') {
      $entity_type = substr($plugin_id, 7);
    }
    else {
      throw new MigrateException('No entity type given.');
    }
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')->getStorageController($entity_type),
      $container->get('entity.manager')->getDefinition($entity_type)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    // @TODO: add field handling. https://drupal.org/node/2164451
    // @TODO: add validation https://drupal.org/node/2164457
    $id_key = $this->entityType->getKey('id');
    if ($entity = $this->storageController->load($row->getDestinationProperty($id_key))) {
      $this->update($entity, $row);
    }
    else {
      $entity = $this->storageController->create($row->getDestination());
      $entity->enforceIsNew();
    }
    $entity->save();
    return array($entity->id());
  }

  /**
   * Update the entity with the contents of the row.
   *
   * @param EntityInterface $entity
   * @param Row $row
   */
  protected function update(EntityInterface $entity, Row $row) {
    foreach ($row->getRawDestination() as $property => $value) {
      $keys = explode(':', $property);
      if ($entity instanceof ContentEntityInterface) {
        $this->updateContentEntity($entity, $keys, $value);
      }
      if ($entity instanceof ConfigEntityInterface) {
        $this->updateConfigEntity($entity, $keys, $value);
      }
    }
  }

  /**
   * @param ContentEntityInterface $entity
   * @param array $parents
   * @param mixed $value
   * @throws \Drupal\migrate\MigrateException
   */
  protected function updateContentEntity(ContentEntityInterface $entity, array $parents, $value) {
    $ref = $entity;
    while ($parent = array_shift($parents)) {
      if ($ref instanceof ListInterface && is_numeric($parent)) {
        $ref = $ref->offsetGet($parent);
      }
      elseif ($ref instanceof ComplexDataInterface) {
        $ref = $ref->get($parent);
      }
      elseif ($ref instanceof TypedDataInterface) {
        // At this point we should have no more parents as there is nowhere to
        // descend.
        if ($parents) {
          throw new MigrateException(String::format('Unexpected extra keys @parents', array('@parents' => $parents)));
        }
      }
    }
    $ref->setValue($value);
  }

  /**
   * @param EntityInterface $entity
   * @param array $parents
   * @param $value
   */
  protected function updateConfigEntity(ConfigEntityInterface $entity, array $parents, $value) {
    $top_key = array_shift($parents);
    $entity_value = $entity->get($top_key);
    $ref = &$entity_value;
    foreach ($parents as $key) {
      if (is_array($ref) || $ref instanceof \ArrayAccess) {
        $ref = &$ref[$key];
      }
      elseif (is_object($ref)) {
        $ref = &$ref->$key;
      }
    }
    $ref = $value;
    $entity->set($top_key, $entity_value);
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
  public function fields(MigrationInterface $migration = NULL) {
    // TODO: Implement fields() method.
  }

}
