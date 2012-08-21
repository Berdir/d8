<?php

/**
 * @file
 * Definition of Drupal\Core\Data\DataListInterface.
 */

namespace Drupal\Core\Data;
use IteratorAggregate;
use ArrayAccess;
use Countable;

/**
 * Interface for a list of properties.
 */
interface DataListInterface extends DataItemInterface, ArrayAccess, IteratorAggregate, Countable { }