<?php

/**
 * @file
 * Definition of Drupal\Core\Entity\EntityRenderController.
 */

namespace Drupal\Core\Entity;

/**
 * Base class for entity view controllers.
 */
class EntityRenderController implements EntityRenderControllerInterface {

  /**
   * The type of entities for which this controller is instantiated.
   *
   * @var string
   */
  protected $entityType;

  public function __construct($entity_type) {
    $this->entityType = $entity_type;
  }

  /**
   * @see \Drupal\Core\Entity\EntityRenderControllerInterface::buildContent()
   */
  public function buildContent(array &$entities = array(), $view_mode = 'full', $langcode = NULL) {
    // Allow modules to change the view mode.
    $context = array('langcode' => $langcode);

    $return = array();
    foreach ($entities as $key => &$entity) {
      // Remove previously built content, if exists.
      $entity->content = array();

      drupal_alter('entity_view_mode', $view_mode, $entity, $context);
      $entity->content['#view_mode'] = $view_mode;
      $return[$key] = $entity->content;
    }
    return $return;
  }

  /**
   * Build fields content.
   *
   * In case of a multiple view, "{$entity}_view_multiple"() already ran the
   * 'prepare_view' step. An internal flag prevents the operation from running
   * twice.
   *
   * @param EntityInterface $entity
   * @param string $view_mode
   * @param string $langcode
   */
  protected function prepareView(EntityInterface $entity, $view_mode, $langcode) {
    $entry = array($entity->id() => $entity);
    field_attach_prepare_view($this->entityType, $entry, $view_mode, $langcode);
    entity_prepare_view($this->entityType, $entry, $langcode);
    $entity->content += field_attach_view($this->entityType, $entity, $view_mode, $langcode);
  }

  /**
   * Provide entity-specific defaults to the build process.
   *
   * @param EntityInterface $entity
   * @param string $view_mode
   * @param string $langcode
   *
   * @return array
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode, $langcode) {
    $return = array(
      '#theme' => $this->entityType,
      "#{$this->entityType}" => $entity,
      '#view_mode' => $view_mode,
      '#langcode' => $langcode,
    );

    return $return;
  }

  /**
   * Specific per-entity building.
   *
   * This method is typically not invoked by the child reimplementations, but
   * simply replaced.
   *
   * @param array $build
   * @param EntityInterface $entity
   * @param string $view_mode
   * @param string $langcode
   *
   * @return array
   *   The build array.
   */
  protected function prepareBuild(array $build, EntityInterface $entity, $view_mode, $langcode = NULL) {
    return $build;
  }

  /**
   * @see \Drupal\Core\Entity\EntityRenderControllerInterface::viewSingle()
   */
  public function viewSingle(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $buildList = $this->viewMultiple(array($entity), $view_mode, $langcode);
    return $buildList[0];
  }

  /**
   * @see \Drupal\Core\Entity\EntityRenderControllerInterface::viewMultiple()
   */
  public function viewMultiple(array $entities = array(), $view_mode = 'full', $weight = 0, $langcode = NULL) {
    if (!isset($langcode)) {
      $langcode = language(LANGUAGE_TYPE_CONTENT)->langcode;
    }
    $this->buildContent($entities, $view_mode, $langcode);

    $view_hook = "{$this->entityType}_view";
    $build = array('#sorted' => TRUE);
    foreach ($entities as $key => $entity) {
      $entity_view_mode = isset($entity->content['#view_mode'])
        ? $entity->content['#view_mode']
        : $view_mode;
      module_invoke_all($view_hook, $entity, $entity_view_mode, $langcode);
      module_invoke_all('entity_view', $entity, $entity_view_mode, $langcode);

      $build[$key] = $entity->content;
      // We don't need duplicate rendering info in $entity->content.
      unset($entity->content);

      $build[$key] += $this->getBuildDefaults($entity, $entity_view_mode, $langcode);
      $build[$key] += $this->prepareBuild($build[$key], $entity, $entity_view_mode, $langcode);
      $build[$key]['#weight'] = $weight++;

      // Allow modules to modify the structured comment.
      drupal_alter(array($view_hook, 'entity_view'), $build[$key], $entity);
    }

    return $build;
  }
}

