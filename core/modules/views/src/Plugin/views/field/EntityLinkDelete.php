<?php

/**
 * @file
 * Contains \Drupal\views\Plugin\views\field\EntityLinkDelete.
 */

namespace Drupal\views\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Field handler to present a link to delete an entity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_link_delete")
 */
class EntityLinkDelete extends EntityLink {

  /**
   * Prepares the link to delete an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The node entity this field belongs to.
   * @param \Drupal\views\ResultRow $values
   *   The values retrieved from the view's result set.
   *
   * @return string
   *   Returns a string for the link text.
   */
  protected function renderLink($entity, ResultRow $values) {
    // Ensure user has access to delete this node.
    if ($entity && $entity->access('delete')) {
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['path'] = $entity->getSystemPath('delete-form');
      $this->options['alter']['query'] = drupal_get_destination();

      $this->addLangcode($values);

      $text = !empty($this->options['text']) ? $this->options['text'] : $this->t('delete');
      return $text;
    }
  }

}
