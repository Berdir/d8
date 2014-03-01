<?php

/**
 * Contains \Drupal\link\LinkItemInterface.
 */

namespace Drupal\link;

use Drupal\Core\Field\ConfigFieldItemInterface;

/**
 * Interface for the link field item.
 */
interface LinkItemInterface extends ConfigFieldItemInterface {

  /**
   * Determines if a link is external.
   *
   * @return bool
   *   TRUE if the link is external, FALSE otherwise.
   */
  public function isExternal();

}
