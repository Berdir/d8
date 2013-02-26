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
   * Constructs an AnnotatedClassDiscovery object.
   *
   * @param array $plugin_namespaces
   *   An array of paths keyed by it's corresponding namespaces.
   */
  function __construct($owner, $type, $root_namespaces = NULL, array $plugin_namespaces = array()) {
    $this->owner = $owner;
    $this->type = $type;
    $this->rootNamespaces = $root_namespaces;
    $this->pluginNamespaces = $plugin_namespaces;
    $annotation_namespaces = array(
      'Drupal\Component\Annotation' => DRUPAL_ROOT . '/core/lib',
      'Drupal\Core\Annotation' => DRUPAL_ROOT . '/core/lib',
    );
    parent::__construct(array(), $annotation_namespaces, 'Drupal\Core\Annotation\Plugin');
  }

  /**
   * Overrides Drupal\Component\Plugin\Discovery\AnnotatedClassDiscovery::getPluginNamespaces().
   *
   * @todo Figure out how to let this comment still be TRUE.
   * This is overridden rather than set in the constructor, because Drupal
   * modules can be enabled (and therefore, namespaces registered) during the
   * lifetime of a plugin manager.
   */
  protected function getPluginNamespaces() {
    if (!empty($this->plugin_namespaces)) {
      return parent::getPluginNamespaces();
    }
    $plugin_namespaces = array();
    foreach ($this->rootNamespaces as $namespace => $dir) {
      $plugin_namespaces["$namespace\\Plugin\\{$this->owner}\\{$this->type}"] = array($dir);
    }
    return $plugin_namespaces;
  }

}
