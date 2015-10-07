<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt files in the "core" directory.
 */

/**
 * Environment assumptions:
 * - XHProf installation directory must be hardcoded below
 * - FlameGraph (ttps://github.com/brendangregg/FlameGraph) is checked out in a
 *   directory called 'FlameGraph', which is a sibling directory of
 *   xhprof.output_dir.
 * - When you want to profile while ignoring some functions, hardcode functions
 *   to ignore below.
 */

// Hierarchical profiling. ?XHPROF_ENABLE
if (isset($_REQUEST['XHPROF_ENABLE'])) {
  $ignored_functions = [];
  if ($_REQUEST['XHPROF_ENABLE'] == 'ignore') {
    $ignored_functions = [
      // DB.
      'Drupal\Core\Database\Connection::query',
      'Drupal\Core\Database\Statement::execute',
      'Drupal\Core\Database\Query\Update::execute',
      'PDOStatement::execute',
      // FS.
      'file_exists',
      // Class loader.
      'Composer\Autoload\ClassLoader::findFileWithExtension',
      'Composer\Autoload\ClassLoader::findFile',
      'Composer\Autoload\ClassLoader::loadClass',
      'spl_autoload_call',
      // DIC.
      'Symfony\Component\DependencyInjection\Container::get',
      'Drupal\Core\DependencyInjection\Container::get',
    ];
  }
  xhprof_enable(XHPROF_FLAGS_MEMORY, ['ignored_functions' => $ignored_functions]);
  // xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
  register_shutdown_function(function () {
    $namespace = $_SERVER['HTTP_HOST'];
    $xhprof_data = xhprof_disable();
    // Look at the output of
    //   brew info php55-xhprof | grep `brew --cellar php55-xhprof`
    // to know which path.
    $XHPROF_ROOT = '/var/www/html';
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

    $xhprof_runs = new XHProfRuns_Default();

    $run_id = $xhprof_runs->save_run($xhprof_data, $namespace);

    echo "<div style='margin:1rem;padding:1rem;border:1px solid black;background-color: white;'>
      <span style='font-size:150%'>
        <a href='http://localhost/xhprof_html/?run=$run_id&source=$namespace'>View run</a>
        â€”
        <tt>$run_id</tt>
        â€”
        <tt>$namespace</tt>
      </span>
    </div>";
  });
}

// Sampling profiling. ?XHPROF_SAMPLE_ENABLE&NAME=<name>&SAMPLES=1000
// Optionally:
//  - &OLD_NAME=<name>, to generate a diff
if (isset($_REQUEST['XHPROF_SAMPLE_ENABLE'])) {
  xhprof_sample_enable();

  // Validate arguments.
  if (!isset($_REQUEST['NAME'])) {
    echo 'Please specify a NAME parameter to name this round of sample profiling.';
    exit;
  }
  if (!isset($_REQUEST['SAMPLES'])) {
    echo 'Please specify a SAMPLES parameter to indicate the number of samples to collect.';
    exit;
  }

  register_shutdown_function(function () {
    $xhprof_data = xhprof_sample_disable();
    $sample_profiling_dir = ini_get('xhprof.output_dir') . '/sample-profiling/';
    if (!is_dir($sample_profiling_dir)) {
      mkdir($sample_profiling_dir);
    }
    $name = $_REQUEST['NAME'];
    $samples_dir = $sample_profiling_dir . $name . '.samples';

    // Store the collected sample profile data.
    if (!file_exists($samples_dir)) {
      mkdir($samples_dir, 0777);
    }
    $filename = $samples_dir . '/' . $_REQUEST['SAMPLES'] . '.sample_xhprof';
    file_put_contents($filename, serialize($xhprof_data));

    // Trigger a reload for the next sample, or if this was the last sample,
    // show a message.
    if ($_REQUEST['SAMPLES'] > 1) {
      // Get the next sample.
      $next_url = preg_replace('/SAMPLES=(\d+)/', 'SAMPLES=' . ((int)$_REQUEST['SAMPLES'] - 1), $_SERVER['REQUEST_URI']);
      echo "<script>window.location = '$next_url';</script>";
    }
    // If this was the last sample, calculate the FlameGraphs.
    else {
      $file_pattern = $samples_dir . '/*.sample_xhprof';
      $flamegraph_dir = '/home/berdir/tools';

      // Generate FlameGraphs.
      $stacks_file = $sample_profiling_dir . $name . '.stacks';
      $flamegraph_svg_file = $sample_profiling_dir . $name . '-FlameGraph.svg';
      $reverse_flamegraph_svg_file = $sample_profiling_dir . $name . '-FlameGraph-reverse.svg';
      file_put_contents($stacks_file, xhprof_sample_files_to_stacks(glob($file_pattern)));
      var_dump("cat $stacks_file | $flamegraph_dir/FlameGraph/flamegraph.pl --title '$name FlameGraph'> $flamegraph_svg_file");
      shell_exec("cat $stacks_file | $flamegraph_dir/FlameGraph/flamegraph.pl --title '$name FlameGraph'> $flamegraph_svg_file");
      shell_exec("cat $stacks_file | $flamegraph_dir/FlameGraph/flamegraph.pl --reverse --title '$name reverse FlameGraph'> $reverse_flamegraph_svg_file");
      $files = ['file://' . $flamegraph_svg_file, 'file://' . $reverse_flamegraph_svg_file];

      // If OLD_NAME is specified, then also create a diff FlameGraph.
      if (isset($_REQUEST['OLD_NAME'])) {
        $old_name = $_REQUEST['OLD_NAME'];
        $old_stacks_file = $sample_profiling_dir . $old_name . '.stacks';
        $diff_flamegraph_svg_file = $sample_profiling_dir . $name . '-FlameGraph-compared-to-old-' . $old_name . '.svg';
        shell_exec("$flamegraph_dir/FlameGraph/difffolded.pl $old_stacks_file $stacks_file | $flamegraph_dir/FlameGraph/flamegraph.pl --title 'Diff FlameGraph, old = $old_name, new = $name' > $diff_flamegraph_svg_file");
        $files[] = 'file://' . $diff_flamegraph_svg_file;
      }

      // Message.
      echo "<div style='margin:1rem;padding:1rem;border:1px solid black;background-color:white;'>
        <span style='font-size:150%'>
          FlameGraphs: <pre>" . implode("\n", $files) . "</pre>
          Samples written to: <pre>
          $file_pattern
          </pre>
        </span>
      </div>";
    }
  });
}

function xhprof_sample_files_to_stacks($files) {
  $stacks = array();

  foreach ($files as $file) {
    if (!file_exists($file)) throw new RuntimeException("$file doesn't exist!");

    $file = strpos($file, 'gz') === (strlen($file) - 2) ? "compress.zlib://$file" : $file;

    $raw_xhprof = @unserialize(file_get_contents($file));

    if ($raw_xhprof === FALSE || empty($raw_xhprof)) {
      continue;
    }

    foreach ($raw_xhprof as $stack) {
      $stack_key = implode(";", explode("==>", $stack));
      if (!isset($stacks[$stack_key])) $stacks[$stack_key] = 0;
      $stacks[$stack_key]++;
    }
  }

  $output = '';
  foreach ($stacks as $stack => $count) {
    $output .= "$stack $count" . PHP_EOL;
  }

  return $output;
}

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';

$kernel = new DrupalKernel('prod', $autoloader);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
