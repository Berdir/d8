<?php

/**
 * @file
 * Definition of Drupal\taxonomy\TermRenderController.
 */

namespace Drupal\taxonomy;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRenderController;
use Drupal\entity\Plugin\Core\Entity\EntityDisplay;

/**
 * Render controller for taxonomy terms.
 */
class TermRenderController extends EntityRenderController {

  /**
   * Overrides \Drupal\Core\Entity\EntityRenderController::getBuildDefaults().
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode, $langcode) {
    $return = parent::getBuildDefaults($entity, $view_mode, $langcode);

    // TODO: rename "term" to "taxonomy_term" in theme_taxonomy_term().
    $return['#term'] = $return["#{$this->entityType}"];
    unset($return["#{$this->entityType}"]);

    return $return;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityRenderController::alterBuild().
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityDisplay $display, $view_mode, $langcode = NULL) {
    parent::alterBuild($build, $entity, $display, $view_mode, $langcode);
    $build['#attached']['css'][] = drupal_get_path('module', 'taxonomy') . '/css/taxonomy.module.css';
    $build['#contextual_links']['taxonomy'] = array('taxonomy/term', array($entity->id()));
  }

}
