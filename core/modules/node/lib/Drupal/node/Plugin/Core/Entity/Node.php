<?php

/**
 * @file
 * Definition of Drupal\node\Plugin\Core\Entity\Node.
 */

namespace Drupal\node\Plugin\Core\Entity;

use Drupal\Core\Entity\EntityBCDecorator;

/**
 * Extends the EntityBCDecorator for nodes.
 *
 * We extend the EntityBCDecorator only to allow BC-nodes to be passed to
 * type-hinted functions.
 */
class Node extends EntityBCDecorator {

}
