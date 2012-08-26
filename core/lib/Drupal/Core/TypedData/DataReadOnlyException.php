<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\DataReadOnlyException.
 */

namespace Drupal\Core\TypedData;
use Exception;

/**
 * Exception thrown when trying to write or set ready-only data.
 */
class DataReadOnlyException extends Exception { }
