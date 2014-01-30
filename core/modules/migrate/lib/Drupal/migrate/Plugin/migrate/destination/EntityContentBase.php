<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityBaseContent.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\field\FieldInfo;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityContentBase extends Entity {

  /**
   * @var \Drupal\field\FieldInfo
   */
  protected $fieldInfo;

  /**
   * {@inheritdoc}
   *
   * @param FieldInfo $field_info
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller, array $bundles, MigratePluginManager $plugin_manager, FieldInfo $field_info) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $storage_controller, $bundles);
    $this->migrateEntityFieldPluginManager = $plugin_manager;
    $this->fieldInfo = $field_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration = NULL) {
    $entity_type = static::getEntityType($plugin_id);
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')->getStorageController($entity_type),
      array_keys($container->get('entity.manager')->getBundleInfo($entity_type)),
      $container->get('plugin.manager.migrate.entity_field'),
      $container->get('field.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    if ($all_instances = $this->fieldInfo->getInstances($this->storageController->getEntityTypeId())) {
      /** @var \Drupal\Field\Entity\FieldInstance[] $instances */
      $instances = array();
      if ($bundle_key = $this->getKey('bundle')) {
        $bundle = $row->getDestinationProperty($bundle_key);
        if (isset($all_instances[$bundle])) {
          $instances = $all_instances[$bundle];
        }
      }
      foreach ($instances as $field_name => $instance) {
        $field_type = $instance->getType();
        if ($this->migrateEntityFieldPluginManager->getDefinition($field_type)) {
          $destination_value = $this->migrateEntityFieldPluginManager->createInstance($field_type)->import($instance, $row->getDestinationProperty($field_name));
          // @TODO: check for NULL return? Add an unset to $row? Maybe needed in exception handling? Propagate exception?
          $row->setDestinationProperty($field_name, $destination_value);
        }
      }
    }
    $entity = $this->getEntity($row);
    $entity->save();
    return array($entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $id_key = $this->getKey('id');
    $ids[$id_key]['type'] = 'integer';
    return $ids;
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param array $parents
   * @param mixed $value
   * @throws \Drupal\migrate\MigrateException
   */
  protected function updateEntityProperty(EntityInterface $entity, array $parents, $value) {
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

}
