<?php

/**
 * @file
 * Definition of Drupal\taxonomy\TermRenderController.
 */

namespace Drupal\taxonomy;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRenderController;
use Drupal\entity\Entity\EntityDisplay;

/**
 * Render controller for taxonomy terms.
 */
class TermRenderController extends EntityRenderController {

  /**
   * Overrides Drupal\Core\Entity\EntityRenderController::buildContent().
   */
  public function buildContent(array $entities, array $displays, $view_mode) {
    parent::buildContent($entities, $displays, $view_mode);

    foreach ($entities as $entity) {
      // Add the description if enabled.
      $display = $displays[$entity->bundle()];
      if (!empty($entity->description->value) && $display->getComponent('description')) {
        $entity->content['description'] = array(
          '#markup' => check_markup($entity->description->value, $entity->format->value, '', TRUE),
          '#prefix' => '<div class="taxonomy-term-description">',
          '#suffix' => '</div>',
        );
      }
    }
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityRenderController::getBuildDefaults().
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $return = parent::getBuildDefaults($entity, $view_mode);

    // TODO: rename "term" to "taxonomy_term" in theme_taxonomy_term().
    $return['#term'] = $return["#{$this->entityType}"];
    unset($return["#{$this->entityType}"]);

    return $return;
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityRenderController::alterBuild().
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityDisplay $display, $view_mode) {
    parent::alterBuild($build, $entity, $display, $view_mode);
    $build['#attached']['css'][] = drupal_get_path('module', 'taxonomy') . '/css/taxonomy.module.css';
    $build['#contextual_links']['taxonomy'] = array('taxonomy/term', array($entity->id()));
  }

}
