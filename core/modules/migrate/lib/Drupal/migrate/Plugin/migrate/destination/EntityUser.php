<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityUser.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigratePassword;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PluginId("entity_user")
 */
class EntityUser extends Entity {

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
   * @param \Drupal\Core\Password\PasswordInterface $password
   *   The password service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller, array $bundles, PasswordInterface $password) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $storage_controller, $bundles);
    if (isset($configuration['md5_passwords'])) {
      $this->password = $password;
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration = NULL) {
    $entity_type = static::getEntityType($configuration, $plugin_id);
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')->getStorageController($entity_type),
      array_keys($container->get('entity.manager')->getBundleInfo($entity_type)),
      $container->get('password')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    if ($this->password) {
      if ($this->password instanceof MigratePassword) {
        $this->password->enableMd5Prefixing();
      }
      else {
        throw new MigrateException('Password service has been altered by another module, aborting.');
      }
    }
    $ids = parent::import($row);
    if ($this->password) {
      $this->password->disableMd5Prefixing();
    }

    return $ids;
  }

}
