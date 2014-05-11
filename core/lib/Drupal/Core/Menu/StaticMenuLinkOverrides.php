<?php

/**
 * @file
 * Contains \Drupal\Core\Menu\StaticMenuLinkOverrides.
 */

namespace Drupal\Core\Menu;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Implementation of the menu link override using a config file.
 */
class StaticMenuLinkOverrides implements StaticMenuLinkOverridesInterface {

  /**
   * The config name used to store the overrides.
   *
   * @var string
   */
  protected $configName = 'menu_link.static.overrides';

  /**
   * The menu link overrides config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Helper function to get the config object.
   *
   * Since this service is injected into all static menu link objects, but
   * only used when updating one, avoid actually loading the config when it's
   * not needed.
   */
  protected function getConfig() {
    if (empty($this->config)) {
      $this->config = $this->configFactory->get($this->configName);
    }
    return $this->config;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverride($id) {
    $all_overrides = $this->getConfig()->get('definitions');
    return $id && isset($all_overrides[$id]) ? $all_overrides[$id] : array();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultipleOverrides(array $ids) {
    $all_overrides = $this->getConfig()->get('definitions');
    $save = FALSE;
    foreach ($ids as $id) {
      if (isset($all_overrides[$id])) {
        unset($all_overrides[$id]);
        $save = TRUE;
      }
    }
    if ($save) {
      $this->getConfig()->set('definitions', $all_overrides)->save();
    }
    return $save;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteOverride($id) {
    return $this->deleteMultipleOverrides(array($id));
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleOverrides(array $ids) {
    $result = array();
    if ($ids) {
      $all_overrides = $this->getConfig()->get('definitions') ?: array();
      $id_keys = array_flip($ids);
      $result = array_intersect_key($all_overrides, $id_keys);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function saveOverride($id, array $definition) {
    // Remove unexpected keys.
    $definition = array_intersect_key($definition, array('menu_name' => 1, 'parent' => 1, 'weight' => 1, 'expanded' => 1, 'hidden' => 1));
    if ($definition) {
      $all_overrides = $this->getConfig()->get('definitions');
      // Combine with any existing data.
      $all_overrides[$id] = $definition + $this->loadOverride($id);
      $this->getConfig()->set('definitions', $all_overrides)->save();
    }
    return array_keys($definition);
  }

}
