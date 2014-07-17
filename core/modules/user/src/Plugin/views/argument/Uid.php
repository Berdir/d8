<?php

/**
 * @file
 * Contains \Drupal\user\Plugin\views\argument\Uid.
 */

namespace Drupal\user\Plugin\views\argument;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\views\Plugin\views\argument\Numeric;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept a user id.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("user_uid")
 */
class Uid extends Numeric {

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param mixed $configuration
   *   Configuration for the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The user storage.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityStorageInterface $storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('entity.manager')->getStorage('user'));
  }

  /**
   * Override the behavior of title(). Get the name of the user.
   *
   * @return array
   *    A list of usernames.
   */
  public function titleQuery() {
    return array_map(function($account) {
      return String::checkPlain($account->label());
    }, $this->storage->loadMultiple($this->value));
  }

}
