<?php
/**
 * @file
 *   Provides Configuration Management destination plugin.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\Config as ConfigObject;

/**
 * Persist data to the config system.
 *
 * @MigrateDestination(
 *   id = "config"
 * )
 */
class Config extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param array $plugin_definition
   * @param ConfigObject $config
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, ConfigObject $config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->config = $config;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('config.factory')->get($configuration['config_name'])
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = array()) {
    $this->config
      ->setData($row->getDestination())
      ->save();
  }

  /**
   * @param array $destination_keys
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function rollbackMultiple(array $destination_keys) {
    throw new MigrateException('Configuration can not be rolled back');
  }

  /**
   * {@inheritdoc}
   */
  public function fields(MigrationInterface $migration = NULL) {
    // @todo Dynamically fetch fields using Config Schema API.
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array();
  }
}
