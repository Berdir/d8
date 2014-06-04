<?php

/**
 * @file
 * Contains \Drupal\comment\Entity\CommentTypeInterface.
 */

namespace Drupal\comment;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a comment type entity.
 */
interface CommentTypeInterface extends ConfigEntityInterface {

  /**
   * Returns the comment type description.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Sets the description of the comment type.
   *
   * @param string $description
   *   The new description.
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Gets the target entity type id for this comment type.
   *
   * @return string
   *   The target entity type id.
   */
  public function getTargetEntityTypeId();

}
