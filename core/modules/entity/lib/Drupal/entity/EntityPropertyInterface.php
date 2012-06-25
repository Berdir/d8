<?php

/**
 * @file
 * Definition of Drupal\entity\EntityPropertyInterface.
 */

namespace Drupal\entity;
use Drupal\Core\Property\PropertyListInterface;

/**
 * Interface for entity properties, being lists of property items implementing delegation for working with the first item.
 *
 * Contained items must implement the EntityPropertyItemInterface.
 * This interface is required for every property of an entity.
 *
 * Methods of the EntityPropertyItemInterface have to be delegated to the first
 * contained EntityPropertyItem, in particular that are get() and set() as well
 * as their magic equivalences.
 *
 * @todo: This makes validate() only validate the first item what is confusing.
 * Maybe we skip the EntityPropertyItemInterface here and manually add only
 * get() and set() instead? That would probably make it clearer what goes where.
 */
interface EntityPropertyInterface extends PropertyListInterface, EntityPropertyItemInterface { }
