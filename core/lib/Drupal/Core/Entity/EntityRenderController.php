<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityRenderController.
 */

namespace Drupal\Core\Entity;
use Drupal\entity\Plugin\Core\Entity\EntityDisplay;

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

  /**
   * The entity info array.
   *
   * @var array
   *
   * @see entity_get_info()
   */
  protected $entityInfo;

  /**
   * An array of view mode info for the type of entities for which this
   * controller is instantiated.
   *
   * @var array
   */
  protected $viewModesInfo;

  /**
   * The cache bin used to store the render cache.
   *
   * @todo Defaults to 'cache' for now, until http://drupal.org/node/1194136 is
   * fixed.
   *
   * @var string
   */
  protected $cacheBin = 'cache';

  public function __construct($entity_type) {
    $this->entityType = $entity_type;
    $this->entityInfo = entity_get_info($entity_type);
    $this->viewModesInfo = entity_get_view_modes($entity_type);
  }

  /**
   * Implements \Drupal\Core\Entity\EntityRenderControllerInterface::buildContent().
   */
  public function buildContent(array $entities, array $displays, $view_mode, $langcode = NULL) {
    field_attach_prepare_view($this->entityType, $entities, $displays, $langcode);
    module_invoke_all('entity_prepare_view', $this->entityType, $entities, $displays, $view_mode);

    foreach ($entities as $entity) {
      // Remove previously built content, if exists.
      $entity->content = array(
        '#view_mode' => $view_mode,
      );
      $entity->content += field_attach_view($entity, $displays[$entity->bundle()], $langcode);
    }
  }

  /**
   * Provides entity-specific defaults to the build process.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which the defaults should be provided.
   * @param string $view_mode
   *   The view mode that should be used.
   * @param string $langcode
   *   (optional) For which language the entity should be prepared, defaults to
   *   the current content language.
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

    // Cache the rendered output if permitted by the view mode settings.
    $view_mode_is_cacheable = !isset($this->viewModesInfo[$view_mode]) || (isset($this->viewModesInfo[$view_mode]) && $this->viewModesInfo[$view_mode]['cache']);
    if (!isset($entity->in_preview) && $this->entityInfo['render_cache'] && $view_mode_is_cacheable) {
      $return['#cache'] = array(
        'keys' => array('entity_view', $this->entityType ,$entity->id(), $view_mode),
        'granularity' => DRUPAL_CACHE_PER_ROLE,
        'bin' => $this->cacheBin,
        'tags' => array(
          $this->entityType . '_view' => TRUE,
          $this->entityType => array($entity->id()),
        ),
      );
    }

    return $return;
  }

  /**
   * Specific per-entity building.
   *
   * @param array $build
   *   The render array that is being created.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be prepared.
   * @param \Drupal\entity\Plugin\Core\Entity\EntityDisplay $display
   *   The entity_display object holding the display options configured for
   *   the entity components.
   * @param string $view_mode
   *   The view mode that should be used to prepare the entity.
   * @param string $langcode
   *   (optional) For which language the entity should be prepared, defaults to
   *   the current content language.
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityDisplay $display, $view_mode, $langcode = NULL) { }

  /**
   * Implements \Drupal\Core\Entity\EntityRenderControllerInterface::view().
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $buildList = $this->viewMultiple(array($entity), $view_mode, $langcode);
    return $buildList[0];
  }

  /**
   * Implements \Drupal\Core\Entity\EntityRenderControllerInterface::viewMultiple().
   */
  public function viewMultiple(array $entities = array(), $view_mode = 'full', $langcode = NULL) {
    if (!isset($langcode)) {
      $langcode = language(LANGUAGE_TYPE_CONTENT)->langcode;
    }

    // Build the view modes and display objects.
    $view_modes = array();
    $displays = array();
    $context = array('langcode' => $langcode);
    foreach ($entities as $entity) {
      $bundle = $entity->bundle();

      // Allow modules to change the view mode.
      $entity_view_mode = $view_mode;
      drupal_alter('entity_view_mode', $entity_view_mode, $entity, $context);
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
      $this->buildContent($view_mode_entities, $displays[$mode], $mode, $langcode);
    }

    $view_hook = "{$this->entityType}_view";
    $build = array('#sorted' => TRUE);
    $weight = 0;
    foreach ($entities as $key => $entity) {
      $entity_view_mode = isset($entity->content['#view_mode']) ? $entity->content['#view_mode'] : $view_mode;
      $display = $displays[$entity_view_mode][$entity->bundle()];
      module_invoke_all($view_hook, $entity, $display, $entity_view_mode, $langcode);
      module_invoke_all('entity_view', $entity, $display, $entity_view_mode, $langcode);

      $build[$key] = $entity->content;
      // We don't need duplicate rendering info in $entity->content.
      unset($entity->content);

      $build[$key] += $this->getBuildDefaults($entity, $entity_view_mode, $langcode);
      $this->alterBuild($build[$key], $entity, $display, $entity_view_mode, $langcode);

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

  /**
   * Implements \Drupal\Core\Entity\EntityRenderControllerInterface::resetCache().
   */
  public function resetCache(array $entities = NULL) {
    if (isset($entities)) {
      $tags = array();
      foreach ($entities as $entity) {
        $tags[$this->entityType][$entity->id()] = $entity->id();

        // @todo Remove when all entities are converted to EntityNG.
        if ($entity->getPropertyDefinitions() === NULL) {
          continue;
        }

        // Add all the referenced entity types and IDs to the tags that will be
        // cleared.
        foreach ($entity->getPropertyDefinitions() as $name => $definition) {
          if ($definition['type'] == 'entity_reference_field' && $field_values = $entity->get($name)->getValue()) {
            foreach ($field_values as $value) {
              $tags[$definition['settings']['target_type']][$value['target_id']] = $value['target_id'];
            }
          }
        }
      }
      \Drupal::cache($this->cacheBin)->deleteTags($tags);
    }
    else {
      \Drupal::cache($this->cacheBin)->deleteTags(array($this->entityType . '_view' => TRUE));
    }
  }
}
