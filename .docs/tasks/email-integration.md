# Email Invoice Integration - Technical Specification

## Purpose

Implement automatic email integration to periodically check email accounts, recognize expense invoices, and import them into the SaaS system. This feature will enable tenants to automatically process invoices received via email, reducing manual data entry and improving workflow efficiency.

## High-Level Requirements

- **Email Integration**: Universal POP3 support with future OAuth provider support (Gmail, Outlook)
- **Per-Tenant Configuration**: Multiple email accounts per tenant
- **Invoice Recognition**: Configurable rules without AI (sender, subject, attachment, content, keywords)
- **OCR Integration**: Use existing OCR system for data extraction
- **Workflow Integration**: Start as draft invoices, follow existing approval processes
- **Duplicate Prevention**: Track by vendor + number + year, attachment checksum, message ID
- **Queue Processing**: Use existing queue system with retry logic
- **Periodic Tasks**: Integrate with existing cron-like scheduling system

## Database Schema

### 1. EmailAccount Model

```php
Schema::create('email_accounts', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('tenant_id')->constrained()->onDelete('cascade');
    $table->string('name'); // "Accounting Gmail", "Main Office"
    $table->enum('provider', ['pop3', 'gmail', 'outlook']); // Extensible for future providers
    $table->text('credentials'); // Encrypted JSON: {host, port, username, password} or OAuth tokens
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_sync_at')->nullable();
    $table->timestamps();
    
    $table->index(['tenant_id', 'is_active']);
});
```

### 2. EmailRule Model

```php
Schema::create('email_rules', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('email_account_id')->constrained()->onDelete('cascade');
    $table->string('name'); // "Vendor ABC Invoices"
    $table->integer('priority')->default(100); // Rule execution order (lower = higher priority)
    $table->json('conditions'); // AND-only logic conditions
    $table->json('actions'); // Import configuration
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index(['email_account_id', 'priority', 'is_active']);
});
```

**Conditions JSON Structure:**
```json
{
  "sender_patterns": ["*@vendor.com", "invoices@*"],
  "subject_patterns": ["*invoice*", "*bill*", "*payment due*"],
  "keywords": ["amount due", "payment terms", "invoice number"],
  "attachment_required": true,
  "attachment_extensions": ["pdf", "jpg", "png"],
  "exclude_keywords": ["receipt", "quote"] // Optional exclusion patterns
}
```

**Actions JSON Structure:**
```json
{
  "import_as_draft": true,
  "contractor_id": "01ABC...", // null for auto-create
  "auto_approve": false,
  "notification_enabled": true,
  "default_invoice_type": "basic"
}
```

### 3. EmailMessage Model

```php
Schema::create('email_messages', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('email_account_id')->constrained()->onDelete('cascade');
    $table->string('message_id'); // Email message ID from server
    $table->timestamp('processed_at');
    $table->enum('status', ['processed', 'failed', 'ignored']);
    $table->integer('failure_count')->default(0);
    $table->text('last_error')->nullable();
    $table->timestamps();
    
    $table->unique(['email_account_id', 'message_id']);
    $table->index(['email_account_id', 'status']);
});
```

### 4. EmailInvoiceImport Model

```php
Schema::create('email_invoice_imports', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('email_message_id')->constrained()->onDelete('cascade');
    $table->foreignUlid('invoice_id')->constrained()->onDelete('cascade');
    $table->foreignUlid('rule_id')->constrained('email_rules')->onDelete('cascade');
    $table->decimal('confidence_score', 5, 2)->default(0); // 0-100 score
    $table->json('extracted_data'); // Raw OCR/extraction results for debugging
    $table->timestamps();
    
    $table->index(['invoice_id']);
    $table->index(['email_message_id']);
});
```

### 5. EmailNotificationSettings Model

```php
Schema::create('email_notification_settings', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('tenant_id')->constrained()->onDelete('cascade');
    $table->json('notify_users'); // Array of user IDs to notify on failures
    $table->boolean('notify_on_success')->default(false);
    $table->boolean('notify_on_failure')->default(true);
    $table->integer('max_failures_before_disable')->default(10);
    $table->timestamps();
    
    $table->unique('tenant_id');
});
```

## System Architecture

### Queue Jobs Structure

```php
// Main periodic job
class EmailSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle(): void
    {
        EmailAccount::active()->each(function ($account) {
            FetchEmailsJob::dispatch($account);
        });
    }
}

// Per-account email fetching
class FetchEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    
    public function __construct(public EmailAccount $emailAccount) {}
    
    public function handle(): void
    {
        $emailService = app(EmailServiceFactory::class)->create($this->emailAccount);
        $newMessages = $emailService->fetchNewMessages();
        
        foreach ($newMessages as $message) {
            ProcessEmailJob::dispatch($this->emailAccount, $message);
        }
        
        $this->emailAccount->update(['last_sync_at' => now()]);
    }
}

// Per-message processing
class ProcessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 5;
    public $backoff = [60, 300, 900, 1800, 3600]; // Exponential backoff
    
    public function __construct(
        public EmailAccount $emailAccount,
        public array $messageData
    ) {}
    
    public function handle(): void
    {
        // Check if already processed
        $existingMessage = EmailMessage::where([
            'email_account_id' => $this->emailAccount->id,
            'message_id' => $this->messageData['message_id']
        ])->first();
        
        if ($existingMessage) {
            return; // Already processed
        }
        
        // Apply rules to determine if this is an invoice
        $ruleEngine = app(EmailRuleEngine::class);
        $matchedRule = $ruleEngine->findMatchingRule($this->emailAccount, $this->messageData);
        
        if ($matchedRule) {
            ExtractInvoiceDataJob::dispatch($this->emailAccount, $this->messageData, $matchedRule);
        }
        
        // Record processing
        EmailMessage::create([
            'email_account_id' => $this->emailAccount->id,
            'message_id' => $this->messageData['message_id'],
            'processed_at' => now(),
            'status' => $matchedRule ? 'processed' : 'ignored'
        ]);
    }
    
    public function failed(Throwable $exception): void
    {
        app('sentry')->captureException($exception);
        
        EmailMessage::updateOrCreate(
            [
                'email_account_id' => $this->emailAccount->id,
                'message_id' => $this->messageData['message_id']
            ],
            [
                'status' => 'failed',
                'failure_count' => DB::raw('failure_count + 1'),
                'last_error' => $exception->getMessage(),
                'processed_at' => now()
            ]
        );
        
        // Notify tenant users
        $this->notifyTenantUsers($exception);
    }
}

// Invoice data extraction using existing OCR
class ExtractInvoiceDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public EmailAccount $emailAccount,
        public array $messageData,
        public EmailRule $rule
    ) {}
    
    public function handle(): void
    {
        // Download attachments
        $attachments = $this->downloadAttachments();
        
        // Create draft invoice
        $invoice = $this->createDraftInvoice();
        
        // Store attachments using Spatie Media Library
        foreach ($attachments as $attachment) {
            $media = $invoice->addMediaFromString($attachment['content'])
                ->usingName($attachment['filename'])
                ->withCustomProperties([
                    'source' => 'email',
                    'email_account_id' => $this->emailAccount->id,
                    'original_message_id' => $this->messageData['message_id'],
                    'sender' => $this->messageData['sender'],
                    'received_at' => $this->messageData['received_at'],
                    'rule_id' => $this->rule->id
                ])
                ->toMediaCollection('email_attachments');
        }
        
        // Trigger existing OCR process
        ProcessInvoiceOcrJob::dispatch($invoice);
        
        // Record import
        EmailInvoiceImport::create([
            'email_message_id' => $this->getEmailMessageId(),
            'invoice_id' => $invoice->id,
            'rule_id' => $this->rule->id,
            'confidence_score' => $this->calculateConfidenceScore(),
            'extracted_data' => $this->messageData
        ]);
    }
}
```

### Email Service Factory

```php
interface EmailServiceInterface
{
    public function testConnection(): bool;
    public function fetchNewMessages(): array;
}

class EmailServiceFactory
{
    public function create(EmailAccount $account): EmailServiceInterface
    {
        return match ($account->provider) {
            'pop3' => new Pop3EmailService($account),
            'gmail' => new GmailEmailService($account),
            'outlook' => new OutlookEmailService($account),
            default => throw new InvalidArgumentException("Unsupported provider: {$account->provider}")
        };
    }
}

class Pop3EmailService implements EmailServiceInterface
{
    public function __construct(private EmailAccount $account) {}
    
    public function testConnection(): bool
    {
        $credentials = decrypt($this->account->credentials);
        
        try {
            $connection = imap_open(
                "{{$credentials['host']}:{$credentials['port']}/pop3/ssl}INBOX",
                $credentials['username'],
                $credentials['password']
            );
            
            if ($connection) {
                imap_close($connection);
                return true;
            }
        } catch (Exception $e) {
            // Log error
        }
        
        return false;
    }
    
    public function fetchNewMessages(): array
    {
        $credentials = decrypt($this->account->credentials);
        $messages = [];
        
        $connection = imap_open(
            "{{$credentials['host']}:{$credentials['port']}/pop3/ssl}INBOX",
            $credentials['username'],
            $credentials['password']
        );
        
        $messageCount = imap_num_msg($connection);
        
        for ($i = 1; $i <= $messageCount; $i++) {
            $header = imap_headerinfo($connection, $i);
            $messageId = $header->message_id;
            
            // Skip if already processed
            if ($this->isAlreadyProcessed($messageId)) {
                continue;
            }
            
            $messages[] = [
                'message_id' => $messageId,
                'sender' => $header->fromaddress,
                'subject' => $header->subject,
                'received_at' => date('Y-m-d H:i:s', $header->udate),
                'body' => imap_body($connection, $i),
                'attachments' => $this->getAttachments($connection, $i)
            ];
        }
        
        imap_close($connection);
        return $messages;
    }
    
    private function isAlreadyProcessed(string $messageId): bool
    {
        return EmailMessage::where([
            'email_account_id' => $this->account->id,
            'message_id' => $messageId
        ])->exists();
    }
}
```

### Rule Engine

```php
class EmailRuleEngine
{
    public function findMatchingRule(EmailAccount $account, array $messageData): ?EmailRule
    {
        $rules = $account->emailRules()
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();
            
        foreach ($rules as $rule) {
            if ($this->matchesRule($messageData, $rule)) {
                return $rule;
            }
        }
        
        return null;
    }
    
    private function matchesRule(array $messageData, EmailRule $rule): bool
    {
        $conditions = $rule->conditions;
        
        // All conditions must pass (AND logic)
        if (!$this->matchesSenderPatterns($messageData['sender'], $conditions['sender_patterns'] ?? [])) {
            return false;
        }
        
        if (!$this->matchesSubjectPatterns($messageData['subject'], $conditions['subject_patterns'] ?? [])) {
            return false;
        }
        
        if (!$this->matchesKeywords($messageData['body'], $conditions['keywords'] ?? [])) {
            return false;
        }
        
        if (!$this->matchesExcludeKeywords($messageData['body'], $conditions['exclude_keywords'] ?? [])) {
            return false;
        }
        
        if (!$this->matchesAttachmentRequirements($messageData['attachments'], $conditions)) {
            return false;
        }
        
        return true;
    }
    
    private function matchesSenderPatterns(string $sender, array $patterns): bool
    {
        if (empty($patterns)) return true;
        
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $sender)) {
                return true;
            }
        }
        return false;
    }
    
    private function matchesSubjectPatterns(string $subject, array $patterns): bool
    {
        if (empty($patterns)) return true;
        
        foreach ($patterns as $pattern) {
            if (Str::contains(strtolower($subject), strtolower($pattern))) {
                return true;
            }
        }
        return false;
    }
    
    private function matchesKeywords(string $body, array $keywords): bool
    {
        if (empty($keywords)) return true;
        
        $body = strtolower($body);
        foreach ($keywords as $keyword) {
            if (Str::contains($body, strtolower($keyword))) {
                return true;
            }
        }
        return false;
    }
    
    private function matchesExcludeKeywords(string $body, array $excludeKeywords): bool
    {
        if (empty($excludeKeywords)) return true;
        
        $body = strtolower($body);
        foreach ($excludeKeywords as $keyword) {
            if (Str::contains($body, strtolower($keyword))) {
                return false; // Found excluded keyword
            }
        }
        return true;
    }
    
    private function matchesAttachmentRequirements(array $attachments, array $conditions): bool
    {
        $attachmentRequired = $conditions['attachment_required'] ?? false;
        $allowedExtensions = $conditions['attachment_extensions'] ?? [];
        
        if ($attachmentRequired && empty($attachments)) {
            return false;
        }
        
        if (!empty($allowedExtensions) && !empty($attachments)) {
            $hasValidAttachment = false;
            foreach ($attachments as $attachment) {
                $extension = pathinfo($attachment['filename'], PATHINFO_EXTENSION);
                if (in_array(strtolower($extension), array_map('strtolower', $allowedExtensions))) {
                    $hasValidAttachment = true;
                    break;
                }
            }
            return $hasValidAttachment;
        }
        
        return true;
    }
}
```

## Media Library Integration

### Custom Path Generator

```php
class EmailInvoicePathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $invoice = $media->model;
        $tenant = $invoice->tenant;
        
        return "{$tenant->slug}/{$tenant->id}/invoices/{$invoice->id}/{$media->id}";
    }
}

// Register in AppServiceProvider
public function register(): void
{
    $this->app->bind(PathGenerator::class, EmailInvoicePathGenerator::class);
}
```

### Media Storage with Custom Properties

```php
// In ExtractInvoiceDataJob
$media = $invoice->addMediaFromString($attachment['content'])
    ->usingName($attachment['filename'])
    ->withCustomProperties([
        'source' => 'email',
        'email_account_id' => $this->emailAccount->id,
        'original_message_id' => $this->messageData['message_id'],
        'sender' => $this->messageData['sender'],
        'received_at' => $this->messageData['received_at'],
        'rule_id' => $this->rule->id,
        'attachment_checksum' => md5($attachment['content']),
        'original_size' => strlen($attachment['content'])
    ])
    ->addMediaConversion('thumbnail')
    ->toMediaCollection('email_attachments');
```

## Periodic Tasks Integration

### Task Registration

```php
// In PeriodicTasksServiceProvider or similar
public function boot(): void
{
    $this->registerEmailSyncTasks();
}

private function registerEmailSyncTasks(): void
{
    PeriodicTask::register([
        'name' => 'email_invoice_sync',
        'description' => 'Sync invoices from configured email accounts',
        'job_class' => EmailSyncJob::class,
        'default_cron' => '*/15 * * * *', // Every 15 minutes
        'tenant_configurable' => true,
        'subscription_dependent' => true,
        'enabled' => true
    ]);
}
```

### Tenant-Specific Configuration

```php
// Allow tenants to configure sync frequency based on subscription
class EmailSyncConfiguration
{
    public static function getAllowedFrequencies(Tenant $tenant): array
    {
        $plan = $tenant->subscription->plan;
        $minFrequency = $plan->features['email_sync_frequency_min'] ?? 60;
        
        return [
            15 => '15 minutes',
            30 => '30 minutes', 
            60 => '1 hour',
            120 => '2 hours',
            240 => '4 hours',
            480 => '8 hours',
            1440 => '24 hours'
        ]->filter(fn($value, $key) => $key >= $minFrequency);
    }
}
```

## Duplicate Prevention

### Duplicate Detection Service

```php
class DuplicateInvoiceDetector
{
    public function isDuplicate(array $extractedData, Tenant $tenant): bool
    {
        // Strategy 1: Vendor + Number + Year
        if ($this->checkByVendorNumberYear($extractedData, $tenant)) {
            return true;
        }
        
        // Strategy 2: Attachment checksum
        if ($this->checkByAttachmentChecksum($extractedData, $tenant)) {
            return true;
        }
        
        // Strategy 3: Gross amount + date range (±2 days)
        if ($this->checkByAmountAndDate($extractedData, $tenant)) {
            return true;
        }
        
        return false;
    }
    
    private function checkByVendorNumberYear(array $data, Tenant $tenant): bool
    {
        if (!isset($data['vendor_name'], $data['invoice_number'], $data['issue_date'])) {
            return false;
        }
        
        $year = Carbon::parse($data['issue_date'])->year;
        
        return Invoice::where('tenant_id', $tenant->id)
            ->whereHas('seller', function ($query) use ($data) {
                $query->where('name', 'LIKE', "%{$data['vendor_name']}%");
            })
            ->where('number', $data['invoice_number'])
            ->whereYear('issue_date', $year)
            ->exists();
    }
    
    private function checkByAttachmentChecksum(array $data, Tenant $tenant): bool
    {
        if (!isset($data['attachment_checksum'])) {
            return false;
        }
        
        return Invoice::where('tenant_id', $tenant->id)
            ->whereHas('media', function ($query) use ($data) {
                $query->where('custom_properties->attachment_checksum', $data['attachment_checksum']);
            })
            ->exists();
    }
    
    private function checkByAmountAndDate(array $data, Tenant $tenant): bool
    {
        if (!isset($data['total_gross'], $data['issue_date'])) {
            return false;
        }
        
        $date = Carbon::parse($data['issue_date']);
        $startDate = $date->copy()->subDays(2);
        $endDate = $date->copy()->addDays(2);
        
        return Invoice::where('tenant_id', $tenant->id)
            ->where('total_gross', $data['total_gross'])
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->exists();
    }
}
```

## Subscription Plan Limits

### Quota Service

```php
class EmailQuotaService
{
    public function canAddEmailAccount(Tenant $tenant): bool
    {
        $currentCount = $tenant->emailAccounts()->count();
        $limit = $tenant->subscription->plan->features['email_accounts_limit'] ?? 1;
        
        return $currentCount < $limit;
    }
    
    public function canProcessEmail(Tenant $tenant): bool
    {
        $monthlyCount = EmailMessage::whereHas('emailAccount', function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        $limit = $tenant->subscription->plan->features['monthly_email_processing_limit'] ?? 100;
        
        return $monthlyCount < $limit;
    }
    
    public function getMinimumSyncFrequency(Tenant $tenant): int
    {
        return $tenant->subscription->plan->features['email_sync_frequency_min'] ?? 60;
    }
    
    public function getMaxRulesPerAccount(Tenant $tenant): int
    {
        return $tenant->subscription->plan->features['email_rules_per_account'] ?? 5;
    }
}
```

### Subscription Feature Definitions

```php
// In subscription plan features
[
    'email_accounts_limit' => 5,
    'email_sync_frequency_min' => 15, // Minimum minutes between syncs
    'email_rules_per_account' => 10,
    'monthly_email_processing_limit' => 1000
]
```

## Notification System Integration

### Email Processing Notifications

```php
class EmailImportSuccessNotification extends Notification
{
    public function __construct(
        public Invoice $invoice,
        public EmailAccount $emailAccount,
        public string $senderEmail
    ) {}
    
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }
    
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Invoice Imported from Email',
            'message' => "New invoice imported from {$this->senderEmail}",
            'type' => 'email_import_success',
            'data' => [
                'invoice_id' => $this->invoice->id,
                'email_account_id' => $this->emailAccount->id,
                'sender' => $this->senderEmail,
                'invoice_number' => $this->invoice->number
            ]
        ];
    }
}

class EmailImportFailureNotification extends Notification
{
    public function __construct(
        public EmailAccount $emailAccount,
        public string $error,
        public int $retryCount,
        public ?string $senderEmail = null
    ) {}
    
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }
    
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Email Invoice Import Failed',
            'message' => "Failed to process invoice from {$this->senderEmail}",
            'type' => 'email_import_failure',
            'data' => [
                'email_account_id' => $this->emailAccount->id,
                'error' => $this->error,
                'retry_count' => $this->retryCount,
                'sender' => $this->senderEmail
            ]
        ];
    }
}
```

## Security Considerations

### Credential Encryption

```php
// In EmailAccount model
protected $casts = [
    'credentials' => 'encrypted:array'
];

// Usage
$account = new EmailAccount([
    'credentials' => [
        'host' => 'pop.gmail.com',
        'port' => 995,
        'username' => 'user@gmail.com',
        'password' => 'app-specific-password'
    ]
]);
```

### Connection Security

```php
class EmailConnectionValidator
{
    public function validateConnection(array $credentials): array
    {
        $errors = [];
        
        // Validate SSL/TLS requirements
        if (!$this->supportsSSL($credentials['host'], $credentials['port'])) {
            $errors[] = 'SSL/TLS connection required';
        }
        
        // Test authentication
        if (!$this->testAuthentication($credentials)) {
            $errors[] = 'Authentication failed';
        }
        
        // Check rate limits
        if (!$this->checkRateLimits($credentials['host'])) {
            $errors[] = 'Rate limit exceeded for this host';
        }
        
        return $errors;
    }
}
```

## API Endpoints

### Email Account Management

```php
// routes/api.php
Route::prefix('email-accounts')->group(function () {
    Route::get('/', [EmailAccountController::class, 'index']);
    Route::post('/', [EmailAccountController::class, 'store']);
    Route::get('/{account}', [EmailAccountController::class, 'show']);
    Route::put('/{account}', [EmailAccountController::class, 'update']);
    Route::delete('/{account}', [EmailAccountController::class, 'destroy']);
    Route::post('/{account}/test', [EmailAccountController::class, 'testConnection']);
    Route::post('/{account}/sync', [EmailAccountController::class, 'triggerSync']);
});

Route::prefix('email-rules')->group(function () {
    Route::get('/', [EmailRuleController::class, 'index']);
    Route::post('/', [EmailRuleController::class, 'store']);
    Route::get('/{rule}', [EmailRuleController::class, 'show']);
    Route::put('/{rule}', [EmailRuleController::class, 'update']);
    Route::delete('/{rule}', [EmailRuleController::class, 'destroy']);
    Route::post('/{rule}/test', [EmailRuleController::class, 'testRule']);
});
```

### Controller Examples

```php
class EmailAccountController extends Controller
{
    public function store(StoreEmailAccountRequest $request): JsonResponse
    {
        $quotaService = app(EmailQuotaService::class);
        
        if (!$quotaService->canAddEmailAccount(auth()->user()->currentTenant)) {
            return response()->json(['error' => 'Email account limit reached'], 403);
        }
        
        $account = EmailAccount::create([
            'tenant_id' => auth()->user()->currentTenant->id,
            'name' => $request->name,
            'provider' => $request->provider,
            'credentials' => $request->credentials,
            'is_active' => true
        ]);
        
        return response()->json($account, 201);
    }
    
    public function testConnection(EmailAccount $account): JsonResponse
    {
        $emailService = app(EmailServiceFactory::class)->create($account);
        
        try {
            $isConnected = $emailService->testConnection();
            return response()->json(['connected' => $isConnected]);
        } catch (Exception $e) {
            return response()->json(['connected' => false, 'error' => $e->getMessage()]);
        }
    }
    
    public function triggerSync(EmailAccount $account): JsonResponse
    {
        FetchEmailsJob::dispatch($account);
        
        return response()->json(['message' => 'Sync job queued']);
    }
}
```

## Testing Strategy

### Unit Tests

```php
class EmailRuleEngineTest extends TestCase
{
    public function test_matches_sender_pattern(): void
    {
        $rule = EmailRule::factory()->create([
            'conditions' => [
                'sender_patterns' => ['*@vendor.com']
            ]
        ]);
        
        $engine = new EmailRuleEngine();
        $messageData = ['sender' => 'invoices@vendor.com'];
        
        $this->assertTrue($engine->matchesRule($messageData, $rule));
    }
    
    public function test_requires_all_conditions(): void
    {
        $rule = EmailRule::factory()->create([
            'conditions' => [
                'sender_patterns' => ['*@vendor.com'],
                'subject_patterns' => ['*invoice*']
            ]
        ]);
        
        $engine = new EmailRuleEngine();
        
        // Should fail if only one condition matches
        $messageData = [
            'sender' => 'invoices@vendor.com',
            'subject' => 'Hello world'
        ];
        
        $this->assertFalse($engine->matchesRule($messageData, $rule));
    }
}
```

### Integration Tests

```php
class EmailProcessingIntegrationTest extends TestCase
{
    public function test_full_email_to_invoice_flow(): void
    {
        $tenant = Tenant::factory()->create();
        $account = EmailAccount::factory()->for($tenant)->create();
        $rule = EmailRule::factory()->for($account)->create();
        
        $messageData = [
            'message_id' => 'test-123',
            'sender' => 'vendor@example.com',
            'subject' => 'Invoice #12345',
            'body' => 'Please find attached invoice',
            'attachments' => [
                [
                    'filename' => 'invoice.pdf',
                    'content' => 'fake-pdf-content'
                ]
            ]
        ];
        
        ProcessEmailJob::dispatchSync($account, $messageData);
        
        $this->assertDatabaseHas('invoices', [
            'tenant_id' => $tenant->id,
            'status' => 'draft'
        ]);
        
        $this->assertDatabaseHas('email_messages', [
            'email_account_id' => $account->id,
            'message_id' => 'test-123',
            'status' => 'processed'
        ]);
    }
}
```

## Implementation Checklist

### Phase 1: Core Infrastructure
- [ ] Create database migrations for all models
- [ ] Implement EmailAccount model with encrypted credentials
- [ ] Implement EmailRule model with JSON conditions/actions
- [ ] Create EmailMessage model for duplicate prevention
- [ ] Build Pop3EmailService with IMAP functions
- [ ] Implement EmailRuleEngine with AND-only logic
- [ ] Create basic queue jobs structure

### Phase 2: Email Processing
- [ ] Build EmailSyncJob for periodic execution
- [ ] Implement FetchEmailsJob with POP3 connection
- [ ] Create ProcessEmailJob with rule matching
- [ ] Build ExtractInvoiceDataJob with OCR integration
- [ ] Integrate with existing Media Library for attachments
- [ ] Implement custom path generator for email attachments

### Phase 3: Business Logic
- [ ] Build DuplicateInvoiceDetector service
- [ ] Implement EmailQuotaService with subscription limits
- [ ] Create notification classes for success/failure
- [ ] Integrate with existing periodic tasks system
- [ ] Build API endpoints for account/rule management

### Phase 4: Security & Testing
- [ ] Implement credential encryption/decryption
- [ ] Add connection validation and testing
- [ ] Create comprehensive unit tests
- [ ] Build integration tests for full flow
- [ ] Add rate limiting and error handling
- [ ] Implement audit logging

### Phase 5: Frontend Integration
- [ ] Create email account management UI
- [ ] Build rule builder interface with visual editor
- [ ] Add connection testing functionality
- [ ] Implement sync status and history viewing
- [ ] Create notification preferences interface

## Configuration Examples

### Email Account Configuration
```json
{
  "name": "Main Accounting Email",
  "provider": "pop3",
  "credentials": {
    "host": "pop.gmail.com",
    "port": 995,
    "username": "accounting@company.com",
    "password": "app-specific-password"
  },
  "is_active": true
}
```

### Email Rule Configuration
```json
{
  "name": "Vendor ABC Invoices",
  "priority": 100,
  "conditions": {
    "sender_patterns": ["*@vendorabc.com", "billing@vendorabc.com"],
    "subject_patterns": ["*invoice*", "*bill*"],
    "keywords": ["payment due", "amount"],
    "attachment_required": true,
    "attachment_extensions": ["pdf"],
    "exclude_keywords": ["quote", "estimate"]
  },
  "actions": {
    "import_as_draft": true,
    "contractor_id": null,
    "auto_approve": false,
    "notification_enabled": true,
    "default_invoice_type": "basic"
  },
  "is_active": true
}
```

This specification provides a complete foundation for implementing email invoice integration with your existing SaaS system. The design leverages your current architecture while adding the necessary components for automated email processing and invoice import.
