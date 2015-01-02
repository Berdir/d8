<?php
/**
 * @file
 * Contains \Drupal\config_test\Form\ConfigTestDeleteForm.
 */

namespace Drupal\config_test\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Url;

/**
 * Delete confirmation form for config_test entities.
 */
class ConfigTestDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('config_test.list_page');
  }

}
