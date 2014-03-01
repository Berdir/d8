<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityViewBuilder.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\entity\Entity\EntityViewDisplay;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for entity view controllers.
 */
class EntityViewBuilder extends EntityControllerBase implements EntityControllerInterface, EntityViewBuilderInterface {

  /**
   * The type of entities for which this controller is instantiated.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * Information about the entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The cache bin used to store the render cache.
   *
   * @todo Defaults to 'cache' for now, until http://drupal.org/node/1194136 is
   * fixed.
   *
   * @var string
   */
  protected $cacheBin = 'cache';

  /**
   * The language manager.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   */
  protected $languageManager;

  /**
   * Constructs a new EntityViewBuilder.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager) {
    $this->entityTypeId = $entity_type->id();
    $this->entityType = $entity_type;
    $this->entityManager = $entity_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildContent(array $entities, array $displays, $view_mode, $langcode = NULL) {
    $entities_by_bundle = array();
    foreach ($entities as $id => $entity) {
      if (empty($entity->content)) {
        $entity->content = array();
      }
      $entity->content += array(
        '#view_mode' => $view_mode,
      );
      // Initialize the field item attributes for the fields being displayed.
      // The entity can include fields that are not displayed, and the display
      // can include components that are not fields, so we want to act on the
      // intersection. However, the entity can have many more fields than are
      // displayed, so we avoid the cost of calling $entity->getProperties()
      // by iterating the intersection as follows.
      foreach ($displays[$entity->bundle()]->getComponents() as $name => $options) {
        if ($entity->hasField($name)) {
          foreach ($entity->get($name) as $item) {
            $item->_attributes = array();
          }
        }
      }
      // Group the entities by bundle.
      $entities_by_bundle[$entity->bundle()][$id] = $entity;
    }

    // Invoke hook_entity_prepare_view().
    \Drupal::moduleHandler()->invokeAll('entity_prepare_view', array($this->entityTypeId, $entities, $displays, $view_mode));

    // Let the displays build their render arrays.
    foreach ($entities_by_bundle as $bundle => $bundle_entities) {
      $build = $displays[$bundle]->buildMultiple($bundle_entities);
      foreach ($bundle_entities as $id => $entity) {
        $entity->content += array(
          '#view_mode' => $view_mode,
        ) + $build[$id];
      }
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
      '#theme' => $this->entityTypeId,
      "#{$this->entityTypeId}" => $entity,
      '#view_mode' => $view_mode,
      '#langcode' => $langcode,
      '#pre_render' => array(array($this, 'entityViewBuilderPreRender')),
    );

    // Cache the rendered output if permitted by the view mode and global entity
    // type configuration.
    if ($this->isViewModeCacheable($view_mode) && !$entity->isNew() && $entity->isDefaultRevision() && $this->entityType->isRenderCacheable()) {
      $return['#cache'] = array(
        'keys' => array('entity_view', $this->entityTypeId, $entity->id(), $view_mode),
        'granularity' => DRUPAL_CACHE_PER_ROLE,
        'bin' => $this->cacheBin,
        'tags' => array(
          $this->entityTypeId . '_view' => TRUE,
          $this->entityTypeId => array($entity->id()),
        ),
      );

      if ($entity instanceof TranslatableInterface && count($entity->getTranslationLanguages()) > 1) {
        $return['#cache']['keys'][] = $langcode;
      }
    }

    return $return;
  }

  /**
   * Performs pre-render tasks on an entity view.
   *
   * This function is assigned as a #pre_render callback in
   * \Drupal\Core\Entity\EntityViewBuilder::getBuildDefaults().
   *
   * @param array $elements
   *   A structured array containing build information and context for an
   *   entity view.
   *
   * @see drupal_render()
   */
  public function entityViewBuilderPreRender(array $elements) {
    $entity = $elements['#entity'];
    $bundle = $entity->bundle();
    $view_hook = "{$this->entityTypeId}_view";
    $view_mode = $elements['#view_mode'];
    $langcode = $elements['#langcode'];
    $context = array('langcode' => $langcode);

    // Allow modules to change the view mode.
    $this->moduleHandler->alter('entity_view_mode', $view_mode, $entity, $context);

    // Get the corresponding display settings.
    $display = EntityViewDisplay::collectRenderDisplay($entity, $view_mode);

    // Build field renderables.
    $entity->content = $elements;
    $this->buildContent(array($entity->id() => $entity), array($bundle => $display), $view_mode, $langcode);
    $view_mode = isset($entity->content['#view_mode']) ? $entity->content['#view_mode'] : $view_mode;

    $this->moduleHandler()->invokeAll($view_hook, array($entity, $display, $view_mode, $langcode));
    $this->moduleHandler()->invokeAll('entity_view', array($entity, $display, $view_mode, $langcode));

    // Do not override $build = $elements because hook_view implementations
    // are expected to add content, not alter it. For example, mymodule_view
    // should not change the #theme key.
    $build = $entity->content;
    // We don't need duplicate rendering info in $entity->content.
    unset($entity->content);

    $this->alterBuild($build, $entity, $display, $view_mode, $langcode);

    // Assign the weights configured in the display.
    // @todo: Once https://drupal.org/node/1875974 provides the missing API,
    //   only do it for 'extra fields', since other components have been taken
    //   care of in EntityViewDisplay::buildMultiple().
    foreach ($display->getComponents() as $name => $options) {
      if (isset($build[$name])) {
        $build[$name]['#weight'] = $options['weight'];
      }
    }

    // Allow modules to modify the render array.
    \Drupal::moduleHandler()->alter(array($view_hook, 'entity_view'), $build, $entity, $display);
    return $build;
  }

  /**
   * Specific per-entity building.
   *
   * @param array $build
   *   The render array that is being created.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be prepared.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display holding the display options configured for the
   *   entity components.
   * @param string $view_mode
   *   The view mode that should be used to prepare the entity.
   * @param string $langcode
   *   (optional) For which language the entity should be prepared, defaults to
   *   the current content language.
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode, $langcode = NULL) { }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $buildList = $this->viewMultiple(array($entity), $view_mode, $langcode);
    return $buildList[0];
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = array(), $view_mode = 'full', $langcode = NULL) {
    if (!isset($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage(Language::TYPE_CONTENT)->id;
    }

    // Build the view modes and display objects.
    $build = array('#sorted' => TRUE);
    $weight = 0;

    foreach ($entities as $key => $entity) {
      // Ensure that from now on we are dealing with the proper translation
      // object.
      $entity = $this->entityManager->getTranslationFromContext($entity, $langcode);
      $entities[$key] = $entity;

      //$build[$key] = $entity->content;
      $build[$key] = array(
        '#entity' => $entity
      );

      // Set defaults for #pre_render.
      $build[$key] += $this->getBuildDefaults($entity, $view_mode, $langcode);

      $build[$key]['#weight'] = $weight++;
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(array $entities = NULL) {
    if (isset($entities)) {
      $tags = array();
      foreach ($entities as $entity) {
        $id = $entity->id();
        $tags[$this->entityTypeId][$id] = $id;
        $tags[$this->entityTypeId . '_view_' . $entity->bundle()] = TRUE;
      }
      Cache::deleteTags($tags);
    }
    else {
      Cache::deleteTags(array($this->entityTypeId . '_view' => TRUE));
    }
  }

  /**
   * Returns TRUE if the view mode is cacheable.
   *
   * @param string $view_mode
   *   Name of the view mode that should be rendered.
   *
   * @return bool
   *   TRUE if the view mode can be cached, FALSE otherwise.
   */
  protected function isViewModeCacheable($view_mode) {
    if ($view_mode == 'default') {
      // The 'default' is not an actual view mode.
      return TRUE;
    }
    $view_modes_info = entity_get_view_modes($this->entityTypeId);
    return !empty($view_modes_info[$view_mode]['cache']);
  }

}
