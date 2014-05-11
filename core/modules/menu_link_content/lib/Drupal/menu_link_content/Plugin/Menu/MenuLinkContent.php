<?php

/**
 * @file
 * Contains \Drupal\menu_link_content\Plugin\Menu\MenuLinkContent.
 */

namespace Drupal\menu_link_content\Plugin\Menu;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Menu\MenuLinkBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the menu link plugin for content menu link.s
 */
class MenuLinkContent extends MenuLinkBase implements ContainerFactoryPluginInterface {

  /**
   * Defines all overrideable values.
   *
   * @var array
   */
  protected $overrideAllowed = array(
    'menu_name' => 1,
    'parent' => 1,
    'weight' => 1,
    'expanded' => 1,
    'hidden' => 1,
    'title' => 1,
    'description' => 1,
    'route_name' => 1,
    'route_parameters' => 1,
    'url' => 1,
    'options' => 1,
  );

  /**
   * The menu link content entity connected to this plugin instance.
   *
   * @var \Drupal\menu_link_content\Entity\MenuLinkContentInterface
   */
  protected $entity;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new MenuLinkContent.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The static override storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * Loads the entity associated with this menu link.
   *
   * @return \Drupal\menu_link_content\Entity\MenuLinkContentInterface
   */
  protected function getEntity() {
    if (empty($this->entity)) {
      $storage = $this->entityManager->getStorage('menu_link_content');
      if (!empty($this->pluginDefinition['metadata']['entity_id'])) {
        $this->entity = $storage->load($this->pluginDefinition['metadata']['entity_id']);
      }
      else {
        // Fallback to the loading by the uuid.
        $uuid = $this->getDerivativeId();
        $links = $storage->loadByProperties(array('uuid' => $uuid));
        $this->entity = reset($links);
      }
    }
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->getEntity()->getTitle();
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->getEntity()->getDescription();
  }

  /**
   * {@inheritdoc}
   */
  public function getDeleteRoute() {
    return array(
      'route_name' => 'menu_link_content.link_delete',
      'route_parameters' => array('menu_link_content' => $this->getEntity()->id()),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function updateLink(array $new_definition_values, $persist) {
    $overrides = array_intersect_key($new_definition_values, $this->overrideAllowed);
    // Update the definition.
    $this->pluginDefinition = $overrides + $this->getPluginDefinition();
    if ($persist) {
      $entity = $this->getEntity();
      foreach ($overrides as $key => $value) {
        $entity->{$key}->value = $value;
      }
      $this->entityManager->getStorage('menu_link_content')->save($entity);
    }

    return $this->pluginDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function isDeletable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLink() {
    // @todo: Flag this call if possible so we don't call the menu tree manager.
    $this->getEntity()->delete();
  }

}
