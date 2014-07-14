<?php

/**
 * @file
 * Contains \Drupal\link\Plugin\Field\FieldType\LinkItem.
 */

namespace Drupal\link\Plugin\Field\FieldType;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\link\LinkItemInterface;

/**
 * Plugin implementation of the 'link' field type.
 *
 * @FieldType(
 *   id = "link",
 *   label = @Translation("Link"),
 *   description = @Translation("Stores a URL string, optional varchar link text, and optional blob of attributes to assemble a link."),
 *   default_widget = "link_default",
 *   default_formatter = "link",
 *   constraints = {"LinkType" = {}}
 * )
 */
class LinkItem extends FieldItemBase implements LinkItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultInstanceSettings() {
    return array(
      'title' => DRUPAL_OPTIONAL,
      'link_type' => LinkItemInterface::LINK_GENERIC
    ) + parent::defaultInstanceSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['url'] = DataDefinition::create('string')
      ->setLabel(t('URL'));

    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Link text'));

    $properties['route_name'] = DataDefinition::create('string')
      ->setLabel(t('Route name'));

    $properties['route_parameters'] = MapDataDefinition::create()
      ->setLabel(t('Route parameters'));

    $properties['options'] = MapDataDefinition::create()
      ->setLabel(t('Options'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'url' => array(
          'description' => 'The URL of the link.',
          'type' => 'varchar',
          'length' => 2048,
          'not null' => FALSE,
        ),
        'title' => array(
          'description' => 'The link text.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ),
        'route_name' => array(
          'description' => 'The machine name of a defined Route this link represents.',
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
        ),
        'route_parameters' => array(
          'description' => 'Serialized array of route parameters of the link.',
          'type' => 'blob',
          'size' => 'big',
          'not null' => FALSE,
          'serialize' => TRUE,
        ),
        'options' => array(
          'description' => 'Serialized array of options for the link.',
          'type' => 'blob',
          'size' => 'big',
          'not null' => FALSE,
          'serialize' => TRUE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function instanceSettingsForm(array $form, array &$form_state) {
    $element = array();

    $element['link_type'] = array(
      '#type' => 'radios',
      '#title' => t('Allowed link type'),
      '#default_value' => $this->getSetting('link_type'),
      '#options' => array(
        static::LINK_INTERNAL => t('Internal links only'),
        static::LINK_EXTERNAL => t('External links only'),
        static::LINK_GENERIC => t('Both internal and external links'),
      ),
    );

    $element['title'] = array(
      '#type' => 'radios',
      '#title' => t('Allow link text'),
      '#default_value' => $this->getSetting('title'),
      '#options' => array(
        DRUPAL_DISABLED => t('Disabled'),
        DRUPAL_OPTIONAL => t('Optional'),
        DRUPAL_REQUIRED => t('Required'),
      ),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    // Trim any spaces around the link text.
    $this->title = trim($this->title);

    $parsed_url = UrlHelper::parse(trim($this->url));
    $this->url = $parsed_url['path'];
    $this->options = ($this->options) ?: array();

    // The link 'query' and 'fragment' parts need to be split and updated only
    // if they exist (e.g. when the 'url' property is new or updated).
    $options = array();
    if (!empty($parsed_url['query'])) {
      $options['query'] = $parsed_url['query'];
    }
    if (!empty($parsed_url['fragment'])) {
      $options['fragment'] = $parsed_url['fragment'];
    }
    $this->options = $options + $this->options;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if ($this->isExternal()) {
      return $this->url === NULL || $this->url === '';
    }
    else {
      return $this->route_name === NULL || $this->route_name === '';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isExternal() {
    // External links don't have a route_name value.
    return empty($this->route_name);
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    // Unserialize the values.
    // @todo The storage controller should take care of this.
    foreach (array('route_parameters', 'options') as $property) {
      if (isset($values[$property]) && is_string($values[$property])) {
        $values[$property] = unserialize($values[$property]);
      }
    }
    parent::setValue($values, $notify);
  }

}
