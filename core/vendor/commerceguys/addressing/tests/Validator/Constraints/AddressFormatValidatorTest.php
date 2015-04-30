<?php

namespace CommerceGuys\Addressing\Tests\Validator\Constraints;

use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Provider\DataProvider;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormat as AddressFormatConstraint;
use CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/**
 * @coversDefaultClass \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
 */
class AddressFormatValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * @var Address;
     */
    protected $address;

    /**
     * @var AddressFormatConstraint
     */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->address = new Address();
        $this->constraint = new AddressFormatConstraint();

        // The following code is copied from the parent setUp(), which isn't
        // called to avoid the call to \Locale, which introduces a dependency
        // on the intl extension (or symfony/intl).
        $this->group = 'MyGroup';
        $this->metadata = null;
        $this->object = null;
        $this->value = 'InvalidValue';
        $this->root = 'root';
        $this->propertyPath = '';
        $this->context = $this->createContext();
        $this->validator = $this->createValidator();
        $this->validator->initialize($this->context);
    }

    protected function getApiVersion()
    {
        // The equivalent of Validation::API_VERSION_2_5_BC, needed to trigger
        // BC mode in symfony/validator >= 2.5.
        return 3;
    }

    protected function createValidator()
    {
        return new AddressFormatValidator();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testInvalidValueType()
    {
        $this->validator->validate(new \stdClass(), $this->constraint);
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Model\Address
     */
    public function testEmptyIsValid()
    {
        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testUnitedStatesOK()
    {
        $this->address
          ->setCountryCode('US')
          ->setAdministrativeArea('US-CA')
          ->setLocality('Mountain View')
          ->setAddressLine1('1234 Somewhere')
          ->setPostalCode('94025');

        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testUnitedStatesNotOK()
    {
        $this->address
          ->setCountryCode('US')
          ->setAdministrativeArea('US-CA')
          ->setPostalCode('90961');

        $this->validator->validate($this->address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[addressLine1]')
            ->setInvalidValue(null)
            ->buildNextViolation($this->constraint->notBlankMessage)
            ->atPath('[locality]')
            ->setInvalidValue(null)
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testChinaOK()
    {
        $this->address
            ->setCountryCode('CN')
            ->setAdministrativeArea('CN-11')
            ->setLocality('CN-11-30524e')
            ->setAddressLine1('Yitiao Lu')
            ->setPostalCode('123456');

        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testGermanAddress()
    {
        $this->address
            ->setCountryCode('DE')
            ->setLocality('Berlin')
            ->setAddressLine1('Huttenstr. 50')
            ->setPostalCode('10553')
            ->setOrganization('BMW AG Niederkassung Berlin')
            ->setRecipient('Herr Diefendorf');

        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();

        // Testing with a empty city should fail.
        $this->address->setLocality(null);

        $this->validator->validate($this->address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[locality]')
            ->setInvalidValue(null)
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testIrishAddress()
    {
        $this->address
            ->setCountryCode('IE')
            ->setLocality('Dublin')
            ->setAdministrativeArea('IE-D')
            ->setAddressLine1('7424 118 Avenue NW')
            ->setRecipient("Conan O'Brien");

        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();

        // Test the same address but leave the county empty. This address should be valid
        // since county is not required.
        $this->address->setAdministrativeArea(null);

        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testChinaPostalCodeBadFormat()
    {
        $this->address
            ->setCountryCode('CN')
            ->setAdministrativeArea('CN-11')
            ->setLocality('CN-11-30524e')
            ->setPostalCode('InvalidValue');

        $this->validator->validate($this->address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[addressLine1]')
            ->setInvalidValue(null)
            ->buildNextViolation($this->constraint->invalidMessage)
            ->atPath('[postalCode]')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testEmptyPostalCodeReportedAsGoodFormat()
    {
        $this->address
            ->setCountryCode('CL')
            ->setAddressLine1('GUSTAVO LE PAIGE ST #159')
            ->setAdministrativeArea('CL-AN')
            ->setLocality('CL-AN-2bb729')
            ->setPostalCode('');

        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();

        // Now check for US addresses, which require a postal code. The following
        // address's postal code is wrong because it is missing a required field, not
        // because it doesn't match the expected postal code pattern.
        $this->address
          ->setCountryCode('US')
          ->setAdministrativeArea('US-CA')
          ->setLocality('California');

        $this->validator->validate($this->address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[postalCode]')
            ->setInvalidValue(null)
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testChinaTaiwanOk()
    {
        $this->address
            ->setCountryCode('CN')
            ->setAdministrativeArea('CN-71')
            ->setLocality('CN-71-dfbf10')
            ->setDependentLocality('CN-71-dfbf10-42fb60')
            ->setAddressLine1('12345 Yitiao Lu"')
            ->setPostalCode('407');

        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     * @uses \CommerceGuys\Addressing\Collection\LazySubdivisionCollection
     */
    public function testChinaTaiwanUnknownDistrict()
    {
        $this->address
          ->setCountryCode('CN')
          ->setAdministrativeArea('CN-71')
          ->setLocality('CN-71-dfbf10')
          ->setDependentLocality('InvalidValue')
          ->setAddressLine1('12345 Yitiao Lu"')
          ->setPostalCode('407');

        $this->validator->validate($this->address, $this->constraint);
        $this->buildViolation($this->constraint->invalidMessage)
            ->atPath('[dependentLocality]')
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testStreetVerification()
    {
        $this->address
          ->setCountryCode('US')
          ->setAdministrativeArea('US-CA')
          ->setLocality('Mountain View')
          ->setPostalCode('94025');

        $violations = $this->validator->validate($this->address, $this->constraint);
        $this->buildViolation($this->constraint->notBlankMessage)
            ->atPath('[addressLine1]')
            ->setInvalidValue(null)
            ->assertRaised();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testSubdivisionPostcodePattern()
    {
        // The correct postal code patters is used for a subdivision.
        $this->address
          ->setCountryCode('US')
          ->setAdministrativeArea('US-CA')
          ->setAddressLine1('1234 Somewhere')
          ->setLocality('Mountain View')
          ->setPostalCode('94025');

        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();

        // When a invalid postal code is used for a subdivision it should fail.
        $this->address->setPostalCode('InvalidValue');

        $this->validator->validate($this->address, $this->constraint);
        $this->buildViolation($this->constraint->invalidMessage)
            ->atPath('[postalCode]')
            ->assertRaised();
    }

    /**
     * @covers CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testJapan()
    {
        $this->address
            ->setCountryCode('JP')
            ->setAdministrativeArea('JP-26')
            ->setLocality('Shigeru Miyamoto')
            ->setAddressLine1('11-1 Kamitoba-hokotate-cho')
            ->setPostalCode('601-8501');

        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testCanadaMixedCasePostcode()
    {
        $this->address
            ->setCountryCode('CA')
            ->setRecipient('Joe Bloggs')
            ->setAddressLine1('11 East St')
            ->setLocality('Montreal')
            ->setAdministrativeArea('CA-QC')
            ->setPostalCode('H2b 2y5');

        $this->validator->validate($this->address, $this->constraint);
        $this->assertNoViolation();
    }

    /**
     * @covers \CommerceGuys\Addressing\Validator\Constraints\AddressFormatValidator
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     * @uses \CommerceGuys\Addressing\Repository\DefinitionTranslatorTrait
     * @uses \CommerceGuys\Addressing\Model\Address
     * @uses \CommerceGuys\Addressing\Model\AddressFormat
     * @uses \CommerceGuys\Addressing\Model\FormatStringTrait
     * @uses \CommerceGuys\Addressing\Model\Subdivision
     */
    public function testCanadaUnusedFields()
    {
        $this->address
          ->setCountryCode('CA')
          ->setSortingCode('InvalidValue')
          ->setRecipient('Joe Bloggs')
          ->setAddressLine1('11 East St')
          ->setLocality('Montreal')
          ->setAdministrativeArea('CA-QC')
          ->setPostalCode('H2b 2y5');

        $this->validator->validate($this->address, $this->constraint);
        $this->buildViolation($this->constraint->blankMessage)
            ->atPath('[sortingCode]')
            ->assertRaised();
    }

    /**
     * @covers ::getDataProvider
     * @covers ::setDataProvider
     * @uses \CommerceGuys\Addressing\Provider\DataProvider
     * @uses \CommerceGuys\Addressing\Repository\AddressFormatRepository
     * @uses \CommerceGuys\Addressing\Repository\SubdivisionRepository
     */
    public function testDataProvider()
    {
        $addressFormatValidator = new AddressFormatValidator();
        $this->assertInstanceOf('CommerceGuys\Addressing\Provider\DataProvider', $addressFormatValidator->getDataProvider());

        // Replace the data provider with a mock.
        $dataProvider = $this
            ->getMockBuilder('CommerceGuys\Addressing\Provider\DataProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $addressFormatValidator->setDataProvider($dataProvider);
        $this->assertEquals($dataProvider, $addressFormatValidator->getDataProvider());
    }
}
