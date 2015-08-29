<?php

/**
 * @file
 * Contains \Drupal\Core\Update\UpdateKernel.
 */

namespace Drupal\Core\Update;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\Site\Settings;
use Drupal\system\Tests\Routing\MockRouteProvider;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Defines a kernel which is used primarily to run the update of Drupal.
 *
 * We use a dedicated kernel + front controller (update.php) in order to be able
 * to repair Drupal if it is in a broken state.
 *
 * @see update.php
 * @see \Drupal\system\Controller\DbUpdateController
 */
class UpdateKernel extends DrupalKernel {

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    try {
      static::bootEnvironment();

      // First boot up basic things, like loading the include files.
      $this->initializeSettings($request);
      $this->boot();
      $container = $this->getContainer();
      /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
      $request_stack = $container->get('request_stack');
      $request_stack->push($request);
      $this->preHandle($request);

      // Handle the actual request. We need the session both for authentication
      // as well as the DB update, like
      // \Drupal\system\Controller\DbUpdateController::batchFinished.
      $this->bootSession($request, $type);
      $result = $this->handleRaw($request);
      $this->shutdownSession($request);

      return $result;
    }
    catch (\Exception $e) {
      return $this->handleException($e, $request, $type);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeContainer() {
    $container = parent::initializeContainer();

    $routes = new RouteCollection();

    $routes->add('<front>', new Route('/', ['_title' => 'Home'], ['_access' => 'TRUE']));
    $routes->add('<none>', new Route('', [], ['_access' => 'TRUE'], ['_no_path' => TRUE]));
    $routes->add('<current>', new Route('<current>'));
    $routes->add('system.site_maintenance_mode', new Route('/admin/config/development/maintenance', ['_title' => 'Maintenance mode'], ['_permission' => 'administer site configuration']));
    $routes->add('system.db_update', new Route('/update.php/{op}', ['op' => 'info'], ['_access' => 'TRUE']));

    $mock_route_provider = new MockRouteProvider($routes);
    $container->set('@router.route_provider', $mock_route_provider);

    return $container;
  }


  /**
   * Generates the actual result of update.php.
   *
   * The actual logic of the update is done in the db update controller.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response object.
   *
   * @see \Drupal\system\Controller\DbUpdateController
   */
  protected function handleRaw(Request $request) {
    $container = $this->getContainer();

    $this->handleAccess($request, $container);

    /** @var \Drupal\Core\Controller\ControllerResolverInterface $controller_resolver */
    $controller_resolver = $container->get('controller_resolver');

    /** @var callable $db_update_controller */
    $db_update_controller = $controller_resolver->getControllerFromDefinition('\Drupal\system\Controller\DbUpdateController::handle');

    $this->setupRequestMatch($request);

    $arguments = $controller_resolver->getArguments($request, $db_update_controller);
    return call_user_func_array($db_update_controller, $arguments);
  }

  /**
   * Boots up the session.
   *
   * bootSession() + shutdownSession() basically simulates what
   * \Drupal\Core\StackMiddleware\Session does.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming request.
   */
  protected function bootSession(Request $request) {
    $container = $this->getContainer();
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
    $session = $container->get('session');
    $session->start();
    $request->setSession($session);
  }

  /**
   * Ensures that the session is saved.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming request.
   */
  protected function shutdownSession(Request $request) {
    if ($request->hasSession()) {
      $request->getSession()->save();
    }
  }

  /**
   * Set up the request with fake routing data for update.php.
   *
   * This fake routing data is needed in order to make batch API work properly.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming request.
   */
  protected function setupRequestMatch(Request $request) {
    $path = $request->getPathInfo();
    $args = explode('/', ltrim($path, '/'));

    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, 'system.db_update');
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $this->getContainer()->get('router.route_provider')->getRouteByName('system.db_update'));
    $op = $args[0] ?: 'info';
    $request->attributes->set('op', $op);
    $request->attributes->set('_raw_variables', new ParameterBag(['op' => $op]));
  }

  /**
   * Checks if the current user has rights to access updates page.
   *
   * If the current user does not have the rights, an exception is thrown.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The incoming request.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when update.php should not be accessible.
   */
  protected function handleAccess(Request $request) {
    /** @var \Drupal\Core\Authentication\AuthenticationManager $authentication_manager */
    $authentication_manager = $this->getContainer()->get('authentication');
    $account = $authentication_manager->authenticate($request) ?: new AnonymousUserSession();

    /** @var \Drupal\Core\Session\AccountProxyInterface $current_user */
    $current_user = $this->getContainer()->get('current_user');
    $current_user->setAccount($account);

    /** @var \Drupal\system\Access\DbUpdateAccessCheck $db_update_access */
    $db_update_access = $this->getContainer()->get('access_check.db_update');

    if (!Settings::get('update_free_access', FALSE) && !$db_update_access->access($account)->isAllowed()) {
      throw new AccessDeniedHttpException('In order to run update.php you need to either be logged in as admin or have set $update_free_access in your settings.php.');
    }
  }

}
