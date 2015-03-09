<?php
/**
 * @file
 * Contains \Drupal\user\Plugin\Validation\Constraint\ProtectedUserFieldConstraintValidator.
 */

namespace Drupal\user\Plugin\Validation\Constraint;

use Drupal\Component\Utility\String;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ProtectedUserFieldConstraint constraint.
 */
class ProtectedUserFieldConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * User storage handler.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The Current User.
   *
   * @var  \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new CommentNameConstraintValidator.
   *
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage handler.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(UserStorageInterface $user_storage, AccountProxyInterface $current_user) {
    $this->userStorage = $user_storage;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('user'),
      $container->get('current_user')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!isset($items)) {
      return;
    }
    /* @var \Drupal\Core\Field\FieldItemListInterface $items */
    $field = $items->getFieldDefinition();

    /* @var \Drupal\user\UserInterface $account */
    $account = $items->getEntity();
    if (!isset($account) || !empty($account->_skipProtectedUserFieldConstraint)) {
      // Looks like we are validating a field not being part of a user, or the
      // constraint should be skipped, so do nothing.
      return;
    }

    // Only validate for existing entities, if this is the current user.
    if (!$account->isNew() && $account->id() == $this->currentUser->id()) {

      /* @var \Drupal\user\UserInterface $account_unchanged */
      $account_unchanged = $this->userStorage
        ->loadUnchanged($account->id());

      $changed = FALSE;

      // Special case for the password, it being empty means that the existing
      // password should not be changed, ignore empty password fields.
      if ($field->getName() != 'pass' || !empty($items->value)) {
        // Compare the values of the field this is being validated on.
        $changed = $items->getValue() != $account_unchanged->get($field->getName())->getValue();
      }
      if ($changed && (!$account->checkExistingPassword($account_unchanged))) {
        $this->context->addViolation($constraint->message, array('!name' => String::placeholder($field->getLabel())));
      }
    }
  }

}
