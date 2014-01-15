<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\Process\d6\FilterFormatRole.
 */


namespace Drupal\migrate_drupal\Plugin\migrate\Process\d6;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Migrate filter format serial to string id in permission name.
 *
 * @MigrateProcessPlugin(
 *   id = "filter_format_permission",
 *   handle_multiples = TRUE
 * )
 */
class FilterFormatPermission extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\migrate\Plugin\MigratePluginManager
   */
  protected $processPluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, MigratePluginManager $process_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migration = $migration;
    $this->processPluginManager = $process_plugin_manager;
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
      $container->get('plugin.manager.migrate.process')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Migrate filter format serial to string id in permission name.
   */
  public function transform($value, MigrateExecutable $migrate_executable, Row $row, $destination_property) {
    $rid = $row->getSourceProperty('rid');
    if ($formats = $row->getSourceProperty("filter_permissions:$rid")) {
      foreach ($formats as $format) {
        $configuration = array('migration' => 'd6_filter_format');
        $new_id = $this->processPluginManager
          ->createInstance('migration', $configuration, $this->migration)
          ->transform(array($format), $migrate_executable, $row, $destination_property);
        if ($new_id) {
          $value[] = 'use text format ' . $new_id[0];
        }
      }
    }
    return $value;
  }

}
