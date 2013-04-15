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
   * @param string $owner
   *   The module name that defines the plugin type.
   * @param string $type
   *   The plugin type, for example filter.
   * @param array $root_namespaces
   *   (optional) An array of root paths keyed by the corresponding namespace to
   *   look for plugin implementations. '\Plugin\$owner\$type' will be appended
   *   to each namespace. Defaults to an empty array.
   * @param array $annotation_namespaces
   *   (optional) The namespaces of classes that can be used as annotations.
   *   Defaults to an empty array.
   * @param string $plugin_definition_annotation_name
   *   (optional) The name of the annotation that contains the plugin definition.
   *   Defaults to 'Drupal\Component\Annotation\Plugin'.
   *
   * @todo Figure out how to make the following comment FALSE.
   *   Drupal modules can be enabled (and therefore, namespaces registered)
   *   during the lifetime of a plugin manager. Passing $root_namespaces into
   *   the constructor means plugins in the new namespaces will not be available
   *   until the next request. Additionally when a namespace is unregistered,
   *   plugins will not be removed until the next request.
   */
  function __construct($owner, $type, array $root_namespaces = array(), $annotation_namespaces = array(), $plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin') {
    $annotation_namespaces += array(
      'Drupal\Component\Annotation' => DRUPAL_ROOT . '/core/lib',
      'Drupal\Core\Annotation' => DRUPAL_ROOT . '/core/lib',
    );
    $plugin_namespaces = array();
    foreach ($root_namespaces as $namespace => $dir) {
      $plugin_namespaces["$namespace\\Plugin\\{$owner}\\{$type}"] = array($dir);
    }
    parent::__construct($plugin_namespaces, $annotation_namespaces, $plugin_definition_annotation_name);
  }

}
