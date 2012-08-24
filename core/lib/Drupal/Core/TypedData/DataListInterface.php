<?php

/**
 * @file
 * Definition of Drupal\Core\TypedData\DataListInterface.
 */

namespace Drupal\Core\TypedData;
use IteratorAggregate;
use ArrayAccess;
use Countable;

/**
 * Interface for a list of typed data.
 */
interface DataListInterface extends DataWrapperInterface, ArrayAccess, IteratorAggregate, Countable { }