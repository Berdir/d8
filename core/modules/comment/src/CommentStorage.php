<?php

/**
 * @file
 * Definition of Drupal\comment\CommentStorage.
 */

namespace Drupal\comment;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityDatabaseStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the controller class for comments.
 *
 * This extends the Drupal\Core\Entity\ContentEntityDatabaseStorage class,
 * adding required special handling for comment entities.
 */
class CommentStorage extends ContentEntityDatabaseStorage implements CommentStorageInterface {

  /**
   * The comment statistics service.
   *
   * @var \Drupal\comment\CommentStatisticsInterface
   */
  protected $statistics;

  /**
   * Constructs a CommentStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_info
   *   An array of entity info for the entity type.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\comment\CommentStatisticsInterface $comment_statistics
   *   The comment statistics service.
   */
  public function __construct(EntityTypeInterface $entity_info, Connection $database, EntityManagerInterface $entity_manager, CommentStatisticsInterface $comment_statistics) {
    parent::__construct($entity_info, $database, $entity_manager);
    $this->statistics = $comment_statistics;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_info) {
    return new static(
      $entity_info,
      $container->get('database'),
      $container->get('entity.manager'),
      $container->get('comment.statistics')
    );
  }


  /**
   * {@inheritdoc}
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $revision_id);
    // Specify additional fields from the user table.
    $query->innerJoin('users', 'u', 'base.uid = u.uid');
    // @todo: Move to a computed 'name' field instead.
    $query->addField('u', 'name', 'registered_name');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function mapFromStorageRecords(array $records) {
    // Prepare standard comment fields.
    foreach ($records as $record) {
      $record->name = $record->uid ? $record->registered_name : $record->name;
    }
    return parent::mapFromStorageRecords($records);
  }

  /**
   * {@inheritdoc}
   */
  public function updateEntityStatistics(CommentInterface $comment) {
    $this->statistics->update($comment);
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxThread(EntityInterface $comment) {
    $query = $this->database->select('comment', 'c')
      ->condition('entity_id', $comment->getCommentedEntityId())
      ->condition('field_name', $comment->getFieldName())
      ->condition('entity_type', $comment->getCommentedEntityTypeId());
    $query->addExpression('MAX(thread)', 'thread');
    return $query->execute()
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxThreadPerThread(EntityInterface $comment) {
    $query = $this->database->select('comment', 'c')
      ->condition('entity_id', $comment->getCommentedEntityId())
      ->condition('field_name', $comment->getFieldName())
      ->condition('entity_type', $comment->getCommentedEntityTypeId())
      ->condition('thread', $comment->getParentComment()->getThread() . '.%', 'LIKE');
    $query->addExpression('MAX(thread)', 'thread');
    return $query->execute()
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function getChildCids(array $comments) {
    return $this->database->select('comment', 'c')
      ->fields('c', array('cid'))
      ->condition('pid', array_keys($comments))
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    $schema = parent::getSchema();

    // Marking the respective fields as NOT NULL makes the indexes more
    // performant.
    $schema['comment']['fields']['pid']['not null'] = TRUE;
    $schema['comment']['fields']['status']['not null'] = TRUE;
    $schema['comment']['fields']['entity_id']['not null'] = TRUE;
    $schema['comment']['fields']['created']['not null'] = TRUE;
    $schema['comment']['fields']['thread']['not null'] = TRUE;

    unset($schema['comment']['indexes']['field__pid']);
    unset($schema['comment']['indexes']['field__entity_id']);
    $schema['comment']['indexes'] += array(
      'comment__status_pid' => array('pid', 'status'),
      'comment__num_new' => array(
        'entity_id',
        array('entity_type', 32),
        array('comment_type', EntityTypeInterface::BUNDLE_MAX_LENGTH),
        'status',
        'created',
        'cid',
        'thread',
      ),
      'comment__entity_langcode' => array(
        'entity_id',
        array('entity_type', 32),
        array('comment_type', EntityTypeInterface::BUNDLE_MAX_LENGTH),
        'langcode',
      ),
      'comment__created' => array('created'),
    );
    $schema['comment']['foreign keys'] += array(
      'comment__author' => array(
        'table' => 'users',
        'columns' => array('uid' => 'uid'),
      ),
    );

    return $schema;
  }

}
