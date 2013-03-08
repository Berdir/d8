<?php

/**
 * @file
 * Contains \Drupal\user\UserAutocompleteController.
 */
namespace Drupal\user;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\ControllerInterface;

/**
 * Controller routines for taxonomy user routes.
 */
class UserAutocompleteController implements ControllerInterface {

  /**
   * The user autocomplete helper class to find matching user names.
   *
   * @var \Drupal\user\UserAutocomplete
   */
  protected $userAutocomplete;

  /**
   * Constructs an UserAutocompleteController object.
   *
   * @param \Drupal\user\UserAutocomplete $user_autocomplete
   *   The user autocomplete helper class to find matching user names.
   */
  public function __construct(UserAutocomplete $user_autocomplete) {
    $this->userAutocomplete = $user_autocomplete;
  }

  /**
   * Implements \Drupal\Core\ControllerInterface::create().
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.autocomplete')
    );
  }

  /**
   * Returns response for the user autocompletion.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   * @param bool $include_anonymous
   *   (optional) TRUE if the the name used to indicate anonymous users (e.g.
   *   "Anonymous") should be autocompleted. Defaults to FALSE.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for existing users.
   *
   * @see \Drupal\user\UserAutocomplete::getMatches()
   */
  public function autocompleteUser(Request $request, $include_anonymous = FALSE) {
    $matches = $this->userAutocomplete->getMatches($request->query->get('q'), $include_anonymous);

    return new JsonResponse($matches);
  }

  /**
   * Returns response for the user autocompletion with the anonymous user.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for existing users.
   *
   * @see \Drupal\user\UserRouteController\autocompleteUser
   */
  public function autocompleteUserAnonymous(Request $request) {
    return $this->autocompleteUser($request, TRUE);
  }

}

