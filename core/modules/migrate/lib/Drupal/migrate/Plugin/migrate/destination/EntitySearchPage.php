<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntitySearchPage.
 */

namespace Drupal\migrate\Plugin\migrate\destination;
use Drupal\Core\Entity\EntityInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @PluginId("entity_search_page")
 */
class EntitySearchPage extends Entity {

  /**
   * {@inheritdoc}
   */
  protected function update(EntityInterface $entity, Row $row) {
    /** @var \Drupal\search\Entity\SearchPage $entity */
    $entity->setPlugin($row->getDestinationProperty('plugin'));
    $entity->getPlugin()->setConfiguration($row->getDestinationProperty('configuration'));
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    $configuration['entity_type'] = 'search_page';
    return parent::create($container, $configuration, $plugin_id, $plugin_definition);
  }
}
