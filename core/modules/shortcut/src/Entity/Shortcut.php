<?php

/**
 * @file
 * Contains \Drupal\shortcut\Entity\Shortcut.
 */

namespace Drupal\shortcut\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;
use Drupal\shortcut\ShortcutInterface;

/**
 * Defines the shortcut entity class.
 *
 * @ContentEntityType(
 *   id = "shortcut",
 *   label = @Translation("Shortcut link"),
 *   controllers = {
 *     "access" = "Drupal\shortcut\ShortcutAccessController",
 *     "form" = {
 *       "default" = "Drupal\shortcut\ShortcutForm",
 *       "add" = "Drupal\shortcut\ShortcutForm",
 *       "edit" = "Drupal\shortcut\ShortcutForm",
 *       "delete" = "Drupal\shortcut\Form\ShortcutDeleteForm"
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "shortcut",
 *   data_table = "shortcut_field_data",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "bundle" = "shortcut_set",
 *     "label" = "title"
 *   },
 *   links = {
 *     "canonical" = "shortcut.link_edit",
 *     "delete-form" = "shortcut.link_delete",
 *     "edit-form" = "shortcut.link_edit",
 *     "admin-form" = "shortcut.link_edit"
 *   },
 *   bundle_entity_type = "shortcut_set"
 * )
 */
class Shortcut extends ContentEntityBase implements ShortcutInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($link_title) {
    $this->set('title', $link_title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->get('weight')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return $this->get('link')->route_name;
  }

  /**
   * {@inheritdoc}
   */
  public function setRouteName($route_name) {
    $this->get('link')->route_name = $route_name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteParams() {
    return $this->get('link')->route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function setRouteParams($route_parameters) {
    $this->get('link')->route_parameters = $route_parameters;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return new Url($this->link->route_name, (array) $this->link->route_parameters, (array) $this->link->options);
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);

    if (!isset($values['shortcut_set'])) {
      $values['shortcut_set'] = 'default';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Entity::postSave() calls Entity::invalidateTagsOnSave(), which only
    // handles the regular cases. The Shortcut entity has one special case: a
    // newly created shortcut is *also* added to a shortcut set, so we must
    // invalidate the associated shortcut set's cache tag.
    if (!$update) {
      Cache::invalidateTags($this->getCacheTag());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = FieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the shortcut.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the shortcut.'))
      ->setReadOnly(TRUE);

    $fields['shortcut_set'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Shortcut set'))
      ->setDescription(t('The bundle of the shortcut.'))
      ->setSetting('target_type', 'shortcut_set')
      ->setRequired(TRUE);

    $fields['title'] = FieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the shortcut.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setDefaultValue('')
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'weight' => -10,
        'settings' => array(
          'size' => 40,
        ),
      ));

    $fields['weight'] = FieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('Weight among shortcuts in the same shortcut set.'));

    $fields['link'] = FieldDefinition::create('link')
      ->setLabel(t('Path'))
      ->setDescription(t('The location this shortcut points to.'))
      ->setRequired(TRUE)
      ->setTranslatable(FALSE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 560,
        'link_type' => LinkItemInterface::LINK_INTERNAL,
        'title' => DRUPAL_DISABLED,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'link',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of the shortcut.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTag() {
    return $this->shortcut_set->entity->getCacheTag();
  }

  /**
   * {@inheritdoc}
   */
  public function getListCacheTags() {
    return $this->shortcut_set->entity->getListCacheTags();
  }

}
