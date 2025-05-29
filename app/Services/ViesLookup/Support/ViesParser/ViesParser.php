<?php

declare(strict_types=1);

namespace App\Services\ViesLookup\Support\ViesParser;

use App\Services\ViesLookup\Support\ViesParser\DTO\ViesAddress;
use Illuminate\Support\Str;

/**
 * @see https://github.com/sunkaflek/vies-parser
 *
 * @note This is a fork of the original ViesParser class, with the following changes:
 *  - camelized method names
 */
class ViesParser
{
    public int $newlines;

    public function __construct(
        public string $vatNumber,
        public string $address,
        public ?string $countryCode = null,
        public array $configFlags = [],
    ) {
        $this->vatNumber   = trim($vatNumber);
        $this->address     = trim($address);
        $this->countryCode = $this->countryCode ?? Str::startsWith($vatNumber, $countryCode) ? $countryCode : substr($vatNumber, 0, 2);
        $this->newlines    = substr_count($address, "\n");
    }

    // Returns currently supported countries. Not all countries return all data, see RO for example
    public function getSupportedCountries(): array
    {
        return ['SK', 'NL', 'BE', 'FR', 'PT', 'IT', 'FI', 'RO', 'SI', 'AT', 'PL', 'HR', 'EL', 'DK', 'EE', 'CZ'];
    }

    public function getParsedAddress(): ?ViesAddress
    {
        /*
        Only attempt parsing for countries tested, the rest returns false

        -DE does not return address on VIES at all
        -IE has pretty much unparsable addresses in VIES - split by commas, in different orders, without zip codes, often without street number etc
        -ES VIES does not return address unless you tell it what it is
        -RO does not have ZIP codes in VIES data, but we parse the rest. ZIP will return false - needs to be input by customer manualy
        -EL additionaly gets transliterated to English characters (resulting in Greeklish - if not excluded by config flags)

        */
        if (!in_array($this->countryCode, $this->getSupportedCountries())) {
            return null;
        }

        if (1 == $this->newlines && in_array($this->countryCode, ['NL', 'BE', 'FR', 'FI', 'AT', 'PL', 'DK'])) { // Countries in expected format
            return $this->parseEuropeanGroup1();
        }

        // Slovenia has everything on one line, split by comma, but seems fairly regular
        if (0 == $this->newlines && in_array($this->countryCode, ['SI', 'HR'])) {
            return $this->parseEuropeanGroup2();
        }

        if (0 == $this->newlines && in_array($this->countryCode, ['EL'])) {
            return $this->parseGreekGroup();
        }

        // Romania new format
        if (0 == $this->newlines && in_array($this->countryCode, ['RO']) && str_contains($this->address, ',') && str_contains($this->address, 'SECTOR') && str_contains($this->address, 'STR')) {
            return $this->parseRomaniaGroup1();
        }

        // Romania does not have ZIP codes in VIES data
        if (1 == $this->newlines && in_array($this->countryCode, ['RO'])) {
            return $this->parseRomaniaGroup2();
        }

        // Romania does not have ZIP codes in VIES data
        // With 3 lines, it has apartement in the last line - we put it on the start of street line
        if (2 == $this->newlines && in_array($this->countryCode, ['RO'])) {
            return $this->parseRomaniaGroup3();
        }

        if (1 == $this->newlines && in_array($this->countryCode, ['IT'])) {
            return $this->parseItalianGroup();
        }

        if (2 == $this->newlines && in_array($this->countryCode, ['PT'])) {
            return $this->parsePortugueseGroup();
        }

        // in these cases the first line is "name of the place", not exactly street, but for ordering something to this address you put in in the street line
        if (2 == $this->newlines && in_array($this->countryCode, ['FR'])) {
            $address_split    = explode("\n", $this->address);
            $street           = $address_split[0] . ', ' . $address_split[1];
            list($zip, $city) = explode(' ', $address_split[2], 2);

            return new ViesAddress(
                address: $this->address,
                street: trim($street),
                zip: trim($zip),
                city: trim($city),
                countryCode: trim($this->countryCode)
            );
        }

        if (2 == $this->newlines && in_array($this->countryCode, ['SK'])) { // Vetsina SK address
            $address_split    = explode("\n", $this->address);
            $street           = $address_split[0];
            list($zip, $city) = explode(' ', $address_split[1], 2);

            if (in_array('sk_delete_mc', $this->configFlags)) {
                $city = str_replace('mestská časť ', '', $city);
                $city = str_replace('m. č. ', '', $city);
            }

            return new ViesAddress(
                address: $this->address,
                street: trim($street),
                zip: trim($zip),
                city: trim($city),
                countryCode: trim($this->countryCode)
            );
        }

        if (1 == $this->newlines && in_array($this->countryCode, ['SK'])) { // vetsinou ma tenhle format Bratislava
            $address_split = explode("\n", $this->address);
            $street        = $address_split[0];

            if ('Slovensko' === $address_split[1]) {
                list($zip, $city) = explode(' ', $address_split[0], 2);
                $street           = ''; // v techto pripadech nemame ulici a cislo popisne, tj. nesmime prepisovat
            } else {
                list($zip, $city) = explode(' ', $address_split[1], 2);
            }

            if (in_array('sk_delete_mc', $this->configFlags)) {
                $city = str_replace('mestská časť ', '', $city);
                $city = str_replace('m. č. ', '', $city);
            }

            return new ViesAddress(
                address: $this->address,
                street: trim($street),
                zip: trim($zip),
                city: trim($city),
                countryCode: trim($this->countryCode)
            );
        }

        if (0 == $this->newlines && in_array($this->countryCode, ['EE']) && false !== strpos($this->address, '  ')) {
            $address          = preg_replace('/ {3,}/', '  ', $this->address); // sometimes they have more than 2 space as divider, we trim the additional ones here
            $address_split    = explode('  ', $address);
            $street           = $address_split[0];
            list($zip, $city) = explode(' ', $address_split[1], 2);

            return new ViesAddress(
                address: $address,
                street: trim($street),
                zip: trim($zip),
                city: trim($city),
                countryCode: trim($this->countryCode)
            );
        }

        if (1 == $this->newlines && in_array($this->countryCode, ['CZ'])) { // Countries in expected format
            $address_split = explode("\n", $this->address);
            $street        = $address_split[0];
            $pos           = strpos($address_split[1], ' ', strpos($address_split[1], ' ') + 1); // second space marks ending of ZIP code

            if (false === $pos) {
                return null;
            }
            list($zip, $city) = [substr($address_split[1], 0, $pos), substr($address_split[1], $pos)];

            return new ViesAddress(
                address: $this->address,
                street: trim($street),
                zip: trim($zip),
                city: trim($city),
                countryCode: trim($this->countryCode)
            );
        }

        if (2 == $this->newlines && in_array($this->countryCode, ['CZ'])) { // Countries in expected format
            $address_split = explode("\n", $this->address);
            $street        = $address_split[0] . ', ' . $address_split[1];
            $pos           = strpos($address_split[2], ' ', strpos($address_split[2], ' ') + 1); // second space marks ending of ZIP code

            if (false === $pos) {
                return null;
            }
            list($zip, $city) = [substr($address_split[2], 0, $pos), substr($address_split[2], $pos)];

            return new ViesAddress(
                address: $this->address,
                street: trim($street),
                zip: trim($zip),
                city: trim($city),
                countryCode: trim($this->countryCode)
            );
        }

        return null;
    }

    // https://gist.github.com/teomaragakis/7580134
    // transliterates Greek characters to English
    private function make_greeklish($text)
    {
        $expressions = [
            '/[αΑ][ιίΙΊ]/u'                             => 'e',
            '/[οΟΕε][ιίΙΊ]/u'                           => 'i',
            '/[αΑ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'af$1',
            '/[αΑ][υύΥΎ]/u'                             => 'av',
            '/[εΕ][υύΥΎ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u' => 'ef$1',
            '/[εΕ][υύΥΎ]/u'                             => 'ev',
            '/[οΟ][υύΥΎ]/u'                             => 'ou',
            '/(^|\s)[μΜ][πΠ]/u'                         => '$1b',
            '/[μΜ][πΠ](\s|$)/u'                         => 'b$1',
            '/[μΜ][πΠ]/u'                               => 'mp',
            '/[νΝ][τΤ]/u'                               => 'nt',
            '/[τΤ][σΣ]/u'                               => 'ts',
            '/[τΤ][ζΖ]/u'                               => 'tz',
            '/[γΓ][γΓ]/u'                               => 'ng',
            '/[γΓ][κΚ]/u'                               => 'gk',
            '/[ηΗ][υΥ]([θΘκΚξΞπΠσςΣτTφΡχΧψΨ]|\s|$)/u'   => 'if$1',
            '/[ηΗ][υΥ]/u'                               => 'iu',
            '/[θΘ]/u'                                   => 'th',
            '/[χΧ]/u'                                   => 'ch',
            '/[ψΨ]/u'                                   => 'ps',
            '/[αά]/u'                                   => 'a',
            '/[βΒ]/u'                                   => 'v',
            '/[γΓ]/u'                                   => 'g',
            '/[δΔ]/u'                                   => 'd',
            '/[εέΕΈ]/u'                                 => 'e',
            '/[ζΖ]/u'                                   => 'z',
            '/[ηήΗΉ]/u'                                 => 'i',
            '/[ιίϊΙΊΪ]/u'                               => 'i',
            '/[κΚ]/u'                                   => 'k',
            '/[λΛ]/u'                                   => 'l',
            '/[μΜ]/u'                                   => 'm',
            '/[νΝ]/u'                                   => 'n',
            '/[ξΞ]/u'                                   => 'x',
            '/[οόΟΌ]/u'                                 => 'o',
            '/[πΠ]/u'                                   => 'p',
            '/[ρΡ]/u'                                   => 'r',
            '/[σςΣ]/u'                                  => 's',
            '/[τΤ]/u'                                   => 't',
            '/[υύϋΥΎΫ]/u'                               => 'i',
            '/[φΦ]/iu'                                  => 'f',
            '/[ωώ]/iu'                                  => 'o',
            '/[Α]/iu'                                   => 'a', // added as otherwise "A" kept as capitals
        ];

        return preg_replace(array_keys($expressions), array_values($expressions), $text);
    }

    protected function parseEuropeanGroup1()
    {
        $address_split    = explode("\n", $this->address);
        $street           = $address_split[0];
        list($zip, $city) = explode(' ', $address_split[1], 2);

        return new ViesAddress(
            address: $this->address,
            street: trim($street),
            zip: trim($zip),
            city: trim($city),
            countryCode: trim($this->countryCode),
        );
    }

    protected function parseEuropeanGroup2()
    {
        $address_split = explode(',', $this->address);
        $street        = $address_split[0];

        if (3 == count($address_split)) {
            $street = $street . ', ' . trim($address_split[1]);
        } // sometimes they have aditional thing after street, seems to be city, but better not to omit
        list($zip, $city) = explode(' ', trim($address_split[array_key_last($address_split)]), 2);

        return new ViesAddress(
            address: $this->address,
            street: trim($street),
            zip: trim($zip),
            city: trim($city),
            countryCode: trim($this->countryCode),
        );
    }

    protected function parseGreekGroup()
    {
        if (in_array('do_not_greeklish', $this->configFlags)) {
            $this->address = $this->address;
        } else {
            $this->address = $this->make_greeklish($this->address);
        }
        $hyphen_pos                   = strpos($this->address, ' - ');
        $city                         = substr($this->address, $hyphen_pos + 3);
        $address_without_city         = substr($this->address, 0, $hyphen_pos);
        $zip_pos                      = strrpos($address_without_city, ' ');
        $zip                          = substr($address_without_city, $zip_pos + 1);
        $address_without_zip_and_city = substr($address_without_city, 0, $zip_pos);
        $street                       = trim($address_without_zip_and_city);

        return new ViesAddress(
            address: $this->address,
            street: trim($street),
            zip: trim($zip),
            city: trim($city),
            countryCode: trim($this->countryCode),
        );
    }

    protected function parseRomaniaGroup1()
    {
        $address_split = explode(',', $this->address);
        $street        = trim($address_split[1]);
        $city          = trim($address_split[0]);

        if (preg_match('/SECTOR\s+(\d+)/i', $street, $matches)) {
            $city .= ' ' . $matches[0]; // Return the full "SECTOR X" match
            // Remove the matched sector from the original string
            $street = preg_replace('/' . preg_quote($matches[0], '/') . '\s*/', '', $street, 1);
        }

        if (preg_match('/^(\d+)(?=\s+STR\.)/i', $street, $matches)) {
            $zip = $matches[1]; // Get the captured number
            // Remove the zip from the original string
            $street = preg_replace('/^' . preg_quote($matches[1], '/') . '\s+/i', '', $street);
        }

        return new ViesAddress(
            address: $this->address,
            street: $street,
            zip: $zip,
            city: $city,
            countryCode: $this->countryCode,
        );
    }

    protected function parseRomaniaGroup2()
    {
        $address_split = explode("\n", $this->address);
        $street        = trim($address_split[1]);
        $city          = trim($address_split[0]);

        return new ViesAddress(
            address: $this->address,
            street: trim($street),
            zip: null,
            city: trim($city),
            countryCode: trim($this->countryCode),
        );
    }

    protected function parseRomaniaGroup3()
    {
        $address_split = explode("\n", $this->address);
        $street        = trim($address_split[2]) . ', ' . trim($address_split[1]);
        $city          = trim($address_split[0]);

        return new ViesAddress(
            address: $this->address,
            street: trim($street),
            zip: null,
            city: trim($city),
            countryCode: trim($this->countryCode),
        );
    }

    protected function parseItalianGroup()
    {
        $address_split    = explode("\n", $this->address);
        $street           = $address_split[0];
        list($zip, $city) = explode(' ', $address_split[1], 2);

        return new ViesAddress(
            address: $this->address,
            street: trim($street),
            zip: trim($zip),
            city: trim($city),
            countryCode: trim($this->countryCode),
        );
    }

    protected function parsePortugueseGroup()
    {
        $address_split = explode("\n", $this->address);
        $street        = $address_split[0];
        $city          = $address_split[1];
        list($zip)     = explode(' ', $address_split[2], 2);

        return new ViesAddress(
            address: $this->address,
            street: trim($street),
            zip: trim($zip),
            city: trim($city),
            countryCode: trim($this->countryCode),
        );
    }
}
