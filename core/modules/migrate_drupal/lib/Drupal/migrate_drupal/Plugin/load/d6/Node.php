<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\load\d6\Node.
 */

namespace Drupal\migrate_drupal\Plugin\load\d6;

use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\load\LoadBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PluginId("d6_node")
 */
class Node extends LoadBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\migrate\MigrationStorageController
   */
  protected $storageController;

  /**
   * Constructs the load plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param EntityStorageControllerInterface $storage_controller
   *   The migration storage controller.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->storageController = $storage_controller;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')->getStorageController('migration')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $sub_ids = NULL) {
    /** @var \Drupal\migrate\Entity\MigrationInterface $node_type_migration */
    $node_type_migration = $this->storageController->load('d6_node_type');
    $types = array();
    foreach ($node_type_migration->getSourcePlugin() as $node_type) {
      $types[] = $node_type['type'];
    }
    $ids_to_add = isset($sub_ids) ? array_intersect($types, $sub_ids) : $types;
    $migrations = array();
    foreach ($ids_to_add as $node_type) {
      $values = $this->migration->getExportProperties();
      $values['id'] = 'd6_node_' . $node_type;
      $values['source']['configuration']['type'] = $node_type;
      $migrations[$values['id']] = $this->storageController->create($values);
    }
    return $migrations;
  }

}
