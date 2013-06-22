<?php
/**
 * @file
 * Contains \Drupal\Core\TypedData\AllowedValuesInterface.
 */

namespace Drupal\Core\TypedData;

/**
 * Interface for retrieving allowed values.
 *
 * While allowed values define the values that are allowed to be set by a user,
 * the available values may be used to get the list of possible values that may
 * be already set on an object.
 * For example, in an workflow scenario the allowed options for a state field
 * depend on the currently set state, while available options are all states.
 * Thus allowed values would be used during any editing context, while available
 * values would be used when e.g. filtering for existing values.
 */
interface AllowedValuesInterface {

  /**
   * Returns an array of allowed values.
   *
   * @param object $account
   *   (optional) The user account for which to generate the allowed values.
   *
   * @return array
   *   An array allowed values.
   */
  public function getValues($account = NULL);

  /**
   * Returns an array of allowed options.
   *
   * @param object $account
   *   (optional) The user account for which to generate the allowed options.
   *
   * @return array
   *   The array of allowed options for the object. Array keys are the values as
   *   expected by the object. Array values are the labels to display; e.g.,
   *   within a widget. The labels should NOT be sanitized.
   *
   * @see Drupal\Core\TypedData\AllowedValuesInterface::getValues()
   */
  public function getOptions($account = NULL);

  /**
   * Returns an array of available values.
   *
   * @param object $account
   *   (optional) The user account for which to generate the available values.
   *
   * @return array
   *   An array of available values.
   */
  public function getAvailableValues($account = NULL);

  /**
   * Returns an array of available options.
   *
   * @param object $account
   *   (optional) The user account for which to generate the available options.
   *
   * @return array
   *   The array of available options for the object. Array keys are the values
   *   as expected by the object. Array values are the labels to display; e.g.,
   *   within a widget. The labels should NOT be sanitized.
   *
   * @see Drupal\Core\TypedData\AllowedValuesInterface::getOptions()
   */
  public function getAvailableOptions($account = NULL);
}
