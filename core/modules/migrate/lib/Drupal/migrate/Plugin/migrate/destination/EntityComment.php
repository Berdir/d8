<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityComment.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\KeyValueStore\StateInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PluginID("entity_comment")
 */
class EntityComment extends Entity {

  /**
   * The state storage object.
   *
   * @var \Drupal\Core\KeyValueStore\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\KeyValueStore\StateInterface $state
   *   The state storage object.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller, array $bundles, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $storage_controller, $bundles);
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration = NULL) {
    $entity_type = static::getEntityType($configuration, $plugin_id);
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('entity.manager')->getStorageController($entity_type),
      array_keys($container->get('entity.manager')->getBundleInfo($entity_type)),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    if (($stub = !$row->getSourceProperty('entity_type')) && ($state = $this->state->get('comment.maintain_entity_statistics', 0))) {
      $this->state->set('comment.maintain_entity_statistics', 0);
    }
    $return = parent::import($row);
    if ($stub && $state) {
      $this->state->set('comment.maintain_entity_statistics', $state);
    }
    return $return;
  }

}
