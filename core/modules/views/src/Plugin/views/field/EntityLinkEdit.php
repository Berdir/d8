<?php

/**
 * @file
 * Contains \Drupal\views\Plugin\views\field\EntityLinkEdit.
 */

namespace Drupal\views\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Field handler to present a link to edit an entity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_link_edit")
 */
class EntityLinkEdit extends EntityLink {

  /**
   * Prepares the link to edit an entity.
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
    if ($entity && $entity->access('update')) {
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['path'] = $entity->getSystemPath('edit-form');
      $this->options['alter']['query'] = drupal_get_destination();

      $this->addLangcode($values);

      $text = !empty($this->options['text']) ? $this->options['text'] : $this->t('edit');
      return $text;
    }
  }

}
