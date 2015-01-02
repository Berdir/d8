<?php

/**
 * @file
 * Contains Drupal\search\Form\SearchPageDeleteForm.
 */

namespace Drupal\search\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Url;

/**
 * Provides a deletion confirm form for search.
 */
class SearchPageDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('search.settings');
  }

}
