<?php

/**
 * @file
 * Contains \Drupal\Core\Config\Entity\ConfigEntityType.
 */

namespace Drupal\Core\Config\Entity;

use Drupal\Core\Entity\EntityType;
use Drupal\Core\Config\ConfigPrefixLengthException;
use Drupal\Component\Utility\String;

/**
 * Provides an implementation of a config entity type and its metadata.
 */
class ConfigEntityType extends EntityType {

  /**
   * Returns the config prefix used by the configuration entity type.
   *
   * @var string
   */
  protected $config_prefix;

  /**
   * {@inheritdoc}
   */
  public function getControllerClasses() {
    return parent::getControllerClasses() + array(
      'storage' => 'Drupal\Core\Config\Entity\ConfigEntityStorage',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigPrefix() {
    // Ensure that all configuration entities are prefixed by the module that
    // provides the configuration entity type. This ensures that default
    // configuration will be created as expected during module install and
    // dependencies can be calculated without the modules that provide the
    // entity types being installed.
    if (isset($this->config_prefix)) {
      $config_prefix = $this->provider . '.' . $this->config_prefix;
    }
    else {
      $config_prefix = $this->provider . '.' . $this->id();
    }

    if (strlen($config_prefix) > 83) {
      throw new ConfigPrefixLengthException(String::format('The @config_prefix config_prefix length is larger than the maximum limit of @char_count characters', array(
        '@config_prefix' => $config_prefix,
        '@char_count' => 83,
      )));
    }
    return $config_prefix;
  }

  /**
   * {@inheritdoc}
   */
  public function getKeys() {
    // Always add a default 'uuid' key.
    return array('uuid' => 'uuid') + parent::getKeys();
  }


  /**
   * {@inheritdoc}
   */
  public function getBaseTable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionDataTable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionTable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataTable() {
    return FALSE;
  }

}
