<?php

/**
 * Contains \Drupal\menu\MenuListController.
 */

namespace Drupal\menu;

use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of contact categories.
 */
class MenuListController extends ConfigEntityListController {

  /**
   * Overrides Drupal\Core\Entity\EntityListController::buildHeader().
   */
  public function buildHeader() {
    $row['title'] = t('Title');
    $row['operations'] = t('Operations');
    return $row;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityListController::buildRow().
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = array(
      'data' => array(
        '#theme' => 'menu_admin_overview',
        '#label' => $entity->label(),
        '#id' => $entity->id(),
        '#description' => $entity->description,
      ),
    );
    $row['operations']['data'] = $this->buildOperations($entity);
    return $row;
  }

  /**
   * Implements Drupal\Core\Entity\EntityListController::getOperations();
   */
  public function getOperations(EntityInterface $menu) {
    $uri = $menu->uri();
    $path = $uri['path'];

    $operations['list'] = array(
      'title' => t('list links'),
      'href' => $path,
      'weight' => 0,
    );
    $operations['edit'] = array(
      'title' => t('edit menu'),
      'href' => $path . '/edit',
      'weight' => 5,
    );
    $operations['add'] = array(
      'title' => t('add link'),
      'href' => $path . '/add',
      'weight' => 10,
    );
    return $operations;
  }

}
