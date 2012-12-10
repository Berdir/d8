<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Routing\Tests\Loader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\Resource\FileResource;

class YamlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony\Component\Config\FileLocator')) {
            $this->markTestSkipped('The "Config" component is not available');
        }

        if (!class_exists('Symfony\Component\Yaml\Yaml')) {
            $this->markTestSkipped('The "Yaml" component is not available');
        }
    }

    /**
     * @covers Symfony\Component\Routing\Loader\YamlFileLoader::supports
     */
    public function testSupports()
    {
        $loader = new YamlFileLoader($this->getMock('Symfony\Component\Config\FileLocator'));

        $this->assertTrue($loader->supports('foo.yml'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports('foo.yml', 'yaml'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports('foo.yml', 'foo'), '->supports() checks the resource type if specified');
    }

    public function testLoadDoesNothingIfEmpty()
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        $collection = $loader->load('empty.yml');

        $this->assertEquals(array(), $collection->all());
        $this->assertEquals(array(new FileResource(realpath(__DIR__.'/../Fixtures/empty.yml'))), $collection->getResources());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadThrowsExceptionIfNotAnArray()
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        $loader->load('nonvalid.yml');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadThrowsExceptionIfArrayHasUnsupportedKeys()
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        $loader->load('nonvalidkeys.yml');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadThrowsExceptionWhenIncomplete()
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        $loader->load('incomplete.yml');
    }

    public function testLoadSpecialRouteName()
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        $routeCollection = $loader->load('special_route_name.yml');
        $route = $routeCollection->get('#$péß^a|');

        $this->assertInstanceOf('Symfony\Component\Routing\Route', $route);
        $this->assertSame('/true', $route->getPattern());
    }

    public function testLoadWithPattern()
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        $routeCollection = $loader->load('validpattern.yml');
        $routes = $routeCollection->all();

        $this->assertEquals(1, count($routes), 'One route is loaded');
        $this->assertContainsOnly('Symfony\Component\Routing\Route', $routes);
        $route = $routes['blog_show'];
        $this->assertEquals('/blog/{slug}', $route->getPattern());
        $this->assertEquals('MyBlogBundle:Blog:show', $route->getDefault('_controller'));
        $this->assertEquals('GET', $route->getRequirement('_method'));
        $this->assertEquals('\w+', $route->getRequirement('locale'));
        $this->assertEquals('{locale}.example.com', $route->getHostnamePattern());
        $this->assertEquals('RouteCompiler', $route->getOption('compiler_class'));
    }

    public function testLoadWithResource()
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        $routeCollection = $loader->load('validresource.yml');
        $routes = $routeCollection->all();

        $this->assertEquals(1, count($routes), 'One route is loaded');
        $this->assertContainsOnly('Symfony\Component\Routing\Route', $routes);
        $this->assertEquals('/{foo}/blog/{slug}', $routes['blog_show']->getPattern());
        $this->assertEquals('MyBlogBundle:Blog:show', $routes['blog_show']->getDefault('_controller'));
        $this->assertEquals('123', $routes['blog_show']->getDefault('foo'));
        $this->assertEquals('\d+', $routes['blog_show']->getRequirement('foo'));
        $this->assertEquals('bar', $routes['blog_show']->getOption('foo'));
        $this->assertEquals('{locale}.example.com', $routes['blog_show']->getHostnamePattern());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseRouteThrowsExceptionWithMissingPattern()
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../Fixtures')));
        $loader->load('incomplete.yml');
    }
}
