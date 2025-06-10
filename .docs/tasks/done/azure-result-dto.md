# Task: Implement Azure Document Intelligence Response DTOs

## Objective
Create a set of DTOs to properly type and structure the Azure Document Intelligence API response, focusing on the essential fields while maintaining type safety and following the project's conventions.

## Current State
- Basic `DocumentAnalysisResult` DTO exists but only handles status, fields, and error
- Raw response contains more structured data that should be properly typed
- Response mapping is currently done in `GetAnalysisResultRequest` and `DocumentAnalysisAgent`

## Required DTOs Structure

1. **DocumentAnalysisResult** (Main DTO)
```php
class DocumentAnalysisResult extends BaseDataDTO
{
    public function __construct(
        public readonly DocumentAnalysisStatus $status,
        public readonly ?AnalyzeResult $analyzeResult = null,
        public readonly ?string $error = null
    ) {}
}
```

2. **AnalyzeResult**
```php
class AnalyzeResult extends BaseDataDTO
{
    public function __construct(
        public readonly string $apiVersion,
        public readonly string $modelId,
        public readonly ?string $content = null,
        public readonly string $contentFormat,
        public readonly array $documents
    ) {}
}
```

3. **Document**
```php
class Document extends BaseDataDTO
{
    public function __construct(
        public readonly string $docType,
        public readonly array $fields,
        public readonly float $confidence
    ) {}
}
```

4. **DocumentField** (for typed field values)
```php
class DocumentField extends BaseDataDTO
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $value,
        public readonly string $content,
        public readonly float $confidence
    ) {}
}
```

## Implementation Tasks

1. Create new DTO classes in `app/Services/AzureDocumentIntelligence/DTOs/`:
   - `AnalyzeResult.php`
   - `Document.php`
   - `DocumentField.php`

2. Update existing `DocumentAnalysisResult.php` to use the new structure

3. Update `GetAnalysisResultRequest::createDtoFromResponse()` to map the response to the new DTO structure:
   ```php
   public function createDtoFromResponse(Response $response): DocumentAnalysisResult
   {
       $data = $response->json();
       
       return new DocumentAnalysisResult(
           status: DocumentAnalysisStatus::from($data['status']),
           analyzeResult: $data['analyzeResult'] ? new AnalyzeResult(
               apiVersion: $data['analyzeResult']['apiVersion'],
               modelId: $data['analyzeResult']['modelId'],
               content: $data['analyzeResult']['content'] ?? null,
               contentFormat: $data['analyzeResult']['contentFormat'],
               documents: array_map(
                   fn(array $doc) => new Document(
                       docType: $doc['docType'],
                       fields: $this->mapFields($doc['fields']),
                       confidence: $doc['confidence']
                   ),
                   $data['analyzeResult']['documents']
               )
           ) : null,
           error: $data['error'] ?? null
       );
   }
   ```

4. Add helper method for field mapping:
   ```php
   protected function mapFields(array $fields): array
   {
       return array_map(
           fn(array $field) => new DocumentField(
               type: $field['type'],
               value: $field['value'] ?? null,
               content: $field['content'],
               confidence: $field['confidence']
           ),
           $fields
       );
   }
   ```

## Requirements

1. All DTOs must extend `BaseDataDTO`
2. Use proper type hints and readonly properties
3. Follow the project's DTO naming conventions
4. Implement proper serialization methods
5. Add PHPDoc blocks with property descriptions
6. Handle nullable fields appropriately
7. Use proper type casting for values (e.g., dates, numbers)

## Testing

1. Create unit tests for each DTO
2. Test serialization/deserialization
3. Test with sample response data
4. Verify type safety
5. Test edge cases (null values, missing fields)

## Notes

- The implementation should focus on the essential fields as specified
- Complex nested structures (like polygons, spans) can be omitted
- The DTOs should be easily extensible for future needs
- Consider adding validation for required fields
- Follow the project's existing patterns for DTO implementation

---

# Refactor, improvements

Yes, we can improve this by moving the mapping logic to a static factory method in `DocumentAnalysisResult`. This follows better object-oriented principles and makes the code more maintainable. Here's how we can refactor it:

I apologize for the confusion with the tools. Let me show you the complete refactoring approach:

1. First, we need to add static factory methods to each DTO:

```php
// app/Services/AzureDocumentIntelligence/DTOs/DocumentAnalysisResult.php
class DocumentAnalysisResult extends BaseDataDTO
{
    public function __construct(
        public readonly DocumentAnalysisStatus $status,
        public readonly ?AnalyzeResult $analyzeResult = null,
        public readonly ?string $error = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: DocumentAnalysisStatus::from($data['status']),
            analyzeResult: isset($data['analyzeResult']) ? AnalyzeResult::fromArray($data['analyzeResult']) : null,
            error: $data['error']['message'] ?? ($data['error'] ?? null)
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'analyzeResult' => $this->analyzeResult?->toArray(),
            'error'  => $this->error,
        ];
    }
}

// app/Services/AzureDocumentIntelligence/DTOs/AnalyzeResult.php
class AnalyzeResult extends BaseDataDTO
{
    public function __construct(
        public readonly string $apiVersion,
        public readonly string $modelId,
        public readonly ?string $content = null,
        public readonly string $contentFormat,
        public readonly array $documents
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            apiVersion: $data['apiVersion'] ?? '',
            modelId: $data['modelId'] ?? '',
            content: $data['content'] ?? null,
            contentFormat: $data['contentFormat'] ?? '',
            documents: array_map(
                fn(array $doc) => Document::fromArray($doc),
                $data['documents'] ?? []
            )
        );
    }

    public function toArray(): array
    {
        return [
            'apiVersion' => $this->apiVersion,
            'modelId' => $this->modelId,
            'content' => $this->content,
            'contentFormat' => $this->contentFormat,
            'documents' => array_map(fn($doc) => $doc instanceof BaseDataDTO ? $doc->toArray() : $doc, $this->documents),
        ];
    }
}

// app/Services/AzureDocumentIntelligence/DTOs/Document.php
class Document extends BaseDataDTO
{
    public function __construct(
        public readonly string $docType,
        public readonly array $fields,
        public readonly float $confidence
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            docType: $data['docType'] ?? '',
            fields: self::mapFields($data['fields'] ?? []),
            confidence: (float)($data['confidence'] ?? 0)
        );
    }

    protected static function mapFields(array $fields): array
    {
        $result = [];
        foreach ($fields as $key => $field) {
            $result[$key] = DocumentField::fromArray($field);
        }
        return $result;
    }

    public function toArray(): array
    {
        return [
            'docType' => $this->docType,
            'fields' => array_map(fn($field) => $field instanceof BaseDataDTO ? $field->toArray() : $field, $this->fields),
            'confidence' => $this->confidence,
        ];
    }
}

// app/Services/AzureDocumentIntelligence/DTOs/DocumentField.php
class DocumentField extends BaseDataDTO
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $value,
        public readonly string $content,
        public readonly float $confidence
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? '',
            value: $data['valueCurrency']['amount'] ?? 
                  $data['valueString'] ?? 
                  $data['valueDate'] ?? 
                  $data['valueNumber'] ?? 
                  $data['valueArray'] ?? 
                  $data['valueObject'] ?? 
                  null,
            content: $data['content'] ?? '',
            confidence: (float)($data['confidence'] ?? 0)
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'value' => $this->value,
            'content' => $this->content,
            'confidence' => $this->confidence,
        ];
    }
}
```

2. Then simplify the `GetAnalysisResultRequest`:

```php
// app/Services/AzureDocumentIntelligence/Requests/GetAnalysisResultRequest.php
class GetAnalysisResultRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $resultUrl)
    {
    }

    public function resolveEndpoint(): string
    {
        return $this->resultUrl;
    }

    public function createDtoFromResponse(Response $response): DocumentAnalysisResult
    {
        return DocumentAnalysisResult::fromArray($response->json());
    }
}
```

Benefits of this refactoring:

1. **Single Responsibility**: Each DTO is responsible for its own mapping logic
2. **Encapsulation**: Mapping logic is encapsulated within the DTOs
3. **Reusability**: The `fromArray` methods can be used anywhere, not just in the request
4. **Maintainability**: Easier to modify mapping logic as it's closer to the data structure
5. **Type Safety**: Each DTO handles its own type casting and validation
6. **Testability**: Easier to test mapping logic in isolation

Would you like me to implement any specific part of this refactoring?
