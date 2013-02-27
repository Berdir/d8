<?php

/**
 * @file
 * Definition of Drupal\layout\Plugin\Type\LayoutManager.
 */

namespace Drupal\layout\Plugin\Type;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\Discovery\ProcessDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Component\Plugin\Factory\ReflectionFactory;

/**
 * Layout plugin manager.
 */
class LayoutManager extends PluginManagerBase {

  protected $defaults = array(
    'class' => 'Drupal\layout\Plugin\layout\layout\StaticLayout',
  );

  /**
   * Overrides Drupal\Component\Plugin\PluginManagerBase::__construct().
   *
   * @param array $namespaces
   *   An array of paths keyed by it's corresponding namespaces.
   */
  public function __construct($namespaces) {
    // Allow themes to provide plugins.
    $namespaces += $this->getThemeNamespaces();

    // Create layout plugin derivatives from declaratively defined layouts.
    $this->discovery = new AnnotatedClassDiscovery('layout', 'layout', $namespaces);
    $this->discovery = new DerivativeDiscoveryDecorator($this->discovery);
    $this->discovery = new ProcessDecorator($this->discovery, array($this, 'processDefinition'));

    $this->factory = new ReflectionFactory($this->discovery);
  }

  /**
   * Returns namespaces for themes to be used for the annotated class discovery.
   *
   * @todo: Move into the base class?
   *
   * @return array
   *   Array of namespaces keyed by the directory.
   */
  protected function getThemeNamespaces() {
    global $theme, $base_theme_info;
    $namespaces = array();
    if (isset($theme)) {
      $theme_keys = array();
      foreach ($base_theme_info as $base) {
        $theme_keys[] = $base->name;
      }
      $theme_keys[] = $theme;
      foreach ($theme_keys as $theme_key) {
        $namespaces[drupal_get_path('theme', $theme_key) . '/lib'] = 'Drupal\\' . $theme_key;
      }
    }
    return $namespaces;
  }
}
