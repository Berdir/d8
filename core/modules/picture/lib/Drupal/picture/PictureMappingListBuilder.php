<?php

/**
 * @file
 * Contains \Drupal\picture\PictureMappingListBuilder.
 */

namespace Drupal\picture;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of picture mapping entities.
 *
 * @see \Drupal\picture\Entity\PictureMapping
 */
class PictureMappingListBuilder extends ConfigEntityListBuilder {

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

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    $operations['duplicate'] = array(
      'title' => t('Duplicate'),
      'weight' => 15,
    ) + $entity->urlInfo('duplicate-form');
    return $operations;
  }

}
