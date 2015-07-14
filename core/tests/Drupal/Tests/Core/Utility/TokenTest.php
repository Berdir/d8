<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Utility\TokenTest.
 */

namespace Drupal\Tests\Core\Utility;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Utility\Token;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Utility\Token
 * @group Utility
 */
class TokenTest extends UnitTestCase {

  /**
   * The cache used for testing.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cache;

  /**
   * The language manager used for testing.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $languageManager;

  /**
   * The module handler service used for testing.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The language interface used for testing.
   *
   * @var \Drupal\Core\Language\LanguageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $language;

  /**
   * The token service under test.
   *
   * @var \Drupal\Core\Utility\Token|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $token;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cacheTagsInvalidator;

  /**
   * The cache contexts manager.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected $cacheContextManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->cache = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $this->languageManager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->language = $this->getMock('\Drupal\Core\Language\LanguageInterface');

    $this->cacheTagsInvalidator = $this->getMock('\Drupal\Core\Cache\CacheTagsInvalidatorInterface');

    $this->renderer = $this->getMock('Drupal\Core\Render\RendererInterface');

    $this->token = new Token($this->moduleHandler, $this->cache, $this->languageManager, $this->cacheTagsInvalidator, $this->renderer);

    $container = new ContainerBuilder();
    $this->cacheContextManager = new CacheContextsManager($container, [
      'current_user',
      'custom_context'
    ]);
    $container->set('cache_contexts_manager', $this->cacheContextManager);
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::getInfo
   */
  public function testGetInfo() {
    $token_info = array(
      'types' => array(
        'foo' => array(
          'name' => $this->randomMachineName(),
        ),
      ),
    );

    $this->language->expects($this->atLeastOnce())
      ->method('getId')
      ->will($this->returnValue($this->randomMachineName()));

    $this->languageManager->expects($this->once())
      ->method('getCurrentLanguage')
      ->with(LanguageInterface::TYPE_CONTENT)
      ->will($this->returnValue($this->language));

    // The persistent cache must only be hit once, after which the info is
    // cached statically.
    $this->cache->expects($this->once())
      ->method('get');
    $this->cache->expects($this->once())
      ->method('set')
      ->with('token_info:' . $this->language->getId(), $token_info);

    $this->moduleHandler->expects($this->once())
      ->method('invokeAll')
      ->with('token_info')
      ->will($this->returnValue($token_info));
    $this->moduleHandler->expects($this->once())
      ->method('alter')
      ->with('token_info', $token_info);

    // Get the information for the first time. The cache should be checked, the
    // hooks invoked, and the info should be set to the cache should.
    $this->token->getInfo();
    // Get the information for the second time. The data must be returned from
    // the static cache, so the persistent cache must not be accessed and the
    // hooks must not be invoked.
    $this->token->getInfo();
  }

  /**
   * @covers ::replace
   */
  public function testReplaceWithCacheableMetadataObject() {
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn(['[node:title]' => 'hello world']);

    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->setCacheContexts(['current_user']);
    $cacheable_metadata->setCacheMaxAge(12);

    $node = $this->prophesize('Drupal\node\NodeInterface');
    $node->getCacheTags()->willReturn(['node:1']);
    $node->getCacheContexts()->willReturn(['custom_context']);
    $node->getCacheMaxAge()->willReturn(10);
    $node = $node->reveal();

    $result = $this->token->replace('[node:title]', ['node' => $node], [], $cacheable_metadata);
    $this->assertEquals('hello world', $result);

    $this->assertEquals(['node:1'], $cacheable_metadata->getCacheTags());
    $this->assertEquals([
      'current_user',
      'custom_context'
    ], $cacheable_metadata->getCacheContexts());
    $this->assertEquals(10, $cacheable_metadata->getCacheMaxAge());
  }

  /**
   * @covers ::replace
   */
  public function testReplaceWithHookTokensWithCacheableMetadata() {
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturnCallback(function ($hook_name, $args) {
        $cacheable_metadata = $args[4];
        $cacheable_metadata->addCacheContexts(['custom_context']);
        $cacheable_metadata->addCacheTags(['node:1']);
        $cacheable_metadata->setCacheMaxAge(10);

        return ['[node:title]' => 'hello world'];
      });

    $node = $this->prophesize('Drupal\node\NodeInterface');
    $node->getCacheContexts()->willReturn([]);
    $node->getCacheTags()->willReturn([]);
    $node->getCacheMaxAge()->willReturn(14);
    $node = $node->reveal();

    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->setCacheContexts(['current_user']);
    $cacheable_metadata->setCacheMaxAge(12);

    $result = $this->token->replace('[node:title]', ['node' => $node], [], $cacheable_metadata);
    $this->assertEquals('hello world', $result);
    $this->assertEquals(['node:1'], $cacheable_metadata->getCacheTags());
    $this->assertEquals([
      'current_user',
      'custom_context'
    ], $cacheable_metadata->getCacheContexts());
    $this->assertEquals(10, $cacheable_metadata->getCacheMaxAge());
  }

  /**
   * @covers ::replace
   * @covers ::replace
   */
  public function testReplaceWithHookTokensAlterWithCacheableMetadata() {
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn([]);

    $this->moduleHandler->expects($this->any())
      ->method('alter')
      ->willReturnCallback(function ($hook_name, array &$replacements, array $context, CacheableMetadata $cacheable_metadata) {
        $replacements['[node:title]'] = 'hello world';
        $cacheable_metadata->addCacheContexts(['custom_context']);
        $cacheable_metadata->addCacheTags(['node:1']);
        $cacheable_metadata->setCacheMaxAge(10);
      });

    $node = $this->prophesize('Drupal\node\NodeInterface');
    $node->getCacheContexts()->willReturn([]);
    $node->getCacheTags()->willReturn([]);
    $node->getCacheMaxAge()->willReturn(14);
    $node = $node->reveal();

    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->setCacheContexts(['current_user']);
    $cacheable_metadata->setCacheMaxAge(12);

    $result = $this->token->replace('[node:title]', ['node' => $node], [], $cacheable_metadata);
    $this->assertEquals('hello world', $result);
    $this->assertEquals(['node:1'], $cacheable_metadata->getCacheTags());
    $this->assertEquals([
      'current_user',
      'custom_context'
    ], $cacheable_metadata->getCacheContexts());
    $this->assertEquals(10, $cacheable_metadata->getCacheMaxAge());
  }

  /**
   * @covers ::resetInfo
   */
  public function testResetInfo() {
    $this->cacheTagsInvalidator->expects($this->once())
      ->method('invalidateTags')
      ->with(['token_info']);

    $this->token->resetInfo();
  }

}
