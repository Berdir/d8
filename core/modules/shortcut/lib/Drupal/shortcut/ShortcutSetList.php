<?php

/**
 * @file
 * Contains \Drupal\shortcut\ShortcutSetList.
 */

namespace Drupal\shortcut;

use Drupal\Core\Config\Entity\ConfigEntityList;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of shortcut sets.
 */
class ShortcutSetList extends ConfigEntityList {

  /**
   * Overrides \Drupal\Core\Entity\EntityList::buildHeader().
   */
  public function buildHeader() {
    $header['name'] = t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    $uri = $entity->uri();

    if (isset($operations['edit'])) {
      $operations['edit']['title'] = t('Edit menu');
      $operations['edit']['href'] = $uri['path'] . '/edit';
    }

    $operations['list'] = array(
      'title' => t('List links'),
      'href' => $uri['path'],
    );
    return $operations;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityList::buildRow().
   */
  public function buildRow(EntityInterface $entity) {
    $row['name'] = $this->getLabel($entity);
    return $row + parent::buildRow($entity);
  }

}
