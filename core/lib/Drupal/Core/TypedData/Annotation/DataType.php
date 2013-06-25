<?php

/**
 * @file
 * Contains \Drupal\Core\TypedData\Annotation\DataType.
 */

namespace Drupal\Core\TypedData\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a data type annotation object.
 *
 * @Annotation
 */
class DataType extends Plugin {

  /**
   * The name of the module providing the type.
   *
   * @var string
   */
  public $module;

  /**
   * The name of the data type class.
   *
   * This is not provided manually, it will be added by the discovery mechanism.
   *
   * @var string
   */
  public $class;

}
