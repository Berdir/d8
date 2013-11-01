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

class MigrateProcessBag extends DefaultPluginBag {

  /**
   * @var \Drupal\migrate\Entity\MigrationInterface
   */
  protected $migration;

  protected $pluginKey = 'plugin';

  public function __construct(PluginManagerInterface $manager, array $configurations = array(), MigrationInterface $migration) {
    parent::__construct($manager, $configurations);
    $this->migration = $migration;
  }

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\migrate\Plugin\ProcessInterface
   */
  public function &get($instance_id) {
    return parent::get($instance_id);
  }

  /**
   * {@inheritdoc}
   */
  public function initializePlugin($instance_id) {
    $this->configurations[$instance_id] += array('plugin' => 'property_map');
    $configuration = isset($this->configurations[$instance_id]) ? $this->configurations[$instance_id] : array();
    if (!isset($configuration[$this->pluginKey])) {
      throw new UnknownPluginException($instance_id);
    }
    $this->pluginInstances[$instance_id] = $this->manager->createInstance($configuration[$this->pluginKey], $configuration, $this->migration);
    $this->addInstanceID($instance_id);
  }

}
