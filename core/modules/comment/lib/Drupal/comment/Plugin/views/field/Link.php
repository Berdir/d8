<?php

/**
 * @file
 * Definition of Drupal\comment\Plugin\views\field\Link.
 */

namespace Drupal\comment\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Component\Annotation\Plugin;

/**
 * Base field handler to present a link.
 *
 * @ingroup views_field_handlers
 *
 * @Plugin(
 *   id = "comment_link",
 *   module = "comment"
 * )
 */
class Link extends FieldPluginBase {

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['text'] = array('default' => '', 'translatable' => TRUE);
    $options['link_to_entity'] = array('default' => FALSE, 'bool' => TRUE);
    return $options;
  }

  public function buildOptionsForm(&$form, &$form_state) {
    $form['text'] = array(
      '#type' => 'textfield',
      '#title' => t('Text to display'),
      '#default_value' => $this->options['text'],
    );
    $form['link_to_entity'] = array(
      '#title' => t('Link field to the entity if there is no comment.'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['link_to_entity'],
    );
    parent::buildOptionsForm($form, $form_state);
  }

  public function query() {}

  function render($values) {
    $comment = $this->get_entity($values);
    return $this->render_link($comment, $values);
  }

  function render_link($data, $values) {
    $text = !empty($this->options['text']) ? $this->options['text'] : t('view');
    $comment = $data;
    $cid = $comment->id();

    $this->options['alter']['make_link'] = TRUE;
    $this->options['alter']['html'] = TRUE;

    if (!empty($cid)) {
      $this->options['alter']['path'] = "comment/" . $cid;
      $this->options['alter']['fragment'] = "comment-" . $cid;
    }
    // If there is no comment link to the node.
    elseif ($this->options['link_to_node']) {
      $entity_id = $comment->entity_id;
      $entity_type = $comment->entity_type;
      $entity = entity_load($entity_type, $entity_id);
      $uri = $entity->uri();
      $this->options['alter']['path'] = $uri['path'];
    }

    return $text;
  }

}
