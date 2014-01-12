<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityFieldInstance.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PluginId("entity_field_instance")
 */
class EntityFieldInstance extends Entity {

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    $row->setDestinationProperty('id', implode('.', array(
      $row->getDestinationProperty('entity_type'),
      $row->getDestinationProperty('bundle'),
      $row->getDestinationProperty('field_name'),
    )));
    return parent::import($row);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    $configuration['entity_type'] = 'field_instance';
    return parent::create($container, $configuration, $plugin_id, $plugin_definition);
  }
}
