<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\SqlBase.
 */

namespace Drupal\migrate\Plugin\migrate\source;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class SqlBase extends SourceBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  public function __construct(array $configuration, $plugin_id, array $plugin_definition, CacheBackendInterface $cache, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
    $this->sourceKey = $source_key;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $configuration['source_key'],
      $container->get()
      $container->get('database')
    );
  }

  /**
   * @return \Drupal\Core\Database\Query\SelectInterface
   */
  abstract function query();

}
