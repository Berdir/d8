<?php

/**
 * @file
 * Contains \Drupal\node\Form\NodeAddForm.
 */

namespace Drupal\node\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for adding nodes.
 */
class NodeAddForm extends NodeFormBase {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entity;
    $node_type = $this->entityManager->getStorage('node_type')->load($node->getType());
    $t_args = array('@type' => $node_type->label(), '%title' => $node->label());
    $log_context = array(
      '@type' => $node->getType(),
      '%title' => $node->label(),
      'link' => $this->getLinkGenerator()
        ->generateFromUrl($this->t('View'), $node->urlInfo()),
    );

    drupal_set_message(t('@type %title has been created.', $t_args));
    $this->logger('content')->notice('@type: added %title.', $log_context);
  }

}
