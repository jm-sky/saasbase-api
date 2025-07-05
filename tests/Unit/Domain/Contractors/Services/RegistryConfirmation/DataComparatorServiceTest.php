<?php

namespace Tests\Unit\Domain\Contractors\Services\RegistryConfirmation;

use App\Domain\Common\DTOs\AddressDTO;
use App\Domain\Common\DTOs\BankAccountDTO;
use App\Domain\Common\Enums\AddressType;
use App\Domain\Contractors\Services\RegistryConfirmation\DataComparatorService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DataComparatorService::class)]
class DataComparatorServiceTest extends TestCase
{
    private DataComparatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DataComparatorService();
    }

    public function testCompareNames(string $contractorName, string $registryName, bool $expectedResult): void
    {
        $result = $this->service->compareNames($contractorName, $registryName);

        $this->assertSame(
            $expectedResult,
            $result,
            "Failed comparing '{$contractorName}' with '{$registryName}'"
        );
    }

    public static function nameComparisonProvider(): array
    {
        return [
            // Exact matches
            ['ABC Company', 'ABC Company', true],
            ['XYZ Ltd', 'XYZ Ltd', true],

            // Case insensitive
            ['abc company', 'ABC COMPANY', true],
            ['Test Ltd', 'test ltd', true],

            // Special characters normalization
            ['Test & Co.', 'Test Co', true],
            ['Company "Name"', 'Company Name', true],
            ['ABC-Company', 'ABC Company', true],
            ['Test\'s Company', 'Tests Company', true],

            // Minor typos (within distance threshold)
            ['ABC Company', 'ABC Compan', true],  // 1 char difference
            ['Test Ltd', 'Test Ldt', true],       // 1 char difference
            ['Company Name', 'Company Naam', true], // 1 char difference

            // Major differences (beyond threshold)
            ['ABC Company', 'XYZ Company', false],
            ['Test Ltd', 'Different Company', false],
            ['Short', 'Very Long Company Name', false],

            // Edge cases
            ['', 'Test', false],
            ['Test', '', false],
            ['A', 'B', true], // Single char difference within threshold
        ];
    }

    public function testCompareVatIds(?string $contractorVatId, ?string $registryVatId, bool $expectedResult): void
    {
        $result = $this->service->compareVatIds($contractorVatId, $registryVatId);

        $this->assertSame($expectedResult, $result);
    }

    public static function vatIdComparisonProvider(): array
    {
        return [
            // Exact matches
            ['1234567890', '1234567890', true],
            ['PL1234567890', 'PL1234567890', true],

            // Country prefix handling
            ['PL1234567890', '1234567890', true],
            ['1234567890', 'PL1234567890', true],
            ['PL1234567890', 'pl1234567890', true],

            // Special characters
            ['123-456-789', '123456789', true],
            ['123 456 789', '123456789', true],

            // Null values
            [null, '1234567890', false],
            ['1234567890', null, false],
            [null, null, false],

            // Different VAT IDs
            ['1234567890', '0987654321', false],
            ['PL1234567890', 'DE1234567890', false],
        ];
    }

    public function testCompareRegons(?string $contractorRegon, ?string $registryRegon, bool $expectedResult): void
    {
        $result = $this->service->compareRegons($contractorRegon, $registryRegon);

        $this->assertSame($expectedResult, $result);
    }

    public static function regonComparisonProvider(): array
    {
        return [
            // Exact matches
            ['123456789', '123456789', true],
            ['12345678901234', '12345678901234', true],

            // Null values
            [null, '123456789', false],
            ['123456789', null, false],
            [null, null, false],

            // Different REGONs
            ['123456789', '987654321', false],
            ['12345678901234', '43210987654321', false],
        ];
    }

    public function testCompareAddressesSuccessfulMatch(): void
    {
        $contractorAddress = new AddressDTO(
            country: 'PL',
            city: 'Warsaw',
            street: 'Test Street 123',
            postalCode: '00-001',
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true
        );

        $registryAddress = new AddressDTO(
            country: 'PL',
            city: 'Warsaw',
            street: 'Test Street 123',
            postalCode: '00-001',
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true
        );

        $result = $this->service->compareAddresses($contractorAddress, $registryAddress);

        $this->assertTrue($result);
    }

    public function testCompareAddressesFuzzyStreetMatch(): void
    {
        $contractorAddress = new AddressDTO(
            country: 'PL',
            city: 'Warsaw',
            street: 'Test Street 123',
            postalCode: '00-001',
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true
        );

        $registryAddress = new AddressDTO(
            country: 'PL',
            city: 'Warsaw',
            street: 'Test Str. 123',  // Minor difference
            postalCode: '00-001',
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true
        );

        $result = $this->service->compareAddresses($contractorAddress, $registryAddress);

        $this->assertTrue($result);
    }

    public function testCompareAddressesPostalCodeNormalization(): void
    {
        $contractorAddress = new AddressDTO(
            country: 'PL',
            city: 'Warsaw',
            street: 'Test Street 123',
            postalCode: '00-001',
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true
        );

        $registryAddress = new AddressDTO(
            country: 'PL',
            city: 'Warsaw',
            street: 'Test Street 123',
            postalCode: '00001',  // No dash
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true
        );

        $result = $this->service->compareAddresses($contractorAddress, $registryAddress);

        $this->assertTrue($result);
    }

    public function testCompareAddressesMissingRequiredFields(): void
    {
        $contractorAddress = new AddressDTO(
            country: 'PL',
            city: 'Warsaw',
            street: '',  // Empty required field
            postalCode: '00-001',
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true
        );

        $registryAddress = new AddressDTO(
            country: 'PL',
            city: 'Warsaw',
            street: 'Test Street 123',
            postalCode: '00-001',
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true
        );

        $result = $this->service->compareAddresses($contractorAddress, $registryAddress);

        $this->assertFalse($result);
    }

    public function testCompareAddressesDifferentCountries(): void
    {
        $contractorAddress = new AddressDTO(
            country: 'PL',
            city: 'Warsaw',
            street: 'Test Street 123',
            postalCode: '00-001',
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true
        );

        $registryAddress = new AddressDTO(
            country: 'DE',  // Different country
            city: 'Warsaw',
            street: 'Test Street 123',
            postalCode: '00-001',
            type: AddressType::REGISTERED_OFFICE,
            isDefault: true
        );

        $result = $this->service->compareAddresses($contractorAddress, $registryAddress);

        $this->assertFalse($result);
    }

    public function testCompareBankAccountsSuccessfulMatch(): void
    {
        $contractorAccount = new BankAccountDTO(
            iban: 'PL60102010260000150202000000',
            bankName: 'Test Bank',
            swift: 'TESTPL22',
            currency: 'PLN',
            isDefault: true
        );

        $registryAccount = new BankAccountDTO(
            iban: 'PL60102010260000150202000000',
            bankName: 'Test Bank',
            swift: 'TESTPL22',
            currency: 'PLN',
            isDefault: true
        );

        $result = $this->service->compareBankAccounts($contractorAccount, $registryAccount);

        $this->assertTrue($result);
    }

    public function testCompareBankAccountsIbanNormalization(): void
    {
        $contractorAccount = new BankAccountDTO(
            iban: 'PL60 1020 1026 0000 1502 0200 0000',  // With spaces
            bankName: 'Test Bank',
            swift: 'TESTPL22',
            currency: 'PLN',
            isDefault: true
        );

        $registryAccount = new BankAccountDTO(
            iban: 'PL60102010260000150202000000',  // Without spaces
            bankName: 'Test Bank',
            swift: 'TESTPL22',
            currency: 'PLN',
            isDefault: true
        );

        $result = $this->service->compareBankAccounts($contractorAccount, $registryAccount);

        $this->assertTrue($result);
    }

    public function testCompareBankAccountsMissingIban(): void
    {
        $contractorAccount = new BankAccountDTO(
            iban: '',  // Empty IBAN
            bankName: 'Test Bank',
            swift: 'TESTPL22',
            currency: 'PLN',
            isDefault: true
        );

        $registryAccount = new BankAccountDTO(
            iban: 'PL60102010260000150202000000',
            bankName: 'Test Bank',
            swift: 'TESTPL22',
            currency: 'PLN',
            isDefault: true
        );

        $result = $this->service->compareBankAccounts($contractorAccount, $registryAccount);

        $this->assertFalse($result);
    }

    public function testCompareBankAccountsDifferentIbans(): void
    {
        $contractorAccount = new BankAccountDTO(
            iban: 'PL60102010260000150202000000',
            bankName: 'Test Bank',
            swift: 'TESTPL22',
            currency: 'PLN',
            isDefault: true
        );

        $registryAccount = new BankAccountDTO(
            iban: 'PL61102010260000150202000000',  // Different IBAN
            bankName: 'Test Bank',
            swift: 'TESTPL22',
            currency: 'PLN',
            isDefault: true
        );

        $result = $this->service->compareBankAccounts($contractorAccount, $registryAccount);

        $this->assertFalse($result);
    }
}
