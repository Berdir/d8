<?php

/**
 * @file
 * Contains \Drupal\filter\Type\FilterFormat.
 */

namespace Drupal\filter\Type;

use Drupal\Core\TypedData\Type\String;
use Drupal\Core\TypedData\AllowedValuesInterface;

/**
 * The filter format data type.
 */
class FilterFormat extends String implements AllowedValuesInterface {

  /**
   * {@inheritdoc}
   */
  public function getAvailableValues($account = NULL) {
    return array_keys($this->getAvailableOptions());
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableOptions($account = NULL) {
    $values = array();
    foreach (filter_formats() as $format) {
      $values[$format->id()] = $format->label();
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getValues($account = NULL) {
    return array_keys($this->getOptions($account));
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions($user = NULL) {
    $user = empty($user) ? $GLOBALS['user'] : $user;
    $values = array();
    // @todo: Avoid calling functions but move to injected dependencies.
    foreach (filter_formats($user) as $format) {
      $values[$format->id()] = $format->label();
    }
    return $values;
  }
}
