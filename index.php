<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt files in the "core" directory.
 */

$uprofiler_path = '/var/www/html';
include_once $uprofiler_path . '/uprofiler_lib/utils/uprofiler_lib.php';
include_once $uprofiler_path . '/uprofiler_lib/utils/uprofiler_runs.php';
uprofiler_enable(UPROFILER_FLAGS_NO_BUILTINS + UPROFILER_FLAGS_MEMORY);

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';

$kernel = new DrupalKernel('prod', $autoloader);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);

$uprofiler_data = uprofiler_disable();
$uprofiler_runs = new uprofilerRuns_Default();
$namespace = 'd8';
$id = $uprofiler_runs->save_run($uprofiler_data, $namespace);
print "<a href='http://localhost/uprofiler_html/?run=$id&sort=excl_wt&source=$namespace'>uprofiler</a>";
