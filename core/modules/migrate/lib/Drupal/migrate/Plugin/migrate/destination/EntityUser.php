<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityUser.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\field\FieldInfo;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigratePassword;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateDestination(
 *   id = "entity:user"
 * )
 */
class EntityUser extends EntityContentBase {

  /**
   * The password service class.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $password;

  /**
   * Builds an user entity destination.
   *
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
   * @param \Drupal\migrate\Plugin\MigratePluginManager $plugin_manager
   *   The migrate plugin manager.
   * @param \Drupal\field\FieldInfo $field_info
   *   The field and instance definitions service.
   * @param \Drupal\Core\Password\PasswordInterface $password
   *   The password service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller, array $bundles, MigratePluginManager $plugin_manager, FieldInfo $field_info, PasswordInterface $password) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $storage_controller, $bundles, $plugin_manager, $field_info);
    if (isset($configuration['md5_passwords'])) {
      $this->password = $password;
    }
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
      $container->get('field.info'),
      $container->get('password')
    );
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\migrate\MigrateException
   */
  public function import(Row $row, array $old_destination_id_values = array()) {
    if ($this->password) {
      if ($this->password instanceof MigratePassword) {
        $this->password->enableMd5Prefixing();
      }
      else {
        throw new MigrateException('Password service has been altered by another module, aborting.');
      }
    }
    $ids = parent::import($row, $old_destination_id_values);
    if ($this->password) {
      $this->password->disableMd5Prefixing();
    }

    return $ids;
  }

}
