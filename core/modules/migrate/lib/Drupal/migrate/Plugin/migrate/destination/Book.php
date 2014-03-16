<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityNodeBook.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\field\FieldInfo;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\MigratePluginManager;
use Drupal\migrate\Row;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateDestination(
 *   id = "book",
 *   provider = "book"
 * )
 */
class Book extends EntityContentBase {

  /**
   * A callable switching the current user to a node administrator.
   *
   * @var Callable
   */
  protected $setNodeAdmin;

  /**
   * A callable which resets the user as it was before $setNodeAdmin.
   *
   * @var Callable
   */
  protected $setCurrentUser;

  /**
   * {@inheritdoc}
   * @param UserInterface $account
   *   A node administrator account.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, EntityStorageControllerInterface $storage_controller, array $bundles, MigratePluginManager $plugin_manager, FieldInfo $field_info, Callable $set_current_user, Callable $set_node_admin) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $storage_controller, $bundles, $plugin_manager, $field_info);
    $this->setCurrentUser = $set_current_user;
    $this->setNodeAdmin = $set_node_admin;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration = NULL) {
    $entity_type_id = 'node';
    $entity_manager = $container->get('entity.manager');
    $superuser = $entity_manager->getStorageController('user')->load(1);
    $current_user = $container->get('current_user');
    $save_session = drupal_save_session();
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $entity_manager->getStorageController($entity_type_id),
      array_keys($entity_manager->getBundleInfo($entity_type_id)),
      $container->get('plugin.manager.migrate.entity_field'),
      $container->get('field.info'),
      function () use ($container, $current_user, $save_session) { $container->set('current_user', $current_user); drupal_save_session($save_session); },
      function () use ($container, $superuser) { drupal_save_session(FALSE); $container->set('current_user', $superuser); }
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function save(ContentEntityInterface $entity, array $old_destination_id_values = array()) {
    // book_node_presave() enforces a new revision for book changes unless
    // the current user is a node administrator so switch to one.
    call_user_func($this->setNodeAdmin);
    $return = parent::save($entity, $old_destination_id_values);
    // Now switch back to the current user.
    call_user_func($this->setCurrentUser);
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  protected function updateEntity(EntityInterface $entity, Row $row) {
    $entity->book = $row->getDestinationProperty('book');
  }

}
