<?php

/**
 * @file
 * Contains \Drupal\smart_cache_test\SmartCacheTestController.
 */

namespace Drupal\smart_cache_test;

use Drupal\Component\Utility\SafeMarkup;
use Symfony\Component\HttpFoundation\Response;

class SmartCacheTestController {

  public function response() {
    return new Response('foobar');
  }

  public function html() {
    return [
      'content' => [
        '#markup' => 'Hello world.',
      ],
    ];
  }

  public function htmlWithCacheContexts() {
    $build = $this->html();
    $build['dynamic_part'] = [
      '#markup' => SafeMarkup::format('Hello there, %animal.', ['%animal' => \Drupal::requestStack()->getCurrentRequest()->query->get('animal')]),
      '#cache' => [
        'contexts' => [
          'url.query_args:animal',
        ],
      ],
    ];
    return $build;
  }

  public function htmlUncacheable() {
    $build = $this->html();
    $build['very_dynamic_part'] = [
      '#markup' => 'Drupal cannot handle the awesomeness of llamas.',
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    return $build;
  }

}
