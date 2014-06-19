<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Routing\CachedUrlGeneratorTest.
 */

namespace Drupal\Tests\Core\Routing;

use Drupal\Core\Routing\CachedUrlGenerator;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the cache url generator.
 */
class CachedUrlGeneratorTest extends UnitTestCase {

  /**
   * The wrapped url generator.
   *
   * @var \Drupal\Core\Routing\PathBasedGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cache;

  /**
   * Language manager for retrieving the URL language type.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $languageManager;

  /**
   * The actual tested cached url generator.
   *
   * @var \Drupal\Core\Routing\CachedUrlGenerator
   */
  protected $cachedUrlGenerator;

  public static function getInfo() {
    return array(
      'name' => 'Cached UrlGenerator',
      'description' => 'Confirm that the cached UrlGenerator is functioning properly.',
      'group' => 'Routing',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->urlGenerator = $this->getMock('Drupal\Core\Routing\UrlGeneratorInterface');
    $this->cache = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
    $this->languageManager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');

    $this->cachedUrlGenerator = new CachedUrlGenerator($this->urlGenerator, $this->cache, $this->languageManager);
  }

  /**
   * Tests the generate method.
   *
   * @see \Drupal\Core\Routing\CachedUrlGenerator::generate()
   */
  public function testGenerate() {
    $this->urlGenerator->expects($this->once())
      ->method('generateFromRoute')
      ->with('test_route')
      ->will($this->returnValue('test-route-1'));
    $this->assertEquals('test-route-1', $this->cachedUrlGenerator->generateFromRoute('test_route'));
    $this->assertEquals('test-route-1', $this->cachedUrlGenerator->generateFromRoute('test_route'));
  }

  /**
   * Tests the generate method with the same route name but different parameters.
   *
   * @see \Drupal\Core\Routing\CachedUrlGenerator::generate()
   */
  public function testGenerateWithDifferentParameters() {
    $this->urlGenerator->expects($this->exactly(2))
      ->method('generateFromRoute')
      ->will($this->returnValueMap(array(
        array('test_route', array('key' => 'value1'), array(), 'test-route-1/value1'),
        array('test_route', array('key' => 'value2'), array(), 'test-route-1/value2'),
      )));
    $this->assertEquals('test-route-1/value1', $this->cachedUrlGenerator->generate('test_route', array('key' => 'value1')));
    $this->assertEquals('test-route-1/value1', $this->cachedUrlGenerator->generate('test_route', array('key' => 'value1')));
    $this->assertEquals('test-route-1/value2', $this->cachedUrlGenerator->generate('test_route', array('key' => 'value2')));
    $this->assertEquals('test-route-1/value2', $this->cachedUrlGenerator->generate('test_route', array('key' => 'value2')));
  }

  /**
   * Tests the generateFromPath method.
   *
   * @see \Drupal\Core\Routing\CachedUrlGenerator::generateFromPath()
   */
  public function testGenerateFromPath() {
    $this->urlGenerator->expects($this->once())
      ->method('generateFromPath')
      ->with('test-route-1')
      ->will($this->returnValue('test-route-1'));
    $this->assertEquals('test-route-1', $this->cachedUrlGenerator->generateFromPath('test-route-1'));
    $this->assertEquals('test-route-1', $this->cachedUrlGenerator->generateFromPath('test-route-1'));
  }

  /**
   * Tests the generate method with the same path but different options
   *
   * @see \Drupal\Core\Routing\CachedUrlGenerator::generateFromPath()
   */
  public function testGenerateFromPathWithDifferentParameters() {
    $this->urlGenerator->expects($this->exactly(2))
      ->method('generateFromPath')
      ->will($this->returnValueMap(array(
        array('test-route-1', array('absolute' => TRUE), 'http://localhost/test-route-1'),
        array('test-route-1', array('absolute' => FALSE), 'test-route-1'),
      )));
    $this->assertEquals('http://localhost/test-route-1', $this->cachedUrlGenerator->generateFromPath('test-route-1', array('absolute' => TRUE)));
    $this->assertEquals('http://localhost/test-route-1', $this->cachedUrlGenerator->generateFromPath('test-route-1', array('absolute' => TRUE)));
    $this->assertEquals('test-route-1', $this->cachedUrlGenerator->generateFromPath('test-route-1', array('absolute' => FALSE)));
    $this->assertEquals('test-route-1', $this->cachedUrlGenerator->generateFromPath('test-route-1', array('absolute' => FALSE)));
  }


  /**
   * Tests the generateFromRoute method.
   *
   * @see \Drupal\Core\Routing\CachedUrlGenerator::generateFromRoute()
   */
  public function testGenerateFromRoute() {
    $this->urlGenerator->expects($this->once())
      ->method('generateFromRoute')
      ->with('test_route')
      ->will($this->returnValue('test-route-1'));
    $this->assertEquals('test-route-1', $this->cachedUrlGenerator->generateFromRoute('test_route'));
    $this->assertEquals('test-route-1', $this->cachedUrlGenerator->generateFromRoute('test_route'));
  }

  /**
   * Tests the generateFromRoute method with the same path, different options.
   *
   * @see \Drupal\Core\Routing\CachedUrlGenerator::generateFromRoute()
   */
  public function testGenerateFromRouteWithDifferentParameters() {
    $this->urlGenerator->expects($this->exactly(4))
      ->method('generateFromRoute')
      ->will($this->returnValueMap(array(
        array('test_route', array('key' => 'value1'), array(), 'test-route-1/value1'),
        array('test_route', array('key' => 'value1'), array('absolute' => TRUE), 'http://localhost/test-route-1/value1'),
        array('test_route', array('key' => 'value2'), array(), 'test-route-1/value2'),
        array('test_route', array('key' => 'value2'), array('absolute' => TRUE), 'http://localhost/test-route-1/value2'),
      )));
    $this->assertEquals('test-route-1/value1', $this->cachedUrlGenerator->generateFromRoute('test_route', array('key' => 'value1')));
    $this->assertEquals('test-route-1/value1', $this->cachedUrlGenerator->generateFromRoute('test_route', array('key' => 'value1')));
    $this->assertEquals('http://localhost/test-route-1/value1', $this->cachedUrlGenerator->generateFromRoute('test_route', array('key' => 'value1'), array('absolute' => TRUE)));
    $this->assertEquals('http://localhost/test-route-1/value1', $this->cachedUrlGenerator->generateFromRoute('test_route', array('key' => 'value1'), array('absolute' => TRUE)));
    $this->assertEquals('test-route-1/value2', $this->cachedUrlGenerator->generateFromRoute('test_route', array('key' => 'value2')));
    $this->assertEquals('test-route-1/value2', $this->cachedUrlGenerator->generateFromRoute('test_route', array('key' => 'value2')));
    $this->assertEquals('http://localhost/test-route-1/value2', $this->cachedUrlGenerator->generateFromRoute('test_route', array('key' => 'value2'), array('absolute' => TRUE)));
    $this->assertEquals('http://localhost/test-route-1/value2', $this->cachedUrlGenerator->generateFromRoute('test_route', array('key' => 'value2'), array('absolute' => TRUE)));
  }

}

