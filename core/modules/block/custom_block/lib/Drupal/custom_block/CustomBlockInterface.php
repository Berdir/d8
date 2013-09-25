<?php

/**
 * @file
 * Contains \Drupal\custom_block\Entity\CustomBlockInterface.
 */

namespace Drupal\custom_block;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a custom block entity.
 */
interface CustomBlockInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * The block ID.
   */
  public function id();

  /**
   * The revision ID.
   */
  public function getRevisionId();

  /**
   * The block UUID.
   */
  public function uuid();

  /**
   * The custom block type (bundle).
   */
  public function bundle();

  /**
   * The block description.
   */
  public function getInfo();

  /**
   * The block revision log message.
   */
  public function getLog();

  /**
   * Sets the theme value.
   *
   * When creating a new custom block from the block library, the user is
   * redirected to the configure form for that block in the given theme. The
   * theme is stored against the block when the custom block add form is shown.
   *
   * @param string $theme
   *   The theme name.
   */
  public function setTheme($theme);

  /**
   * Gets the theme value.
   *
   * When creating a new custom block from the block library, the user is
   * redirected to the configure form for that block in the given theme. The
   * theme is stored against the block when the custom block add form is shown.
   *
   * @return string
   *   The theme name.
   */
  public function getTheme();

  /**
   * Gets the configured instances of this custom block.
   *
   * @return array
   *   Array of Drupal\block\Core\Plugin\Entity\Block entities.
   */
  public function getInstances();

}
