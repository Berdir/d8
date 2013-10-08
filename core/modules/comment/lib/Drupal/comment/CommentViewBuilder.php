<?php

/**
 * @file
 * Definition of Drupal\comment\CommentViewBuilder.
 */

namespace Drupal\comment;

use Drupal\Core\Entity\EntityControllerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\entity\Entity\EntityDisplay;
use Drupal\field\FieldInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Render controller for comments.
 */
class CommentViewBuilder extends EntityViewBuilder implements EntityViewBuilderInterface, EntityControllerInterface {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * The field info service.
   *
   * @var \Drupal\field\FieldInfo
   */
  protected $fieldInfo;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, $entity_type, array $entity_info) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('field.info'),
      $container->get('module_handler')
    );
  }

  /**
   * Constructs a new CommentViewBuilder.
   *
   * @param string $entity_type
   *   The entity type.
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   The entity manager service.
   * @param \Drupal\field\FieldInfo $field_info
   *   The field info service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct($entity_type, EntityManager $entity_manager, FieldInfo $field_info, ModuleHandlerInterface $module_handler) {
    parent::__construct($entity_type);
    $this->entityManager = $entity_manager;
    $this->fieldInfo = $field_info;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityViewBuilder::buildContent().
   *
   * In addition to modifying the content key on entities, this implementation
   * will also set the comment entity key which all comments carry.
   *
   * @throws \InvalidArgumentException
   *   Thrown when a comment is attached to an entity that no longer exists.
   */
  public function buildContent(array $entities, array $displays, $view_mode, $langcode = NULL) {
    $return = array();
    if (empty($entities)) {
      return $return;
    }

    // Pre-load associated users into cache to leverage multiple loading.
    $uids = array();
    foreach ($entities as $entity) {
      $uids[] = $entity->uid->target_id;
    }
    $this->entityManager->getStorageController('user')->loadMultiple(array_unique($uids));

    parent::buildContent($entities, $displays, $view_mode, $langcode);

    // Load all the entities that have comments attached.
    $commented_entity_ids = array();
    $commented_entities = array();
    foreach ($entities as $entity) {
      $commented_entity_ids[$entity->entity_type->value][] = $entity->entity_id->value;
    }
    // Load entities in bulk. This is more performant than using
    // $comment->entity_id->value as we can load them in bulk per type.
    foreach ($commented_entity_ids as $entity_type => $entity_ids) {
      $commented_entities[$entity_type] = $this->entityManager->getStorageController($entity_type)->loadMultiple($entity_ids);
    }

    foreach ($entities as $entity) {
      if (isset($commented_entities[$entity->entity_type->value][$entity->entity_id->value])) {
        $commented_entity = $commented_entities[$entity->entity_type->value][$entity->entity_id->value];
      }
      else {
        throw new \InvalidArgumentException(t('Invalid entity for comment.'));
      }
      $entity->content['#entity'] = $entity;
      $entity->content['#theme'] = 'comment__' . $entity->field_id->value . '__' . $commented_entity->bundle();
      $entity->content['links'] = array(
        '#theme' => 'links__comment',
        '#pre_render' => array('drupal_pre_render_links'),
        '#attributes' => array('class' => array('links', 'inline')),
      );
      if (empty($entity->in_preview)) {
        $entity->content['links'][$this->entityType] = array(
          '#theme' => 'links__comment__comment',
          // The "entity" property is specified to be present, so no need to
          // check.
          '#links' => comment_links($entity, $commented_entity, $entity->field_name->value),
          '#attributes' => array('class' => array('links', 'inline')),
        );
      }

      if (!isset($entity->content['#attached'])) {
        $entity->content['#attached'] = array();
      }
      $entity->content['#attached']['library'][] = array('comment', 'drupal.comment-by-viewer');
      if ($this->moduleHandler->moduleExists('history') &&  \Drupal::currentUser()->isAuthenticated()) {
        $entity->content['#attached']['library'][] = array('comment', 'drupal.comment-new-indicator');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $comment, EntityDisplay $display, $view_mode, $langcode = NULL) {
    parent::alterBuild($build, $comment, $display, $view_mode, $langcode);
    if (empty($comment->in_preview)) {
      $prefix = '';
      $commented_entity = $this->entityManager->getStorageController($comment->entity_type->value)->load($comment->entity_id->value);
      $instance = $this->fieldInfo->getInstance($commented_entity->entityType(), $commented_entity->bundle(), $comment->field_name->value);
      $is_threaded = isset($comment->divs)
        && $instance->getFieldSetting('default_mode') == COMMENT_MODE_THREADED;

      // Add indentation div or close open divs as needed.
      if ($is_threaded) {
        $build['#attached']['css'][] = drupal_get_path('module', 'comment') . '/css/comment.theme.css';
        $prefix .= $comment->divs <= 0 ? str_repeat('</div>', abs($comment->divs)) : "\n" . '<div class="indented">';
      }

      // Add anchor for each comment.
      $prefix .= "<a id=\"comment-{$comment->id()}\"></a>\n";
      $build['#prefix'] = $prefix;

      // Close all open divs.
      if ($is_threaded && !empty($comment->divs_final)) {
        $build['#suffix'] = str_repeat('</div>', $comment->divs_final);
      }
    }
  }

}
