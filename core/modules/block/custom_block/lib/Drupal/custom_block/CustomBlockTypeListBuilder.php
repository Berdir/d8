<?php

/**
 * @file
 * Contains \Drupal\custom_block\CustomBlockTypeListBuilder.
 */

namespace Drupal\custom_block;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of custom block type entities.
 *
 * @see \Drupal\custom_block\Entity\CustomBlockType
 */
class CustomBlockTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    // Place the edit operation after the operations added by field_ui.module
    // which have the weights 15, 20, 25.
    if (isset($operations['edit'])) {
      $operations['edit']['weight'] = 30;
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['type'] = t('Block type');
    $header['description'] = t('Description');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $uri = $entity->urlInfo();
    $row['type'] = \Drupal::l($entity->label(), $uri['route_name'], $uri['route_parameters'], $uri['options']);
    $row['description'] = filter_xss_admin($entity->description);
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getTitle() {
    return $this->t('Custom block types');
  }

}
