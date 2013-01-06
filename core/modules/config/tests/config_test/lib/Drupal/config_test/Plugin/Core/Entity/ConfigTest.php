<?php

/**
 * @file
 * Definition of Drupal\config_test\Plugin\Core\Entity\ConfigTest.
 */

namespace Drupal\config_test\Plugin\Core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines the ConfigTest configuration entity.
 *
 * @Plugin(
 *   id = "config_test",
 *   label = @Translation("Test configuration"),
 *   module = "config_test",
 *   controller_class = "Drupal\config_test\ConfigTestStorageController",
 *   list_controller_class = "Drupal\Core\Config\Entity\ConfigEntityListController",
 *   form_controller_class = {
 *     "default" = "Drupal\config_test\ConfigTestFormController"
 *   },
 *   uri_callback = "config_test_uri",
 *   config_prefix = "config_test.dynamic",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class ConfigTest extends ConfigEntityBase {

  /**
   * The machine name for the configuration entity.
   *
   * @var string
   */
  public $id;

  /**
   * The UUID for the configuration entity.
   *
   * @var string
   */
  public $uuid;

  /**
   * The human-readable name of the configuration entity.
   *
   * @var string
   */
  public $label;

  /**
   * The image style to use.
   *
   * @var string
   */
  public $style;

}
