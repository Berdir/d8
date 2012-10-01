<?php

/**
 * @file
 * Definition of Drupal\comment\CommentRenderController.
 */

namespace Drupal\comment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRenderController;

/**
 * Render controller for comments.
 */
class CommentRenderController extends EntityRenderController {

  /**
   * Overrides Drupal\Core\Entity\EntityRenderController::buildContent().
   *
   * In addition to modifying the content key on entities, this implementation
   * will also set the node key which all comments carry.
   */
  public function buildContent(array $entities = array(), $view_mode = 'full', $langcode = NULL) {
    $return = array();
    if (empty($entities)) {
      return $return;
    }

    // Array is known not be empty, and all comments apply to the same node,
    // so we can just fetch the node from the first comment.
    $entity = reset($entities);
    if (isset($entity->node)) {
      $node = $entity->node;
    }
    else {
      $node = node_load($entity->nid);
      if (empty($node)) {
        throw new \InvalidArgumentException(t('Invalid node for comment.'));
      }
    }

    foreach ($entities as $entity) {
      if (!isset($entity->node)) {
        $entity->node = $node;
      }
    }

    parent::buildContent($entities, $view_mode, $langcode);

    foreach ($entities as $entity) {
      $entity->content['links'] = array(
        '#theme' => 'links__comment',
        '#pre_render' => array('drupal_pre_render_links'),
        '#attributes' => array('class' => array('links', 'inline')),
      );
      if (empty($entity->in_preview)) {
        $entity->content['links'][$this->entityType] = array(
          '#theme' => 'links__comment__comment',
          // The "node" property is specified to be present, so no need to check.
          '#links' => comment_links($entity, $entity->node),
          '#attributes' => array('class' => array('links', 'inline')),
        );
      }
    }
  }

  /**
   * Overrides Drupal\Core\Entity\EntityRenderController::getBuildDefaults().
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode, $langcode) {
    $return = parent::getBuildDefaults($entity, $view_mode, $langcode);
    // @todo Accessing $node on an EntityInterface is not clean. Maybe we want
    // to define some extended interface exposing node.
    $node = $entity->node;
    $return['#theme'] = 'comment__node_' . $node->bundle();
    $return['#node'] = $node;
    return $return;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityRenderController::prepareBuild().
   */
  protected function prepareBuild(array $build, EntityInterface $comment, $view_mode, $langcode = NULL) {
    $build = parent::prepareBuild($build, $comment, $view_mode, $langcode);
    if (empty($comment->in_preview)) {
      $prefix = '';
      $is_threaded = isset($comment->divs)
        && variable_get('comment_default_mode_' . $comment->bundle(), COMMENT_MODE_THREADED) == COMMENT_MODE_THREADED;

      // Add 'new' anchor if needed.
      if (!empty($comment->first_new)) {
        $prefix .= "<a id=\"new\"></a>\n";
      }

      // Add indentation div or close open divs as needed.
      if ($is_threaded) {
        $prefix .= $comment->divs <= 0 ? str_repeat('</div>', abs($comment->divs)) : "\n" . '<div class="indented">';
      }

      // Add anchor for each comment.
      $prefix .= "<a id=\"comment-$comment->cid\"></a>\n";
      $build['#prefix'] = $prefix;

      // Close all open divs.
      if ($is_threaded && !empty($comment->divs_final)) {
        $build['#suffix'] = str_repeat('</div>', $comment->divs_final);
      }
    }

    return $build;
  }
}
