<?php

/**
 * @file
 * Contains \Drupal\aggregator_test\Plugin\aggregator\fetcher\TestFetcher.
 */

namespace Drupal\aggregator_test\Plugin\aggregator\fetcher;

use Drupal\aggregator\Plugin\FetcherInterface;
use Drupal\aggregator\Plugin\Core\Entity\Feed;
use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Guzzle\Http\Exception\BadResponseException;

/**
 * Defines a test fetcher implementation.
 *
 * Uses http_default_client class to download the feed.
 *
 * @Plugin(
 *   id = "aggregator_test_fetcher",
 *   title = @Translation("Test fetcher"),
 *   description = @Translation("Dummy fetcher for testing purposes.")
 * )
 */
class TestFetcher implements FetcherInterface {

  /**
   * Implements \Drupal\aggregator\Plugin\FetcherInterface::fetch().
   * @todo Actually test this.
   */
  public function fetch(Feed $feed) {}
}
