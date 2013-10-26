<?php

/**
 * @file
 * Contains \Drupal\config_test\ConfigTestList.
 */

namespace Drupal\config_test;

use Drupal\Core\Config\Entity\ConfigEntityList;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a list for config_test.
 */
class ConfigTestList extends ConfigEntityList {

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
