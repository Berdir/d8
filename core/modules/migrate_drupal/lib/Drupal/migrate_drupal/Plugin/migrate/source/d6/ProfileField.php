<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\ProfileField.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\RequirementsInterface;


/**
 * Drupal 6 profile fields source from database.
 *
 * @PluginId("drupal6_profile_field")
 */
class ProfileField extends Drupal6SqlBase implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('profile_fields', 'pf')
      ->fields('pf', array(
        'fid',
        'title',
        'name',
        'explanation',
        'category',
        'page',
        'type',
        'weight',
        'required',
        'register',
        'visibility',
        'autocomplete',
        'options',
      ));

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'fid' => t('Primary Key: Unique profile field ID.'),
      'title' => t('Title of the field shown to the end user.'),
      'name' => t('Internal name of the field used in the form HTML and URLs.'),
      'explanation' => t('Explanation of the field to end users.'),
      'category' => t('Profile category that the field will be grouped under.'),
      'page' => t("Title of page used for browsing by the field's value"),
      'type' => t('Type of form field.'),
      'weight' => t('Weight of field in relation to other profile fields.'),
      'required' => t('Whether the user is required to enter a value. (0 = no, 1 = yes)'),
      'register' => t('Whether the field is visible in the user registration form. (1 = yes, 0 = no)'),
      'visibility' => t('The level of visibility for the field. (0 = hidden, 1 = private, 2 = public on profile but not member list pages, 3 = public on profile and list pages)'),
      'autocomplete' => t('Whether form auto-completion is enabled. (0 = disabled, 1 = enabled)'),
      'options' => t('List of options to be used in a list selection field.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    return $this->moduleExists('profile');
  }

}
