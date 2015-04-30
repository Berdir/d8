<?php

namespace CommerceGuys\Addressing\Tests\Model;

use CommerceGuys\Addressing\Model\Subdivision;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Model\Subdivision
 */
class SubdivisionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Subdivision
     */
    protected $subdivision;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->subdivision = new Subdivision();
    }

    /**
     * @covers ::getParent
     * @covers ::setParent
     * @uses \CommerceGuys\Addressing\Model\Subdivision::__construct
     */
    public function testParent()
    {
        $parent = $this
            ->getMockBuilder('CommerceGuys\Addressing\Model\Subdivision')
            ->getMock();
        $this->subdivision->setParent($parent);
        $this->assertEquals($parent, $this->subdivision->getParent());
    }

    /**
     * @covers ::setChildren
     * @uses \CommerceGuys\Addressing\Model\Subdivision::__construct
     * @expectedException \CommerceGuys\Addressing\Exception\UnexpectedTypeException
     */
    public function testSetInvalidChildren()
    {
        $this->subdivision->setChildren([1, 2]);
    }

    /**
     * @covers ::__construct
     * @covers ::getChildren
     * @covers ::setChildren
     * @covers ::hasChildren
     * @covers ::addChild
     * @covers ::removeChild
     * @covers ::hasChild
     * @uses \CommerceGuys\Addressing\Model\Subdivision::__construct
     * @uses \Doctrine\Common\Collections\ArrayCollection
     */
    public function testChildren()
    {
        $firstChild = $this
            ->getMockBuilder('CommerceGuys\Addressing\Model\Subdivision')
            ->getMock();
        $secondChild = $this
            ->getMockBuilder('CommerceGuys\Addressing\Model\Subdivision')
            ->getMock();
        $empty = new ArrayCollection();
        $children = new ArrayCollection([$firstChild, $secondChild]);

        $this->assertEquals(false, $this->subdivision->hasChildren());
        $this->assertEquals($empty, $this->subdivision->getChildren());
        $this->subdivision->setChildren($children);
        $this->assertEquals($children, $this->subdivision->getChildren());
        $this->assertEquals(true, $this->subdivision->hasChildren());
        $this->subdivision->removeChild($secondChild);
        $this->assertEquals(false, $this->subdivision->hasChild($secondChild));
        $this->assertEquals(true, $this->subdivision->hasChild($firstChild));
        $this->subdivision->addChild($secondChild);
        $this->assertEquals($children, $this->subdivision->getChildren());
    }

    /**
     * @covers ::getCountryCode
     * @covers ::setCountryCode
     * @uses \CommerceGuys\Addressing\Model\Subdivision::__construct
     */
    public function testCountryCode()
    {
        $this->subdivision->setCountryCode('US');
        $this->assertEquals('US', $this->subdivision->getCountryCode());
    }

    /**
     * @covers ::getId
     * @covers ::setId
     * @uses \CommerceGuys\Addressing\Model\Subdivision::__construct
     */
    public function testId()
    {
        $this->subdivision->setId('US-CA');
        $this->assertEquals('US-CA', $this->subdivision->getId());
    }

    /**
     * @covers ::getCode
     * @covers ::setCode
     * @uses \CommerceGuys\Addressing\Model\Subdivision::__construct
     */
    public function testCode()
    {
        $this->subdivision->setCode('CA');
        $this->assertEquals('CA', $this->subdivision->getCode());
    }

    /**
     * @covers ::getName
     * @covers ::setName
     * @uses \CommerceGuys\Addressing\Model\Subdivision::__construct
     */
    public function testName()
    {
        $this->subdivision->setName('California');
        $this->assertEquals('California', $this->subdivision->getName());
    }

    /**
     * @covers ::getPostalCodePattern
     * @covers ::setPostalCodePattern
     * @uses \CommerceGuys\Addressing\Model\Subdivision::__construct
     */
    public function testPostalCodePattern()
    {
        $this->subdivision->setPostalCodePattern('9[0-5]|96[01]');
        $this->assertEquals('9[0-5]|96[01]', $this->subdivision->getPostalCodePattern());
    }

    /**
     * @covers ::getLocale
     * @covers ::setLocale
     * @uses \CommerceGuys\Addressing\Model\Subdivision::__construct
     */
    public function testLocale()
    {
        $this->subdivision->setLocale('en');
        $this->assertEquals('en', $this->subdivision->getLocale());
    }
}
