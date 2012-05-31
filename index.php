<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * The routines here dispatch control to the appropriate handler, which then
 * prints the appropriate page.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt files in the "core" directory.
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd());
// Bootstrap the lowest level of what we need.
require_once DRUPAL_ROOT . '/core/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_CONFIGURATION);

// Create a request object from the HTTPFoundation.
$request = Request::createFromGlobals();

// Set the global $request object. This is a temporary measure to keep legacy
// utility functions working. It should be moved to a dependency injection
// container at some point.
request($request);

// Bootstrap all of Drupal's subsystems, but do not initialize anything that
// depends on the fully resolved Drupal path, because path resolution happens
// during the REQUEST event of the kernel.
// @see Drupal\Core\EventSubscriber\PathSubscriber;
// @see Drupal\Core\EventSubscriber\LegacyRequestSubscriber;
drupal_bootstrap(DRUPAL_BOOTSTRAP_CODE);

$dispatcher = new EventDispatcher();
$resolver = new ControllerResolver();

$kernel = new DrupalKernel($dispatcher, $resolver);
$response = $kernel->handle($request)->prepare($request)->send();
$kernel->terminate($request, $response);
