<?php

/**
 * @file
 * Contains \Drupal\comment\Plugin\views\field\LinkDelete.
 */

namespace Drupal\comment\Plugin\views\field;

use Drupal\Component\Annotation\PluginID;

/**
 * Field handler to present a link to delete a comment.
 *
 * @ingroup views_field_handlers
 *
 * @PluginID("comment_link_delete")
 */
class LinkDelete extends Link {

  public function access() {
    //needs permission to administer comments in general
    return user_access('administer comments');
  }

  function render_link($data, $values) {
    $text = !empty($this->options['text']) ? $this->options['text'] : t('delete');
    $comment = $this->get_entity($values);

    $this->options['alter']['make_link'] = TRUE;
    $this->options['alter']['path'] = "comment/" . $comment->id(). "/delete";
    $this->options['alter']['query'] = drupal_get_destination();

    return $text;
  }

}
