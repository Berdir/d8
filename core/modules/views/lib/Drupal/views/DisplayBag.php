<?php

/**
 * @file
 * Contains \Drupal\views\DisplayBag.
 */

namespace Drupal\views;

use Drupal\Component\Plugin\PluginBag;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * A class which wraps the displays of a view so you can lazy-initialize them.
 */
class DisplayBag extends PluginBag {

  /**
   * Stores a reference to the view which has this displays attached.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected $view;

  /**
   * The manager used to instantiate the plugins.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * Constructs a DisplayBag object.
   *
   * @param \Drupal\views\ViewExecutable
   *   The view which has this displays attached.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The manager to be used for instantiating plugins.
   */
  public function __construct(ViewExecutable $view, PluginManagerInterface $manager) {
    $this->view = $view;
    $this->manager = $manager;

    $this->initializePlugin('default');

    // Store all display IDs to access them easy and fast.
    $display = $this->view->storage->get('display');
    $this->instanceIDs = drupal_map_assoc(array_keys($display));
  }

  /**
   * Destructs a DisplayBag object.
   */
  public function __destruct() {
    $this->clear();
  }

  /**
   * Overrides \Drupal\Component\Plugin\PluginBag::clear().
   */
  public function clear() {
    foreach ($this->pluginInstances as $display_id => $display) {
      $display->destroy();
    }

    parent::clear();
  }

  /**
   * Overrides \Drupal\Component\Plugin\PluginBag::initializePlugin().
   */
  protected function initializePlugin($display_id) {
    // If the display was initialized before, just return.
    if (isset($this->pluginInstances[$display_id])) {
      return;
    }

    // Retrieve and initialize the new display handler with data.
    $display = &$this->view->storage->getDisplay($display_id);
    $this->pluginInstances[$display_id] = $this->manager->createInstance($display['display_plugin']);
    if (empty($this->pluginInstances[$display_id])) {
      // Provide a 'default' handler as an emergency. This won't work well but
      // it will keep things from crashing.
      $this->pluginInstances[$display_id] = $this->manager->createInstance('default');
    }

    $this->pluginInstances[$display_id]->initDisplay($this->view, $display);
    // If this is not the default display handler, let it know which is since
    // it may well utilize some data from the default.
    if ($display_id != 'default') {
      $this->pluginInstances[$display_id]->default_display = $this->pluginInstances['default'];
    }
  }

}
