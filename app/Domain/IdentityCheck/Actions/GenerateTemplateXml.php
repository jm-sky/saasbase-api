<?php

namespace App\Domain\IdentityCheck\Actions;

use App\Domain\Auth\Models\User;
use Carbon\Carbon;

class GenerateTemplateXml
{
    public static function generateTemplateXml(User $user, string $token): string
    {
        $now     = Carbon::now('UTC');
        $appName = config('app.name');

        $fullName  = $user->full_name;
        $birthDate = $user->profile?->birth_date ? Carbon::parse($user->profile->birth_date)->toDateString() : '';
        $pesel     = $user->personalData?->pesel ?? '';

        return self::generateIdentityXml([
            'FirstName'         => $user->first_name,
            'LastName'          => $user->last_name,
            'FullName'          => $fullName,
            'BirthDate'         => $birthDate,
            'PESEL'             => $pesel,
            'GeneratedAt'       => $now->toIso8601String(),
            'ConfirmationToken' => $token,
            'ApplicationName'   => $appName,
        ]);
    }

    /**
     * Helper to generate XML for identity confirmation.
     */
    public static function generateIdentityXml(array $data): string
    {
        $dom               = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $baseUrl           = config('app.frontend_url');

        // Add XSL stylesheet reference
        $xsl = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . $baseUrl . '/xml/identity/v1/identity-confirmation.xsl"');
        $dom->appendChild($xsl);

        // Create root element with namespace and schema location
        $root = $dom->createElementNS($baseUrl . '/xml/identity/v1', 'IdentityConfirmation');
        $root->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', $baseUrl . '/xml/identity/v1/identity-confirmation.xsd');
        $dom->appendChild($root);

        // Add data elements
        foreach ($data as $key => $value) {
            $element = $dom->createElement($key, htmlspecialchars($value));
            $root->appendChild($element);
        }

        return $dom->saveXML();
    }
}
