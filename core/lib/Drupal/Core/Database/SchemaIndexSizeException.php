<?php

/**
 * @file
 * Contains Drupal\Core\Database\SchemaIndexSizeException.
 */

namespace Drupal\Core\Database;

/**
 * Exception thrown if a schema contains an index that is too long.
 *
 * @see Drupal\Core\Database\Schema::MAX_INDEX_LENGTH
 */
class SchemaIndexSizeException extends SchemaException implements DatabaseException { }
