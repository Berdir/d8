<?php
/**
 * @file
 *   Provides Configuration Management destination plugin.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\Config as ConfigObject;

/**
 * Persist data to the config system.
 *
 * @PluginID("d8_config")
 */
class Config extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ConfigObject $config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')->get($configuration['config_name'])
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    $destination = $row->getDestination();
    foreach ($row->getDestinationArrayKeys() as $keys) {
      $property = NestedArray::getValue($destination, $keys);
      $this->config->set(implode('.', $keys), $property['value']);
    }
    $this->config->save();
  }

  public function rollbackMultiple(array $destination_keys) {
    throw new \MigrateException('Configuration can not be rolled back');
  }

  public function fields(Migration $migration = NULL) {
    // @todo Dynamically fetch fields using Config Schema API.
  }

  public function getIdsSchema() {
    return array($this->config->getName() => array());
  }
}
