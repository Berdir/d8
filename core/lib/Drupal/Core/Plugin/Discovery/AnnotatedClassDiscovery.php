<?php

/**
 * @file
 * Definition of Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery.
 */

namespace Drupal\Core\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery as ComponentAnnotatedClassDiscovery;

/**
 * Defines a discovery mechanism to find annotated plugins in PSR-0 namespaces.
 */
class AnnotatedClassDiscovery extends ComponentAnnotatedClassDiscovery {

  /**
   * The module name that defines the plugin type.
   *
   * @var string
   */
  protected $owner;

  /**
   * The plugin type, for example filter.
   *
   * @var string
   */
  protected $type;

  /**
   * An object containing the namespaces to look for plugin implementations.
   *
   * @var \Traversable
   */
  protected $rootNamespacesIterator;

  /**
   * Constructs an AnnotatedClassDiscovery object.
   *
   * @param string $owner
   *   The module name that defines the plugin type.
   * @param string $type
   *   The plugin type, for example filter.
   * @param \Traversable $root_namespaces
   *   Array of root paths keyed by the corresponding namespace to look for
   *   plugin implementations, \Plugin\$owner\$type will be appended to each
   *   namespace. Defaults to array().
   */
  public function __construct($owner, $type, \Traversable $root_namespaces) {
    $this->owner = $owner;
    $this->type = $type;
    $this->rootNamespacesIterator = $root_namespaces;

    $annotation_namespaces = array(
      'Drupal\Component\Annotation' => DRUPAL_ROOT . '/core/lib',
      'Drupal\Core\Annotation' => DRUPAL_ROOT . '/core/lib',
    );

    $plugin_namespaces = array();
    parent::__construct($plugin_namespaces, $annotation_namespaces);
  }

  /**
   * Overrides \Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery::getPluginNamespaces().
   */
  protected function getPluginNamespaces() {
    $plugin_namespaces = array();
    foreach ($this->rootNamespacesIterator as $namespace => &$dir) {
      $plugin_namespaces["$namespace\\Plugin\\{$this->owner}\\{$this->type}"] = array($dir);
    }

    return $plugin_namespaces;
  }

}
