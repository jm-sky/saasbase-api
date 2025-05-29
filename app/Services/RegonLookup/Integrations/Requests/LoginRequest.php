<?php

namespace App\Services\RegonLookup\Integrations\Requests;

use App\Services\RegonLookup\DTOs\RegonAuthResultDTO;
use App\Services\RegonLookup\Exceptions\RegonLookupException;
use Saloon\Enums\Method;
use Saloon\Http\Response;

class LoginRequest extends BaseRegonRequest
{
    protected Method $method = Method::POST;

    protected string $userKey;

    public function __construct()
    {
        parent::__construct();

        $userKey = config('regon_lookup.user_key');

        if (!$userKey) {
            throw new RegonLookupException('REGON API user key not configured.');
        }

        $this->userKey = $userKey;
    }

    public function hasRequestFailed(Response $response): bool
    {
        $data = $response->xml();

        return empty((string) $data->{'ZalogujResponse'}->{'ZalogujResult'});
    }

    public function createDtoFromResponse(Response $response): RegonAuthResultDTO
    {
        $data       = $response->xml();
        $sessionKey = (string) $data->{'ZalogujResponse'}->{'ZalogujResult'};

        return new RegonAuthResultDTO($sessionKey);
    }

    protected function defaultBody(): ?string
    {
        return <<<XML
            <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ns="http://CIS/BIR/PUBL/2014/07">
                <soap:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
                    <wsa:To>{$this->baseUrl}</wsa:To>
                    <wsa:Action>http://CIS/BIR/PUBL/2014/07/IUslugaBIRzewnPubl/Zaloguj</wsa:Action>
                </soap:Header>
                <soap:Body>
                    <ns:Zaloguj>
                        <ns:pKluczUzytkownika>{$this->userKey}</ns:pKluczUzytkownika>
                    </ns:Zaloguj>
                </soap:Body>
            </soap:Envelope>
        XML;
    }
}
