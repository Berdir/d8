<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\Entity.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\MigrateException;

/**
 * @PluginId("entity")
 */
class Entity extends DestinationBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityStorageControllerInterface $storage_controller, EntityTypeInterface $entity_type) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storageController = $storage_controller;
    $this->entityType = $entity_type;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
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
      $this->setValue($entity, explode(':', $property), $value);
    }
  }

  /**
   * @param EntityInterface $entity
   * @param array $keys
   * @param $value
   */
  protected function setValue(EntityInterface $entity, array $keys, $value) {
    $ref = &$entity;
    foreach ($keys as $key) {
      if (is_array($ref) || $ref instanceof \ArrayAccess) {
        $ref = &$ref[$key];
      }
      elseif (is_object($ref)) {
        $ref = &$ref->$key;
      }
    }
    $ref = $value;
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
