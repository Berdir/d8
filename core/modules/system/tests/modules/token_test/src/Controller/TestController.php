<?php

/**
 * @file
 * Contains \Drupal\token_test\Controller\TestController.
 */

namespace Drupal\token_test\Controller;


use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Utility\Token;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a test controller for token replacement.
 */
class TestController extends ControllerBase {

  /**
   * The token replacement system.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructs a new TestController instance.
   *
   * @param \Drupal\Core\Utility\Token $token
   *   The token replacement system.
   */
  public function __construct(Token $token) {
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('token'));
  }

  /**
   * Provides a token replacement with a node as well as the current user.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return array
   *   The render array.
   */
  public function tokenReplace(NodeInterface $node) {
    $cacheable_metadata = new CacheableMetadata();
    $build['#markup'] = $this->token->replace('Tokens: [node:nid] [current-user:uid]', ['node' => $node], [], $cacheable_metadata);

    $cacheable_metadata->applyTo($build);

    return $build;
  }

  /**
   * Provides a token replacement with a node as well as the current user.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return array
   *   The render array.
   */
  public function tokenReplaceWithoutPassedCacheabilityMetadata(NodeInterface $node) {
    // Note: We explicitly don't pass the cacheability metadata in order to
    // ensure that bubbling works.
    $build['#markup'] = $this->token->replace('Tokens: [node:nid] [current-user:uid]', ['node' => $node], []);

    return $build;
  }

}
