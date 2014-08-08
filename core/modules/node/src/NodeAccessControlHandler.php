<?php

/**
 * @file
 * Contains \Drupal\node\NodeAccessControlHandler.
 */

namespace Drupal\node;

use Drupal\Core\Entity\EntityControllerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the access control handler for the node entity type.
 *
 * @see \Drupal\node\Entity\Node
 */
class NodeAccessControlHandler extends EntityAccessControlHandler implements NodeAccessControlHandlerInterface, EntityControllerInterface {

  /**
   * The node grant storage.
   *
   * @var \Drupal\node\NodeGrantStorageInterface
   */
  protected $grantStorage;

  /**
   * Constructs a NodeAccessControlHandler object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\node\NodeGrantDatabaseStorageInterface $grant_storage
   *   The node grant storage.
   */
  public function __construct(EntityTypeInterface $entity_type, NodeGrantDatabaseStorageInterface $grant_storage) {
    parent::__construct($entity_type);
    $this->grantStorage = $grant_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('node.grant_storage')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, $langcode = LanguageInterface::LANGCODE_DEFAULT, AccountInterface $account = NULL) {
    $account = $this->prepareUser($account);

    if ($account->hasPermission('bypass node access')) {
      return TRUE;
    }
    if (!$account->hasPermission('access content')) {
      return FALSE;
    }
    return parent::access($entity, $operation, $langcode, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function createAccess($entity_bundle = NULL, AccountInterface $account = NULL, array $context = array()) {
    $account = $this->prepareUser($account);

    if ($account->hasPermission('bypass node access')) {
      return TRUE;
    }
    if (!$account->hasPermission('access content')) {
      return FALSE;
    }

    return parent::createAccess($entity_bundle, $account, $context);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $node, $operation, $langcode, AccountInterface $account) {
    /** @var \Drupal\node\NodeInterface $node */
    /** @var \Drupal\node\NodeInterface $translation */
    $translation = $node->getTranslation($langcode);
    // Fetch information from the node object if possible.
    $status = $translation->isPublished();
    $uid = $translation->getOwnerId();

    // Check if authors can view their own unpublished nodes.
    if ($operation === 'view' && !$status && $account->hasPermission('view own unpublished content')) {

      if ($account->id() != 0 && $account->id() == $uid) {
        return TRUE;
      }
    }

    // If no module specified either allow or deny, we fall back to the
    // node_access table.
    if (($grants = $this->grantStorage->access($node, $operation, $langcode, $account)) !== NULL) {
      return $grants;
    }

    // If no modules implement hook_node_grants(), the default behavior is to
    // allow all users to view published nodes, so reflect that here.
    if ($operation === 'view') {
      return $status;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $configured_types = node_permissions_get_configured_types();
    if (isset($configured_types[$entity_bundle])) {
      return $account->hasPermission('create ' . $entity_bundle . ' content');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL) {
    $administrative_fields = array('uid', 'status', 'created', 'promote', 'sticky', 'revision_log');
    $read_only_fields = array('changed', 'revision_timestamp', 'revision_uid');
    if ($operation == 'edit' && in_array($field_definition->getName(), $administrative_fields)) {
      return $account->hasPermission('administer nodes');
    }
    if ($operation == 'edit' && in_array($field_definition->getName(), $read_only_fields)) {
      return FALSE;
    }
    return parent::checkFieldAccess($operation, $field_definition, $account, $items);
  }

  /**
   * {@inheritdoc}
   */
  public function acquireGrants(NodeInterface $node) {
    $grants = $this->moduleHandler->invokeAll('node_access_records', array($node));
    // Let modules alter the grants.
    $this->moduleHandler->alter('node_access_records', $grants, $node);
    // If no grants are set and the node is published, then use the default grant.
    if (empty($grants) && $node->isPublished()) {
      $grants[] = array('realm' => 'all', 'gid' => 0, 'grant_view' => 1, 'grant_update' => 0, 'grant_delete' => 0);
    }
    return $grants;
  }

  /**
   * {@inheritdoc}
   */
  public function writeGrants(NodeInterface $node, $delete = TRUE) {
    $grants = $this->acquireGrants($node);
    $this->grantStorage->write($node, $grants, NULL, $delete);
  }

  /**
   * {@inheritdoc}
   */
  public function writeDefaultGrant() {
    $this->grantStorage->writeDefault();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteGrants() {
    $this->grantStorage->delete();
  }

  /**
   * {@inheritdoc}
   */
  public function countGrants() {
    return $this->grantStorage->count();
  }

  /**
   * {@inheritdoc}
   */
  public function checkAllGrants(AccountInterface $account) {
    return $this->grantStorage->checkAll($account);
  }

}
