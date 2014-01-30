<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntitySearchPage.
 */

namespace Drupal\migrate\Plugin\migrate\destination;
use Drupal\Core\Entity\EntityInterface;
use Drupal\migrate\Row;

/**
 * @MigrateDestinationPlugin(
 *   id = "entity:entity_search_page"
 * )
 */
class EntitySearchPage extends EntityConfigBase {

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\search\Entity\SearchPage $entity
   */
  protected function updateEntity(EntityInterface $entity, Row $row) {
    $entity->setPlugin($row->getDestinationProperty('plugin'));
    $entity->getPlugin()->setConfiguration($row->getDestinationProperty('configuration'));
  }

}
