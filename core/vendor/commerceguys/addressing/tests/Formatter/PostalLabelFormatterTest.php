<?php

namespace CommerceGuys\Addressing\Tests\Formatter;

use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Formatter\PostalLabelFormatter;
use CommerceGuys\Addressing\Provider\DataProvider;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
 */
class PostalLabelFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The data provider.
     *
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * The formatter.
     *
     * @var PostalLabelFormatter
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->dataProvider = new DataProvider();
        $this->formatter = new PostalLabelFormatter($this->dataProvider);
    }

    /**
     * @covers ::format
     * @uses \CommerceGuys\Addressing\Formatter\PostalLabelFormatter::__construct
     * @uses \CommerceGuys\Addressing\Formatter\PostalLabelFormatter::getDefaultOptions
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @expectedException \RuntimeException
     */
    public function testMissingOriginCountryCode()
    {
        $address = new Address();
        $this->formatter->format($address);
    }

    /**
     * @covers ::getOriginCountryCode
     * @covers ::setOriginCountryCode
     * @uses \CommerceGuys\Addressing\Formatter\PostalLabelFormatter::__construct
     * @uses \CommerceGuys\Addressing\Formatter\PostalLabelFormatter::getDefaultOptions
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     */
    public function testOriginCountryCode()
    {
        $this->formatter->setOriginCountryCode('FR');
        $this->assertEquals('FR', $this->formatter->getOriginCountryCode('FR'));
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testEmptyAddress()
    {
        $expectedLines = [];
        $address = new Address();
        $address->setCountryCode('US');

        $this->formatter->setOriginCountryCode('US');
        $formattedAddress = $this->formatter->format($address, 'US');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testUnitedStatesAddress()
    {
        $address = new Address();
        $address
            ->setCountryCode('US')
            ->setAdministrativeArea('US-CA')
            ->setLocality('Mt View')
            ->setAddressLine1('1098 Alta Ave')
            ->setPostalCode('94043');

        // Test a US address formatted for sending from the US.
        $expectedLines = [
            '1098 Alta Ave',
            'MT VIEW, CA 94043',
        ];
        $this->formatter->setOriginCountryCode('US');
        $formattedAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);

        // Test a US address formatted for sending from France.
        $expectedLines = [
            '1098 Alta Ave',
            'MT VIEW, CA 94043',
            'ÉTATS-UNIS - UNITED STATES',
        ];
        $this->formatter->setOriginCountryCode('FR');
        $this->formatter->setLocale('fr');
        $formattedAddress = $this->formatter->format($address, 'FR', 'fr');
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\PostalLabelFormatter
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testAddressLeadingPostPrefix()
    {
        $address = new Address();
        $address
            ->setCountryCode('CH')
            ->setLocality('Herrliberg')
            ->setPostalCode('8047');

        // Domestic mail shouldn't have the postal code prefix added.
        $expectedLines = [
            '8047 Herrliberg',
        ];
        $this->formatter->setOriginCountryCode('CH');
        $formattedAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);

        // International mail should have the postal code prefix added.
        $expectedLines = [
            'CH-8047 Herrliberg',
            'SWITZERLAND',
        ];
        $this->formatter->setOriginCountryCode('FR');
        $formattedAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedLines, $formattedAddress);
    }

    /**
     * Asserts that the formatted address is valid.
     *
     * @param array  $expectedLines
     * @param string $formattedAddress
     */
    protected function assertFormattedAddress(array $expectedLines, $formattedAddress)
    {
        $expectedLines = implode("\n", $expectedLines);
        $this->assertEquals($expectedLines, $formattedAddress);
    }
}
