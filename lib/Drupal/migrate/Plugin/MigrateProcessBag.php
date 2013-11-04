<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\FieldMappingBag.
 */

namespace Drupal\migrate\Plugin;

use Drupal\Component\Plugin\DefaultPluginBag;
use Drupal\Component\Plugin\Exception\UnknownPluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\migrate\Entity\MigrationInterface;

/**
 * Contains all active process plugins on a migration.
 */
class MigrateProcessBag extends DefaultPluginBag {

  /**
   * The migration entity this process bag is attached to.
   *
   * @var \Drupal\migrate\Entity\MigrationInterface
   */
  protected $migration;

  /**
   * The key within the plugin configuration that contains the plugin ID.
   *
   * @var string
   */
  protected $pluginKey = 'plugin';

  /**
   * @var \Drupal\migrate\Plugin\MigratePluginManager
   */
  protected $manager;

  /**
   * Constructs a new MigrateProcessBag.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The plugin manager to initialize process plugins.
   * @param array $configurations
   *   (optional) An associative array containing the initial configuration for
   *   each plugin in the bag, keyed by plugin instance ID.
   * @param \Drupal\migrate\Entity\MigrationInterface $migration
   *   The migration entity this process bag is attached to.
   */
  public function __construct(PluginManagerInterface $manager, array $configurations = array(), MigrationInterface $migration) {
    parent::__construct($manager, $configurations);
    $this->migration = $migration;
  }

  /**
   * {@inheritdoc}
   *
   * Extends in order to pass the migration entity to the plugin manager.
   */
  public function initializePlugin($instance_id) {
    $this->configurations[$instance_id] += array('plugin' => 'copy_from_source');
    $configuration = isset($this->configurations[$instance_id]) ? $this->configurations[$instance_id] : array();
    if (!isset($configuration[$this->pluginKey])) {
      throw new UnknownPluginException($instance_id);
    }
    $plugin_key = $configuration[$this->pluginKey];
    unset($configuration[$this->pluginKey]);
    $this->pluginInstances[$instance_id] = $this->manager->createInstance($plugin_key, $configuration, $this->migration);
    $this->addInstanceID($instance_id);
  }

}
