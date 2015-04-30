<?php

namespace CommerceGuys\Addressing\Tests\Formatter;

use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Provider\DataProvider;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Formatter\DefaultFormatter
 */
class DefaultFormatterTest extends \PHPUnit_Framework_TestCase
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
     * @var DefaultFormatter
     */
    protected $formatter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->dataProvider = new DataProvider();
        $this->formatter = new DefaultFormatter($this->dataProvider);
    }

    /**
     * @covers ::__construct
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::setOptions
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::getDefaultOptions
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     */
    public function testConstructor()
    {
        $this->dataProvider = new DataProvider();
        $formatter = new DefaultFormatter($this->dataProvider);
        $this->assertEquals($this->dataProvider, $this->getObjectAttribute($formatter, 'dataProvider'));
    }

    /**
     * @covers ::getLocale
     * @covers ::setLocale
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::__construct
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::setOptions
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::getDefaultOptions
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     */
    public function testLocale()
    {
        $this->dataProvider = new DataProvider();
        $formatter = new DefaultFormatter($this->dataProvider, 'en');
        $this->assertEquals('en', $formatter->getLocale());
        $formatter->setLocale('fr');
        $this->assertEquals('fr', $formatter->getLocale());
    }

    /**
     * @covers ::setOption
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::__construct
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::setOptions
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::getDefaultOptions
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidOption()
    {
        $this->formatter->setOption('invalid', 'new value');
    }

    /**
     * @covers ::getOptions
     * @covers ::setOptions
     * @covers ::getOption
     * @covers ::setOption
     * @covers ::getDefaultOptions
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::__construct
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::setOptions
     * @uses \CommerceGuys\Addressing\Formatter\DefaultFormatter::getDefaultOptions
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     */
    public function testOptions()
    {
        $this->dataProvider = new DataProvider();
        $formatter = new DefaultFormatter($this->dataProvider, 'en', ['html' => false]);

        $expectedOptions = [
            'html' => false,
            'html_tag' => 'p',
            'html_attributes' => ['translate' => 'no'],
        ];
        $this->assertEquals($expectedOptions, $formatter->getOptions());
        $this->assertEquals('p', $formatter->getOption('html_tag'));
        $formatter->setOption('html_tag', 'div');
        $this->assertEquals('div', $formatter->getOption('html_tag'));
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testElSalvadorAddress()
    {
        $address = new Address();
        $address
            ->setCountryCode('SV')
            ->setAdministrativeArea('Ahuachapán')
            ->setLocality('Ahuachapán')
            ->setAddressLine1('Some Street 12');

        $expectedHtmlLines = [
            '<p translate="no">',
            '<span class="address-line1">Some Street 12</span><br>',
            '<span class="locality">Ahuachapán</span><br>',
            '<span class="administrative-area">Ahuachapán</span><br>',
            '<span class="country">El Salvador</span>',
            '</p>'
        ];
        $htmlAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedHtmlLines, $htmlAddress);

        $expectedTextLines = [
            'Some Street 12',
            'Ahuachapán',
            'Ahuachapán',
            'El Salvador',
        ];
        $this->formatter->setOption('html', false);
        $textAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);

        $address->setPostalCode('CP 2101');
        $expectedHtmlLines = [
            '<p translate="no">',
            '<span class="address-line1">Some Street 12</span><br>',
            '<span class="postal-code">CP 2101</span>-<span class="locality">Ahuachapán</span><br>',
            '<span class="administrative-area">Ahuachapán</span><br>',
            '<span class="country">El Salvador</span>',
            '</p>'
        ];
        $this->formatter->setOption('html', true);
        $htmlAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedHtmlLines, $htmlAddress);

        $expectedTextLines = [
            'Some Street 12',
            'CP 2101-Ahuachapán',
            'Ahuachapán',
            'El Salvador',
        ];
        $this->formatter->setOption('html', false);
        $textAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);
    }

     /**
     * @covers \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testTaiwanAddress()
    {
        // Real addresses in the major-to-minor order would be completely in
        // Traditional Chinese. That's not the case here, for readability.
        $address = new Address();
        $address
            ->setCountryCode('TW')
            ->setAdministrativeArea('TW-TPE')  // Taipei city
            ->setLocality('TW-TPE-e3cc33')  // Da-an district
            ->setAddressLine1('Sec. 3 Hsin-yi Rd.')
            ->setPostalCode('106')
            // Any HTML in the fields is supposed to be removed when formatting
            // for text, and escaped when formatting for html.
            ->setOrganization('Giant <h2>Bike</h2> Store')
            ->setRecipient('Mr. Liu');
        $this->formatter->setLocale('zh-hant');
        // Test adding a new wrapper attribute, and passing a value as an array.
        $options = ['translate' => 'no', 'class' => ['address', 'postal-address']];
        $this->formatter->setOption('html_attributes', $options);

        $expectedHtmlLines = [
            '<p translate="no" class="address postal-address">',
            '<span class="country">台灣</span><br>',
            '<span class="postal-code">106</span><br>',
            '<span class="administrative-area">台北市</span><span class="locality">大安區</span><br>',
            '<span class="address-line1">Sec. 3 Hsin-yi Rd.</span><br>',
            '<span class="organization">Giant &lt;h2&gt;Bike&lt;/h2&gt; Store</span><br>',
            '<span class="recipient">Mr. Liu</span>',
            '</p>'
        ];
        $htmlAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedHtmlLines, $htmlAddress);

        $expectedTextLines = [
            '台灣',
            '106',
            '台北市大安區',
            'Sec. 3 Hsin-yi Rd.',
            'Giant Bike Store',
            'Mr. Liu',
        ];
        $this->formatter->setOption('html', false);
        $textAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);
    }

    /**
     * @covers \CommerceGuys\Addressing\Formatter\DefaultFormatter
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     */
    public function testUnitedStatesIncompleteAddress()
    {
        // Create a US address without a locality.
        $address = new Address();
        $address
            ->setAdministrativeArea('US-CA')
            ->setCountryCode('US')
            ->setAddressLine1('1098 Alta Ave')
            ->setPostalCode('94043');

        $expectedHtmlLines = [
            '<p translate="no">',
            '<span class="address-line1">1098 Alta Ave</span><br>',
            '<span class="administrative-area">CA</span> <span class="postal-code">94043</span><br>',
            '<span class="country">United States</span>',
            '</p>'
        ];
        $htmlAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedHtmlLines, $htmlAddress);

        $expectedTextLines = [
            '1098 Alta Ave',
            'CA 94043',
            'United States'
        ];
        $this->formatter->setOption('html', false);
        $textAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);

        // Now add the locality, but remove the administrative area.
        $address
            ->setLocality('Mountain View')
            ->setAdministrativeArea('');

        $expectedHtmlLines = [
            '<p translate="no">',
            '<span class="address-line1">1098 Alta Ave</span><br>',
            '<span class="locality">Mountain View</span>, <span class="postal-code">94043</span><br>',
            '<span class="country">United States</span>',
            '</p>'
        ];
        $this->formatter->setOption('html', true);
        $htmlAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedHtmlLines, $htmlAddress);

        $expectedTextLines = [
            '1098 Alta Ave',
            'Mountain View, 94043',
            'United States'
        ];
        $this->formatter->setOption('html', false);
        $textAddress = $this->formatter->format($address);
        $this->assertFormattedAddress($expectedTextLines, $textAddress);
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
