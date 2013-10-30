<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\ContactCategory.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 6 contact category source from database.
 *
 * @PluginId("drupal6_contact_category")
 */
class ContactCategory extends SqlBase {

  /**
   * {@inheritdoc}
   */
  function query() {
    $query = $this->database
      ->select('contact', 'c')
      ->fields('c', array('cid', 'category', 'recipients', 'reply', 'weight', 'selected'));
    $query->orderBy('cid');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'cid' => t('Primary Key: Unique category ID.'),
      'category' => t('Category name.'),
      'recipients' => t('Comma-separated list of recipient e-mail addresses.'),
      'reply' => t('Text of the auto-reply message.'),
      'weight' => t("The category's weight."),
      'selected' => t('Flag to indicate whether or not category is selected by default. (1 = Yes, 0 = No)'),
    );
  }

}
