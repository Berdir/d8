<?php
namespace Drupal\taxonomy;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRenderController;

class TermRenderController extends EntityRenderController {

  public function buildContent(array &$entities = array(), $view_mode = 'full', $langcode = NULL) {
    parent::buildContent($entities, $view_mode, $langcode);

    foreach ($entities as $key => $entity) {
      // Try to add in the core taxonomy pieces like description.
      $bundle = $entity->bundle();
      $entity_view_mode = $entity->content['#view_mode'];
      $settings = field_view_mode_settings($this->entityType, $bundle);
	    $fields = field_extra_fields_get_display($this->entityType, $bundle, $entity_view_mode);
      if (!empty($entity->description) && isset($fields['description']) && $fields['description']['visible']) {
        $entity->content['description'] = array(
          '#markup' => check_markup($entity->description, $entity->format, '', TRUE),
          '#weight' => $fields['description']['weight'],
          '#prefix' => '<div class="taxonomy-term-description">',
          '#suffix' => '</div>',
        );
      }

      parent::prepareView($entity, $entity_view_mode, $langcode);
    }
    return $entity->content;
  }

  protected function getBuildDefaults(EntityInterface $entity, $view_mode, $langcode) {
    $return = parent::getBuildDefaults($entity, $view_mode, $langcode);

    // TODO: rename "term" to "taxonomy_term" in theme_taxonomy_term().
    $return['#term'] = $return["#{$this->entityType}"];
    unset($return["#{$this->entityType}"]);

    return $return;
  }

  protected function prepareBuild(array $build, EntityInterface $entity, $view_mode, $langcode = NULL) {
    $build['#attached']['css'][] = drupal_get_path('module', 'taxonomy') . '/taxonomy.css';
    return $build;
  }
}
