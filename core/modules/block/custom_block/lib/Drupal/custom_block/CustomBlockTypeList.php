<?php

/**
 * @file
 * Contains \Drupal\custom_block\CustomBlockTypeList.
 */

namespace Drupal\custom_block;

use Drupal\Core\Config\Entity\ConfigEntityList;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of custom block types.
 */
class CustomBlockTypeList extends ConfigEntityList {

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
   * Overrides \Drupal\Core\Entity\EntityList::buildHeader().
   */
  public function buildHeader() {
    $header['type'] = t('Block type');
    $header['description'] = t('Description');
    return $header + parent::buildHeader();
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityList::buildRow().
   */
  public function buildRow(EntityInterface $entity) {
    $uri = $entity->uri();
    $row['type'] = l($entity->label(), $uri['path'], $uri['options']);
    $row['description'] = filter_xss_admin($entity->description);
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    // @todo Remove this once https://drupal.org/node/2032535 is in.
    drupal_set_title(t('Custom block types'));
    return parent::render();
  }

}
