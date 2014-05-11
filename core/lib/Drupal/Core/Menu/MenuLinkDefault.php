<?php

/**
 * @file
 * Contains \Drupal\Core\Menu\MenuLinkDefault.
 */

namespace Drupal\Core\Menu;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default object used for MenuLink plugins.
 */
class MenuLinkDefault extends MenuLinkBase implements ContainerFactoryPluginInterface {

  /**
   * @var array
   */
  protected $overrideAllowed = array(
    'menu_name' => 1,
    'parent' => 1,
    'weight' => 1,
    'expanded' => 1,
    'hidden' => 1,
  );

  /**
   * @var \Drupal\Core\Menu\StaticMenuLinkOverridesInterface
   */
  protected $staticOverride;

  /**
   * Constructs a new MenuLinkDefault.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\StaticMenuLinkOverridesInterface $static_override
   *   The static override storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StaticMenuLinkOverridesInterface $static_override) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->staticOverride = $static_override;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu_link.static.overrides')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isResetable() {
    // The link can be reset if it was discovered and has an override.
    return $this->pluginDefinition['discovered'] && $this->staticOverride->loadOverride($this->getPluginId());
  }

  /**
   * {@inheritdoc}
   */
  public function updateLink(array $new_definition_values, $persist) {
    $overrides = array_intersect_key($new_definition_values, $this->overrideAllowed);
    if ($persist) {
      $this->staticOverride->saveOverride($this->getPluginId(), $overrides);
    }
    // Update the definition.
    $this->pluginDefinition = $overrides + $this->getPluginDefinition();
    return $this->pluginDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function persistLinkDeletion() {
    // @todo - what should this do by default?
  }
}
