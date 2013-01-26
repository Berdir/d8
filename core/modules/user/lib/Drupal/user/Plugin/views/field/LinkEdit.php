<?php

/**
 * @file
 * Definition of Drupal\user\Plugin\views\field\LinkEdit.
 */

namespace Drupal\user\Plugin\views\field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Annotation\Plugin;

/**
 * Field handler to present a link to user edit.
 *
 * @ingroup views_field_handlers
 *
 * @Plugin(
 *   id = "user_link_edit",
 *   module = "user"
 * )
 */
class LinkEdit extends Link {

  /**
   * Overrides \Drupal\user\Plugin\views\field\Link::render_link().
   */
  public function render_link(EntityInterface $entity, \stdClass $values) {
    if ($entity && $entity->access('edit')) {
      $this->options['alter']['make_link'] = TRUE;

      $text = !empty($this->options['text']) ? $this->options['text'] : t('edit');

      $uri = $entity->uri();
      $this->options['alter']['path'] = $uri['path'] . '/edit';
      $this->options['alter']['query'] = drupal_get_destination();

      return $text;
    }
  }

}
