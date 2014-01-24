<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\load\LoadBase.
 */

namespace Drupal\migrate\Plugin\load;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\Plugin\MigrateLoadInterface;
use Drupal\migrate\Entity\MigrationInterface;

/**
 * Base class for load plugins.
 */
abstract class LoadBase extends PluginBase implements MigrateLoadInterface {

  /**
   * @var \Drupal\migrate\Entity\MigrationInterface
   */
  protected $migration;

  /**
   * {@inheritdoc}
   */
  function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
  }

  /**
   * {@inheritdoc}
   */
  public function load($sub_id) {
    $entities = $this->loadMultiple(array($sub_id));
    return isset($entities[$sub_id]) ? $entities[$sub_id] : FALSE;
  }
}
