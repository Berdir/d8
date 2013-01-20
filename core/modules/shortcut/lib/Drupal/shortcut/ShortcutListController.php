<?php

/**
 * Contains \Drupal\shortcut\ShortcutListController.
 */

namespace Drupal\shortcut;

use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of contact categories.
 */
class ShortcutListController extends ConfigEntityListController {

  /**
   * Overrides \Drupal\Core\Entity\EntityListController::buildHeader().
   */
  public function buildHeader() {
    $row['label'] = t('Name');
    $row['operations'] = t('Operations');
    return $row;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityListController::getOperations().
   */
  public function getOperations(EntityInterface $entity) {
    $uri = $entity->uri();
    $operations['list'] = array(
      'title' => t('list links'),
      'href' => $uri['path'],
    );
    $operations['edit'] = array(
      'title' => t('edit set'),
      'href' => $uri['path'] . '/edit',
      'options' => $uri['options'],
      'weight' => 10,
    );
    if (shortcut_set_delete_access($entity)) {
      $operations['delete'] = array(
        'title' => t('delete set'),
        'href' => $uri['path'] . '/delete',
        'options' => $uri['options'],
        'weight' => 100,
      );
    }
    return $operations;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityListController::buildRow().
   */
  public function buildRow(EntityInterface $entity) {
    $row['name'] = check_plain($entity->label());
    $row['operations']['data'] = $this->buildOperations($entity);
    return $row;
  }

}
