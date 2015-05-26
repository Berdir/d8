<?php

/**
 * @file
 * Contains \Drupal\system\Entity\Menu.
 */

namespace Drupal\system\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\system\MenuInterface;

/**
 * Defines the Menu configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "menu",
 *   label = @Translation("Menu"),
 *   handlers = {
 *     "storage" = "Drupal\system\MenuStorage",
 *     "access" = "Drupal\system\MenuAccessControlHandler"
 *   },
 *   admin_permission = "administer menu",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "locked",
 *   }
 * )
 */
class Menu extends ConfigEntityBase implements MenuInterface {

  /**
   * The menu machine name.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the menu entity.
   *
   * @var string
   */
  protected $label;

  /**
   * The menu description.
   *
   * @var string
   */
  protected $description;

  /**
   * The locked status of this menu.
   *
   * @var bool
   */
  protected $locked = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    return (bool) $this->locked;
  }

  /**
   * {@inheritdoc}
   *
   * Override the default cache tags to use the cache tags that are not coupled
   * to Menu config entities (i.e. this class), to not force other parts of the
   * menu system to couple themselves to Menu config entities.
   */
  public function getCacheTags() {
    return ['menu:' . $this->id()];
  }

}
