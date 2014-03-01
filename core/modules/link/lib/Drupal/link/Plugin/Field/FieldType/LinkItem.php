<?php

/**
 * @file
 * Contains \Drupal\email\Plugin\Field\FieldType\LinkItem.
 */

namespace Drupal\link\Plugin\Field\FieldType;

use Drupal\Component\Utility\Url as UrlHelper;
use Drupal\Core\Field\ConfigFieldItemBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\link\LinkItemInterface;

/**
 * Plugin implementation of the 'link' field type.
 *
 * @FieldType(
 *   id = "link",
 *   label = @Translation("Link"),
 *   description = @Translation("Stores a URL string, optional varchar link text, and optional blob of options to assemble a link."),
 *   instance_settings = {
 *     "url_type" = \Drupal\link\LinkItemInterface::LINK_GENERIC,
 *     "title" = "1"
 *   },
 *   default_widget = "link_default",
 *   default_formatter = "link"
 * )
 */
class LinkItem extends ConfigFieldItemBase implements LinkItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldDefinitionInterface $field_definition) {
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
  public static function schema(FieldDefinitionInterface $field_definition) {
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

    $element['url_type'] = array(
      '#type' => 'radios',
      '#title' => t('Allowed url type'),
      '#default_value' => $this->getSetting('url_type'),
      '#options' => array(
        static::LINK_INTERNAL => t('Internal urls only'),
        static::LINK_EXTERNAL => t('External urls only'),
        static::LINK_GENERIC => t('Both internal and external urls'),
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

    // Split out the link 'query' and 'fragment' parts.
    $parsed_url = UrlHelper::parse(trim($this->url));
    $this->url = $parsed_url['path'];
    $this->options = array(
      'query' => $parsed_url['query'],
      'fragment' => $parsed_url['fragment'],
    ) + $this->options;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('url')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public function isExternal() {
    // External URLs don't have a route_name value.
    return empty($this->route_name);
  }

}
