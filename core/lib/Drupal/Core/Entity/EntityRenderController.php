<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityRenderController.
 */

namespace Drupal\Core\Entity;
use Drupal\entity\Entity\EntityDisplay;

use Drupal\Core\Language\Language;

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
   * Implements \Drupal\Core\Entity\EntityRenderControllerInterface::buildContent().
   */
  public function buildContent(array $entities, array $displays, $view_mode) {
    field_attach_prepare_view($this->entityType, $entities, $displays);
    module_invoke_all('entity_prepare_view', $this->entityType, $entities, $displays, $view_mode);

    foreach ($entities as $entity) {
      // Remove previously built content, if exists.
      $entity->content = array(
        '#view_mode' => $view_mode,
      );
      $entity->content += field_attach_view($entity, $displays[$entity->bundle()]);
    }
  }

  /**
   * Provides entity-specific defaults to the build process.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which the defaults should be provided.
   * @param string $view_mode
   *   The view mode that should be used.
   *
   * @return array
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $return = array(
      '#theme' => $this->entityType,
      "#{$this->entityType}" => $entity,
      '#view_mode' => $view_mode,
    );
    return $return;
  }

  /**
   * Specific per-entity building.
   *
   * @param array $build
   *   The render array that is being created.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be prepared.
   * @param \Drupal\entity\Entity\EntityDisplay $display
   *   The entity_display object holding the display options configured for
   *   the entity components.
   * @param string $view_mode
   *   The view mode that should be used to prepare the entity.
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityDisplay $display, $view_mode) { }

  /**
   * Implements \Drupal\Core\Entity\EntityRenderControllerInterface::view().
   */
  public function view(EntityInterface $entity, $view_mode = 'full') {
    $buildList = $this->viewMultiple(array($entity), $view_mode);
    return $buildList[0];
  }

  /**
   * Implements \Drupal\Core\Entity\EntityRenderControllerInterface::viewMultiple().
   */
  public function viewMultiple(array $entities = array(), $view_mode = 'full') {
    // Build the view modes and display objects.
    $view_modes = array();
    $displays = array();
    foreach ($entities as $entity) {
      $bundle = $entity->bundle();

      // Allow modules to change the view mode.
      $entity_view_mode = $view_mode;
      drupal_alter('entity_view_mode', $entity_view_mode, $entity);
      // Store entities for rendering by view_mode.
      $view_modes[$entity_view_mode][$entity->id()] = $entity;

      // Load the corresponding display settings if not stored yet.
      if (!isset($displays[$entity_view_mode][$bundle])) {
        // Get the display object for this bundle and view mode.
        $display = entity_get_render_display($entity, $entity_view_mode);

        // Let modules alter the display.
        $display_context = array(
          'entity_type' => $this->entityType,
          'bundle' => $bundle,
          'view_mode' => $entity_view_mode,
        );
        drupal_alter('entity_display', $display, $display_context);

        $displays[$entity_view_mode][$bundle] = $display;
      }
    }

    foreach ($view_modes as $mode => $view_mode_entities) {
      $this->buildContent($view_mode_entities, $displays[$mode], $mode);
    }

    $view_hook = "{$this->entityType}_view";
    $build = array('#sorted' => TRUE);
    $weight = 0;
    foreach ($entities as $key => $entity) {
      $entity_view_mode = isset($entity->content['#view_mode']) ? $entity->content['#view_mode'] : $view_mode;
      $display = $displays[$entity_view_mode][$entity->bundle()];
      module_invoke_all($view_hook, $entity, $display, $entity_view_mode);
      module_invoke_all('entity_view', $entity, $display, $entity_view_mode);

      $build[$key] = $entity->content;
      // We don't need duplicate rendering info in $entity->content.
      unset($entity->content);

      $build[$key] += $this->getBuildDefaults($entity, $entity_view_mode);
      $this->alterBuild($build[$key], $entity, $display, $entity_view_mode);

      // Assign the weights configured in the display.
      foreach ($display->getComponents() as $name => $options) {
        if (isset($build[$key][$name])) {
          $build[$key][$name]['#weight'] = $options['weight'];
        }
      }

      $build[$key]['#weight'] = $weight++;

      // Allow modules to modify the render array.
      drupal_alter(array($view_hook, 'entity_view'), $build[$key], $entity, $display);
    }

    return $build;
  }
}
