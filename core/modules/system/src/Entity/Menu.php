<?php

/**
 * @file
 * Contains \Drupal\system\Entity\Menu.
 */

namespace Drupal\system\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\system\MenuInterface;

/**
 * Defines the Menu configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "menu",
 *   label = @Translation("Menu"),
 *   handlers = {
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
   */
  public function getCacheTags() {
    // Override the default cache tags to use the cache tags that are not
    // coupled to Menu config entities (i.e. this class), to not force other
    // parts of the menu system to couple themselves to Menu config entities.
    return ['menu:' . $this->id()];
  }

  /**
   * {@inheritdoc}
   */
  protected function invalidateTagsOnSave($update) {
    parent::invalidateTagsOnSave($update);
    // The menu API doesn't require one to use Menu config entities. Hence the
    // Menu config entity should not use config-specific cache tags, but generic
    // ones instead. Drupal\Core\Config\Entity\ConfigEntityBase explicitly
    // overrides the default implementation and does not invalidate the specific
    // cache tag, this adds that again.
    if ($update) {
      Cache::invalidateTags($this->getCacheTags());
    }
  }

}
