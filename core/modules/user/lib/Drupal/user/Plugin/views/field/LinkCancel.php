<?php

/**
 * @file
 * Definition of Drupal\user\Plugin\views\field\LinkCancel.
 */

namespace Drupal\user\Plugin\views\field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Annotation\Plugin;

/**
 * Field handler to present a link to user cancel.
 *
 * @ingroup views_field_handlers
 *
 * @Plugin(
 *   id = "user_link_cancel",
 *   module = "user"
 * )
 */
class LinkCancel extends Link {

  /**
   * Overrides \Drupal\user\Plugin\views\field\Link::render_link().
   */
  public function render_link(EntityInterface $entity, \stdClass $values) {
    if ($entity && $entity->access('delete')) {
      $this->options['alter']['make_link'] = TRUE;

      $text = !empty($this->options['text']) ? $this->options['text'] : t('cancel');

      $uri = $entity->uri();
      $this->options['alter']['path'] = $uri['path'] . '/cancel';
      $this->options['alter']['query'] = drupal_get_destination();

      return $text;
    }
  }

}
