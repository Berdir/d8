<?php
/**
 * @file
 *   Provides Configuration Management destination plugin.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Symfony\Component\Yaml\Dumper;

/**
 * Persist data to the config system.
 *
 * @PluginId("config")
 */
class Config extends MigrateDestination {

  /**
   * The config name to use when saving.
   *
   * @var string
   */
  protected $config_name;

  public function __construct(array $options) {
    $this->config_name = $options['config_name'];
  }

  /**
   * {@inheritdoc}
   */
  function import(Row $row) {
    $dumper = new Dumper();
    $dumper->setIndentation(2);
    $yaml = $dumper->dump($row->data);
    $config = Drupal::config($this->config_name);
    $config->setData($yaml);
    $config->save();
  }

  public function getKeySchema() {
    return array($this->config_name);
  }

  public function public function rollbackMultiple(array $destination_keys) {
    // Not wise to delete config? Nothing to do.
  }

  public function fields(Migration $migration = NULL) {
    return array(
      'data' => t('A PHP array to be saved as config.'),
    );
  }
}
