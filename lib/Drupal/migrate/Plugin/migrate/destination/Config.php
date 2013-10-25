<?php
/**
 * @file
 *   Provides Configuration Management destination plugin.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Plugin\MigrateDestinationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Persist data to the config system.
 *
 * @PluginId("config")
 */
class Config extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * The config name to use when saving.
   *
   * @var string
   */
  protected $configName;

  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ConfigFactory $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configName = $configuration['config_name'];
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  function import(Row $row) {
    $this->configFactory->get($this->configName)
      ->setData($row->getDestination())
      ->save();
  }

  public function rollbackMultiple(array $destination_keys) {
    throw new \MigrateException('Configuration can not be rolled back');
  }

  public function fields(Migration $migration = NULL) {
    // @todo Dynamically fetch fields using Config Schema API.
  }

  public function getIdsSchema() {
    return array($this->configName => array());
  }
}
