<?php

/**
 * @file
 * Contains \Drupal\views_ui\ViewDeleteForm.
 */

namespace Drupal\views_ui;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Url;

/**
 * Provides a delete form for a view.
 */
class ViewDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('views_ui.list');
  }

}
