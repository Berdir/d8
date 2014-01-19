<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\UrlAlias.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\Core\Path\Path;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * @PluginId("url_alias")
 */
class UrlAlias extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * The path crud service.
   *
   * @var Path $path
   */
  protected $path;

  /**
   * Constructs an entity destination plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param MigrationInterface $migration
   *   The migration.
   * @param \Drupal\Core\Path\Path $path
   *   The path crud service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, Path $path) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->path = $path;
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
      $container->get('path.crud')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {

    $path = $this->path->save(
      $row->getDestinationProperty('source'),
      $row->getDestinationProperty('alias'),
      $row->getDestinationProperty('langcode')
    );

    return array($path['pid']);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['pid']['type'] = 'integer';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields(MigrationInterface $migration = NULL) {
    // TODO: Implement fields() method.
  }

}
