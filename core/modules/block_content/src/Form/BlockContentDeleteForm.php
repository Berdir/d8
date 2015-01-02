<?php

/**
 * @file
 * Contains \Drupal\block\Form\BlockContentDeleteForm.
 */

namespace Drupal\block_content\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a confirmation form for deleting a custom block entity.
 */
class BlockContentDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('block.admin_display');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $instances = $this->entity->getInstances();

    $form['message'] = array(
      '#markup' => format_plural(count($instances), 'This will also remove 1 placed block instance.', 'This will also remove @count placed block instances.'),
      '#access' => !empty($instances),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->logger('block_content')->notice('Custom block %label has been deleted.', array('%label' => $this->entity->label()));
  }

}
