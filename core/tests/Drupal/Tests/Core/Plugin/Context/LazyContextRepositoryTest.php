<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Plugin\Context\LazyContextRepositoryTest.
 */

namespace Drupal\Tests\Core\Plugin\Context;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\LazyContextRepository;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\Core\Plugin\Context\LazyContextRepository
 * @group context
 */
class LazyContextRepositoryTest extends UnitTestCase {

  /**
   * The container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->container = new ContainerBuilder();
  }

  /**
   * @covers ::getRunTimeContexts
   */
  public function testGetRunTimeContextsSingle() {
    $contexts = $this->setupContextAndProvider('test_provider', ['test_context']);

    $lazy_context_repository = new LazyContextRepository($this->container, ['test_provider']);
    $run_time_contexts = $lazy_context_repository->getRunTimeContexts(['@test_provider:test_context']);
    $this->assertEquals(['@test_provider:test_context' => $contexts[0]], $run_time_contexts);
  }

  /**
   * @covers ::getRunTimeContexts
   */
  public function testGetRunTimeMultipleContextsPerService() {
    $contexts = $this->setupContextAndProvider('test_provider', ['test_context0', 'test_context1']);

    $lazy_context_repository = new LazyContextRepository($this->container, ['test_provider']);
    $run_time_contexts = $lazy_context_repository->getRunTimeContexts(['@test_provider:test_context0', '@test_provider:test_context1']);
    $this->assertEquals(['@test_provider:test_context0' => $contexts[0], '@test_provider:test_context1' => $contexts[1]], $run_time_contexts);
  }

  /**
   * @covers ::getRunTimeContexts
   */
  public function testGetRunTimeMultipleContextProviders() {
    $contexts0 = $this->setupContextAndProvider('test_provider', ['test_context0', 'test_context1'], ['test_context0']);
    $contexts1 = $this->setupContextAndProvider('test_provider2', ['test1_context0', 'test1_context1'], ['test1_context0']);

    $lazy_context_repository = new LazyContextRepository($this->container, ['test_provider']);
    $run_time_contexts = $lazy_context_repository->getRunTimeContexts(['@test_provider:test_context0', '@test_provider2:test1_context0']);
    $this->assertEquals(['@test_provider:test_context0' => $contexts0[0], '@test_provider2:test1_context0' => $contexts1[1]], $run_time_contexts);
  }

  /**
   * @covers ::getRunTimeContexts
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessage You must provide the context IDs in the @{service_id}:{context_slot_name} format.
   */
  public function testInvalidContextId() {
    $lazy_context_repository = new LazyContextRepository($this->container, ['test_provider']);
    $lazy_context_repository->getRunTimeContexts(['test_context', '@test_provider:test_context1']);
  }

  /**
   * @covers ::getRunTimeContexts
   */
  public function testGetRunTimeStaticCache() {
    $context0 = new Context(new ContextDefinition('example'));
    $context1 = new Context(new ContextDefinition('example'));

    $context_provider = $this->prophesize('\Drupal\Core\Plugin\Context\ContextProviderInterface');
    $context_provider->getRunTimeContexts(['test_context0', 'test_context1'])
      ->shouldBeCalledTimes(1)
      ->willReturn(['test_context0' => $context0, 'test_context1' => $context1]);
    $context_provider = $context_provider->reveal();
    $this->container->set('test_provider', $context_provider);

    $lazy_context_repository = new LazyContextRepository($this->container, ['test_provider']);
    $lazy_context_repository->getRunTimeContexts(['@test_provider:test_context0', '@test_provider:test_context1']);
    $lazy_context_repository->getRunTimeContexts(['@test_provider:test_context0', '@test_provider:test_context1']);
  }

  /**
   * @covers ::getConfigurationTimeContexts
   */
  public function testGetConfigurationTimeContexts() {
    $contexts0 = $this->setupContextAndProvider('test_provider0', ['test0_context0', 'test0_context1']);
    $contexts1 = $this->setupContextAndProvider('test_provider1', ['test1_context0', 'test1_context1']);

    $lazy_context_repository = new LazyContextRepository($this->container, ['test_provider0', 'test_provider1']);
    $contexts = $lazy_context_repository->getConfigurationTimeContexts();

    $this->assertEquals([
      '@test_provider0:test0_context0' => $contexts0[0],
      '@test_provider0:test0_context1' => $contexts0[1],
      '@test_provider1:test1_context0' => $contexts1[0],
      '@test_provider1:test1_context1' => $contexts1[1],
    ], $contexts);

  }

  /**
   * Sets up contexts and context providers.
   *
   * @param string $service_id
   *   The service ID of the service provider.
   * @param string[] $context_slot_names
   *   An array of context slot names.
   * @param string[] $expected_context_slot_names
   *   The expected context slotes passed to getRunTimeContexts.
   *
   * @return array
   *   An array of set up contexts.
   */
  protected function setupContextAndProvider($service_id, array $context_slot_names, array $expected_context_slot_names = []) {
    $contexts = [];
    for ($i = 0; $i < count($context_slot_names); $i++) {
      $contexts[] = new Context(new ContextDefinition('example'));
    }

    $expected_context_slot_names = $expected_context_slot_names ?: $context_slot_names;

    $context_provider = $this->prophesize('\Drupal\Core\Plugin\Context\ContextProviderInterface');
    $context_provider->getRunTimeContexts($expected_context_slot_names)
      ->willReturn(array_combine($context_slot_names, $contexts));
    $context_provider->getConfigurationTimeContexts()
      ->willReturn(array_combine($context_slot_names, $contexts));
    $context_provider = $context_provider->reveal();
    $this->container->set($service_id, $context_provider);

    return $contexts;
  }

}
