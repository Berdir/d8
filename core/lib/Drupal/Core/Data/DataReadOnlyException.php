<?php

/**
 * @file
 * Definition of Drupal\Core\Data\DataReadOnlyException.
 */

namespace Drupal\Core\Data;

/**
 * Exception thrown when trying to write or set a ready-only property.
 */
class DataReadOnlyException extends \Exception { }
