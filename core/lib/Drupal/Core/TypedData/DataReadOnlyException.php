<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\DataReadOnlyException.
 */

namespace Drupal\Core\TypedData;

/**
 * Exception thrown when trying to write or set a ready-only property.
 */
class DataReadOnlyException extends \Exception { }
