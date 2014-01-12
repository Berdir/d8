<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityFieldEntity.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PluginId("entity_field_entity")
 */
class EntityFieldEntity extends Entity {

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    $row->setDestinationProperty('id', implode('.', array(
      $row->getDestinationProperty('entity_type'),
      $row->getDestinationProperty('name'),
    )));
    return parent::import($row);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    $configuration['entity_type'] = 'field_entity';
    return parent::create($container, $configuration, $plugin_id, $plugin_definition);
  }

}
