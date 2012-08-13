<?php

/**
 * @file
 * Definition of Drupal\Core\Property\PropertyReadOnlyException.
 */

namespace Drupal\Core\Property;

/**
 * Exception thrown when trying to write or set a ready-only property.
 */
class PropertyReadOnlyException extends \Exception { }
