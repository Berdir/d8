<?php

/**
 * @file
 * Contains \Drupal\user\Theme\UserNegotiator.
 */

namespace Drupal\user\Theme;

use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Theme\ActiveTheme;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines the theme negotiator service for theme configured per user.
 */
class UserNegotiator implements ThemeNegotiatorInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a UserNegotiator object.
   *
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   The entity manager
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(EntityManager $entity_manager, AccountInterface $current_user) {
    $this->entityManager = $entity_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Request $request) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(Request $request) {
    if ($this->currentUser->isAuthenticated()) {
      if ($user = $this->entityManager->getStorageController('user')->load($this->currentUser->id())) {
        // Only select the user selected theme if it is available in the
        // list of themes that can be accessed.
        if ($user->getDefaultTheme() && drupal_theme_access($user->getDefaultTheme())) {
          return $user->getDefaultTheme();
        }
      }
    }
  }

}
