<?php

/**
 * @file
 * Contains \Drupal\entity_test\EntityTestList.
 */

namespace Drupal\entity_test;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityList;

/**
 * Provides a list for entity_test.
 */
class EntityTestList extends EntityList {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Label');
    $header['id'] = t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['id'] = $entity->id();
    return $row + parent::buildRow($entity);
  }

}
