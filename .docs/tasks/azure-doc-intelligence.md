# 📄 Plan: Integracja Azure Document Intelligence (v4.0.0 REST API) z Laravel (Saloon)

## 🎯 Cel

Stworzenie klasy serwisowej w PHP (Laravel), która korzysta z klienta HTTP [Saloon](https://docs.saloon.dev/), aby komunikować się z REST API Azure Document Intelligence.

Zakres:
- Wysyłanie dokumentu (PDF)
- Polling (czekanie na analizę)
- Pobranie i zwrot wyniku
- (Opcjonalnie) mapowanie danych do struktury aplikacji

---

## 🧱 Stack technologiczny

- Laravel (PHP 8.3)
- Saloon v3
- Azure Document Intelligence REST API (v4.0.0)
- JSON-based response
- REST (nie SDK)

---

## 📁 Proponowana struktura katalogów

App/
└── Services/
    └── AzureDocumentIntelligence/
        ├── AzureConnector.php
        ├── AnalyzeDocumentRequest.php
        ├── GetAnalysisResultRequest.php
        └── DocumentAnalysisService.php

---

## ⚙️ Konfiguracja środowiska

> Uwaga: Serwis może działać w dwóch trybach: (1) używając danych z `.env` jako usługa wewnętrzna aplikacji, (2) używając danych endpoint, klucza, modelu z ustawień tenanta (tu potrzebny jest model typu TenantIntegration dla trzymnia konfiguracji różnych integracji)

.env:
AZURE_DOCUMENT_INTELLIGENCE_ENDPOINT=https://<your-resource-name>.cognitiveservices.azure.com/
AZURE_DOCUMENT_INTELLIGENCE_KEY=<your-key>
AZURE_DOCUMENT_MODEL_ID=prebuilt-invoice

(config opcjonalnie: config/azure_doc_intel.php)

---

## 🔌 AzureConnector.php

namespace App\Services\AzureDocumentIntelligence;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class AzureConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return config('azure_doc_intel.endpoint');
    }

    protected function defaultHeaders(): array
    {
        return [
            'Ocp-Apim-Subscription-Key' => config('azure_doc_intel.key'),
        ];
    }
}

---

## 📤 AnalyzeDocumentRequest.php

namespace App\Services\AzureDocumentIntelligence;

use Saloon\Http\Request;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasStreamBody;

class AnalyzeDocumentRequest extends Request
{
    use HasStreamBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $modelId,
        protected string $filePath
    ) {}

    public function resolveEndpoint(): string
    {
        return "/formrecognizer/documentModels/{$this->modelId}:analyze?api-version=2023-10-31";
    }

    protected function defaultHeaders(): array
    {
        return ['Content-Type' => 'application/pdf'];
    }

    protected function defaultBody(): mixed
    {
        return fopen($this->filePath, 'r');
    }
}

---

## 🔁 GetAnalysisResultRequest.php

class GetAnalysisResultRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $resultUrl) {}

    public function resolveEndpoint(): string
    {
        return $this->resultUrl;
    }
}

---

## 🧠 DocumentAnalysisService.php

namespace App\Services\AzureDocumentIntelligence;

class DocumentAnalysisService
{
    protected AzureConnector $connector;

    public function __construct()
    {
        $this->connector = new AzureConnector();
    }

    public function analyze(string $filePath, string $modelId = null): array
    {
        $modelId = $modelId ?? config('azure_doc_intel.model_id');

        $uploadRequest = new AnalyzeDocumentRequest($modelId, $filePath);
        $response = $this->connector->send($uploadRequest);

        if (!$response->successful()) {
            throw new \Exception('Failed to submit document to Azure.');
        }

        $operationLocation = $response->header('Operation-Location');

        sleep(5); // uproszczony polling, docelowo można przenieść do joba z retry

        $pollRequest = new GetAnalysisResultRequest($operationLocation);
        $pollResponse = $this->connector->send($pollRequest);

        return $pollResponse->json();
    }
}

---

## ✅ To-do / dalsze kroki

- [ ] Zainstaluj Saloon (`composer require saloonphp/saloon`)
- [ ] Dodaj konfigurację do `.env` / `config/azure_doc_intel.php`
- [ ] Wygeneruj pliki klas (Connector, Requesty, Serwis)
- [ ] Utwórz prosty testowy endpoint / job
- [ ] (Opcjonalnie) Zmapuj dane do modeli Eloquent
- [ ] (Opcjonalnie) Przenieś polling do osobnego Job + retry

---

## ✨ Rozszerzenia (opcjonalne)

- Retry z exponential backoff
- Obsługa dokumentów zdalnych (URL, nie tylko upload)
- Walidacja / standaryzacja danych (np. invoice fields)
- Notyfikacja użytkownika po zakończeniu przetwarzania

---

Gotowe do integracji z SaasBase-Api. 🚀
