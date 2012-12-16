<?php

/**
 * @file
 * Definition of Drupal\Core\Annotation\Plugin.
 */

namespace Drupal\Core\Annotation;

use Drupal\Component\Annotation\Plugin as ComponentPlugin;

/**
 * Defines a Plugin annotation object.
 *
 * @Annotation
 *
 * @todo Remove after globally replacing all plugin classes to "use" the
 *   Component one instead of this one: http://drupal.org/node/1849752.
 */
class Plugin extends ComponentPlugin {
}
