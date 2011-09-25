<?php

namespace Drupal\Context;

/**
 * Interface for context value objects.
 *
 * ValueInterface includes a method - contextKey() - that will return a
 * load key for that object. It is up to the object to return something
 * meaningful. The load key is the value by which we can load that object later,
 * such as nid, view machine name, etc.
 */
interface ValueInterface {

  /**
   * Retrieves the key for the object to be loaded
   *
   * @return mixed
   *   A key for the object to be returned by the appropriate handler
   */
  public function contextKey();
}
