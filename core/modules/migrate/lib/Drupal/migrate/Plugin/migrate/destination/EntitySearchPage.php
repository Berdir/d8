<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntitySearchPage.
 */

namespace Drupal\migrate\Plugin\migrate\destination;
use Drupal\Core\Entity\EntityInterface;
use Drupal\migrate\Row;

/**
 * @PluginID("entity_search_page")
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

}
