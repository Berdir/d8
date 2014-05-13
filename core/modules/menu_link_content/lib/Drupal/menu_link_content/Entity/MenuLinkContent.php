<?php

/**
 * @file
 * Contains \Drupal\menu_link_content\Entity\MenuLinkContent.
 */

namespace Drupal\menu_link_content\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Url;

/**
 * Defines the menu link content entity class.
 *
 * @EntityType(
 *   id = "menu_link_content",
 *   label = @Translation("Menu link content"),
 *   controllers = {
 *     "storage" = "Drupal\Core\Entity\ContentEntityDatabaseStorage",
 *     "form" = {
 *       "default" = "Drupal\menu_link_content\Form\MenuLinkContentForm",
 *       "delete" = "Drupal\menu_link_content\Form\MenuLinkContentDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer menu",
 *   static_cache = FALSE,
 *   base_table = "menu_link_content",
 *   data_table = "menu_link_content_data",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "bundle" = "bundle"
 *   },
 * )
 */
class MenuLinkContent extends ContentEntityBase implements MenuLinkContentInterface {

  /**
   * A flag for whether this entity is wrapped in a plugin instance.
   *
   * @var bool
   */
  protected $insidePlugin = FALSE;

  /**
   * {@inheritdoc}
   */
  public function setInsidePlugin() {
    $this->insidePlugin = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return $this->get('route_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters() {
    return $this->get('route_parameters')->first()->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setRouteParameters(array $route_parameters) {
    $this->set('route_parameters', array($route_parameters));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return $this->get('url')->value ?: NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrlObject() {
    if ($route_name = $this->getRouteName()) {
      $url = new Url($route_name, $this->getRouteParameters(), $this->getOptions());
    }
    else {
      $path = $this->getUrl();
      if (isset($path)) {
        $url = Url::createFromPath($path);
      }
      else {
        $url = new Url('<front>');
      }
    }

    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuName() {
    return $this->get('menu_name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->get('options')->first()->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setOptions(array $options) {
    $this->set('options', array($options));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->get('description')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return 'menu_link_content:' . $this->uuid();
  }

  /**
   * {@inheritdoc}
   */
  public function isHidden() {
    return (bool) $this->get('hidden')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function isExpanded() {
    return (bool) $this->get('expanded')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentId() {
    return $this->get('parent')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return (int) $this->get('weight')->value;
  }

  /**
   * Builds up the menu plugin definition for this entity.
   *
   * @return array
   *
   * @see \Drupal\Core\Menu\MenuLinkTree::$defaults
   */
  protected function getMenuDefinition() {
    $definition = array();
    $definition['class'] = 'Drupal\menu_link_content\Plugin\Menu\MenuLinkContent';
    $definition['menu_name'] = $this->getMenuName();
    $definition['route_name'] = $this->getRouteName();
    $definition['route_parameters'] = $this->getRouteParameters();
    $definition['url'] = $this->getUrl();
    $definition['options'] = $this->getOptions();
    // Don't bother saving title and description strings, since they are never
    // used.
    $definition['title'] = '';
    $definition['description'] = '';
    $definition['weight'] = $this->getWeight();
    $definition['id'] = $this->getPluginId();
    $definition['metadata'] = array('entity_id' => $this->id());
    $definition['form_class'] = '\Drupal\menu_link_content\Form\MenuLinkContentForm';
    $definition['hidden'] = $this->isHidden() ? 1 : 0;
    $definition['expanded'] = $this->isExpanded() ? 1 : 0;
    $definition['provider'] = 'menu_link_content';
    $definition['discovered'] = 0;
    $definition['parent'] = $this->getParentId();

    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    /** @var \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree */
    $menu_tree = \Drupal::menuTree();

    if ($update) {
      // When the entity is saved via a plugin instance, we should not call
      // the menu tree manager to update the definition a second time.
      if (!$this->insidePlugin) {
        $menu_tree->updateLink($this->getPluginId(), $this->getMenuDefinition(), FALSE);
      }
    }
    else {
      $menu_tree->createLink($this->getPluginId(), $this->getMenuDefinition());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    foreach ($entities as $menu_link) {
      /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $menu_link */
      \Drupal::menuTree()->deleteLink($menu_link->getPluginId(), FALSE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = FieldDefinition::create('integer')
      ->setLabel(t('Content menu link ID'))
      ->setDescription(t('The menu link ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The content menu link UUID.'))
      ->setReadOnly(TRUE);

    $fields['bundle'] = FieldDefinition::create('string')
      ->setLabel(t('Bundle'))
      ->setDescription(t('The content menu link bundle.'))
      ->setReadOnly(TRUE);

    $fields['title'] = FieldDefinition::create('string')
      ->setLabel(t('Menu link title'))
      ->setDescription(t('The text to be used for this link in the menu.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['description'] = FieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setDescription(t('Shown when hovering over the menu link.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'weight' => 0,
      ));

    $fields['menu_name'] = FieldDefinition::create('string')
      ->setLabel(t('Menu name'))
      ->setDescription(t('The menu name. All links with the same menu name (such as "tools") are part of the same menu.'));
      //      ->setSetting('default_value', 'tools')

    // @todo use a link field in the end? see https://drupal.org/node/2235457
    $fields['route_name'] = FieldDefinition::create('string')
      ->setLabel(t('Route name'))
      ->setDescription(t('The machine name of a defined Symfony Route this menu item represents.'));

    $fields['route_parameters'] = FieldDefinition::create('map')
      ->setLabel(t('Route parameters'))
      ->setDescription(t('A serialized array of route parameters of this menu link.'));

    $fields['url'] = FieldDefinition::create('string')
      ->setLabel(t('External link url'))
      ->setDescription(t('The url of the link, in case you have an external link.'));

    $fields['options'] = FieldDefinition::create('map')
      ->setLabel(t('Options'))
      ->setDescription(t('A serialized array of options to be passed to the url() or l() function, such as a query string or HTML attributes.'))
      ->setSetting('default_value', array());

    $fields['external'] = FieldDefinition::create('boolean')
      ->setLabel(t('External'))
      ->setDescription(t('A flag to indicate if the link points to a full URL starting with a protocol, like http:// (1 = external, 0 = internal).'))
      ->setSetting('default_value', 0);

    $fields['expanded'] = FieldDefinition::create('boolean')
      ->setLabel(t('Expanded'))
      ->setDescription(t('Flag for whether this link should be rendered as expanded in menus - expanded links always have their child links displayed, instead of only when the link is in the active trail (1 = expanded, 0 = not expanded).'))
      ->setSetting('default_value', 0)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'boolean',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_onoff',
        'weight' => 0,
      ));

    $fields['hidden'] = FieldDefinition::create('boolean')
      ->setLabel(t('Hidden'))
      ->setDescription(t('A flag for whether the link should be rendered in menus. (1 = a disabled menu item that may be shown on admin screens, -1 = a menu callback, 0 = a normal, visible link).'))
      ->setSetting('default_value', 0);

    $fields['weight'] = FieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('Link weight among links in the same menu at the same depth.'))
      ->setSetting('default_value', 0)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'integer',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'integer',
        'weight' => 0,
      ));

    $fields['langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The node language code.'));

    $fields['parent'] = FieldDefinition::create('string')
      ->setLabel(t('Parent menu link ID'))
      ->setDescription(t('The parent menu link ID.'));

    return $fields;
  }

}

