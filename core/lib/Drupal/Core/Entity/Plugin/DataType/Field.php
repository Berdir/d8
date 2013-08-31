<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\DataType\Field.
 */

namespace Drupal\Core\Entity\Plugin\DataType;

use Drupal\Core\TypedData\Annotation\DataType;
use Drupal\Core\Annotation\Translation;

/**
 * Defines a data type plugin for entity fields, i.e. the list of field items.
 *
 * Note that the class only register the plugin, and is actually never used.
 * @todo: Move the implementation to this place also.
 *
 * @DataType(
 *   id = "entity_field",
 *   label = @Translation("Entity field"),
 *   class = "\Drupal\Core\Entity\Field\Field"
 * )
 */
class Field extends \Drupal\Core\Entity\Field\Field {

}
