<?php

// ============================================================================
// 1. Intent Classification Service
// ============================================================================

namespace App\Domain\Ai\Services;

use App\Domain\Ai\DTOs\IntentResult;
use Illuminate\Support\Facades\Http;

class IntentClassificationService
{
    public function __construct(
        private OpenRouterService $openRouterService
    ) {}

    public function classifyIntent(string $message): IntentResult
    {
        $systemPrompt = "
        You are an intent classifier for a business management application.
        Analyze the user's message and classify it into one of these intents:

        INTENTS:
        - CREATE_INVOICE: Create new invoices (e.g., 'Create invoice for ABC Corp for $500')
        - CREATE_EXPENSE: Create new expenses (e.g., 'Add expense for office supplies $50')
        - SHOW_INVOICES: Display invoices (e.g., 'Show my invoices', 'List all invoices')
        - SHOW_EXPENSES: Display expenses (e.g., 'Show expenses', 'What are my expenses?')
        - SHOW_CONTRACTORS: Display contractors (e.g., 'Show contractors', 'List suppliers')
        - SHOW_PRODUCTS: Display products (e.g., 'Show products', 'List my products')
        - CHANGE_ROUTE: Navigate to different pages (e.g., 'Go to dashboard', 'Open projects')
        - GENERAL_CHAT: General conversation or unclear intent

        ENTITY EXTRACTION:
        For CREATE_INVOICE: Extract client_name, amount, description
        For CREATE_EXPENSE: Extract vendor_name, amount, description, category
        For SHOW_* actions: Extract filters like date_range, status, amount_range
        For CHANGE_ROUTE: Extract target_page

        Return ONLY valid JSON in this format:
        {
            \"intent\": \"CREATE_INVOICE\",
            \"confidence\": 0.95,
            \"entities\": {
                \"client_name\": \"ABC Corp\",
                \"amount\": 500,
                \"description\": \"Web development services\"
            }
        }
        ";

        try {
            $response = $this->openRouterService->sendMessage($systemPrompt, $message);
            $decoded = json_decode($response, true);
            
            return new IntentResult(
                intent: $decoded['intent'] ?? 'GENERAL_CHAT',
                confidence: $decoded['confidence'] ?? 0.0,
                entities: $decoded['entities'] ?? []
            );
        } catch (\Exception $e) {
            // Fallback to general chat on error
            return new IntentResult('GENERAL_CHAT', 0.0, []);
        }
    }
}

// ============================================================================
// 2. Intent Result DTO
// ============================================================================

namespace App\Domain\Ai\DTOs;

class IntentResult
{
    public function __construct(
        public string $intent,
        public float $confidence,
        public array $entities = []
    ) {}

    public function requiresAction(): bool
    {
        return $this->intent !== 'GENERAL_CHAT' && $this->confidence > 0.7;
    }

    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'confidence' => $this->confidence,
            'entities' => $this->entities
        ];
    }
}

// ============================================================================
// 3. AI Response DTO
// ============================================================================

namespace App\Domain\Ai\DTOs;

class AiResponse
{
    public function __construct(
        public string $type,
        public array $data = [],
        public string $message = '',
        public ?string $route = null,
        public bool $success = true
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'message' => $this->message,
            'route' => $this->route,
            'success' => $this->success
        ];
    }
}

// ============================================================================
// 4. Action Interface
// ============================================================================

namespace App\Domain\Ai\Contracts;

use App\Domain\Ai\DTOs\IntentResult;
use App\Domain\Ai\DTOs\AiResponse;
use App\Domain\Auth\Models\User;

interface AiActionInterface
{
    public function canHandle(IntentResult $intent): bool;
    public function execute(IntentResult $intent, User $user): AiResponse;
}

// ============================================================================
// 5. Create Invoice Action
// ============================================================================

namespace App\Domain\Ai\Actions;

use App\Domain\Ai\Contracts\AiActionInterface;
use App\Domain\Ai\DTOs\IntentResult;
use App\Domain\Ai\DTOs\AiResponse;
use App\Domain\Auth\Models\User;
use App\Domain\Invoice\Services\InvoiceService;
use App\Domain\Contractor\Services\ContractorService;

class CreateInvoiceAction implements AiActionInterface
{
    public function __construct(
        private InvoiceService $invoiceService,
        private ContractorService $contractorService
    ) {}

    public function canHandle(IntentResult $intent): bool
    {
        return $intent->intent === 'CREATE_INVOICE';
    }

    public function execute(IntentResult $intent, User $user): AiResponse
    {
        $entities = $intent->entities;
        
        try {
            // Find or create contractor
            $contractor = null;
            if (!empty($entities['client_name'])) {
                $contractor = $this->contractorService->findOrCreate(
                    $entities['client_name'],
                    $user->tenant_id
                );
            }

            // Create invoice
            $invoiceData = [
                'tenant_id' => $user->tenant_id,
                'contractor_id' => $contractor?->id,
                'total_gross' => $entities['amount'] ?? 0,
                'description' => $entities['description'] ?? 'AI Generated Invoice',
                'status' => 'draft',
                'created_by' => $user->id,
                'issue_date' => now(),
                'due_date' => now()->addDays(30),
            ];

            $invoice = $this->invoiceService->create($invoiceData);

            return new AiResponse(
                type: 'invoice_created',
                data: [
                    'invoice' => $invoice->load('contractor'),
                    'redirect_url' => "/invoices/{$invoice->id}"
                ],
                message: "Invoice #{$invoice->number} created successfully for " . 
                        ($contractor?->name ?? 'New Client') . 
                        " - Amount: $" . number_format($entities['amount'] ?? 0, 2)
            );

        } catch (\Exception $e) {
            return new AiResponse(
                type: 'error',
                message: 'Failed to create invoice: ' . $e->getMessage(),
                success: false
            );
        }
    }
}

// ============================================================================
// 6. Create Expense Action
// ============================================================================

namespace App\Domain\Ai\Actions;

use App\Domain\Ai\Contracts\AiActionInterface;
use App\Domain\Ai\DTOs\IntentResult;
use App\Domain\Ai\DTOs\AiResponse;
use App\Domain\Auth\Models\User;
use App\Domain\Expense\Services\ExpenseService;

class CreateExpenseAction implements AiActionInterface
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    public function canHandle(IntentResult $intent): bool
    {
        return $intent->intent === 'CREATE_EXPENSE';
    }

    public function execute(IntentResult $intent, User $user): AiResponse
    {
        $entities = $intent->entities;
        
        try {
            $expenseData = [
                'tenant_id' => $user->tenant_id,
                'vendor_name' => $entities['vendor_name'] ?? 'Unknown Vendor',
                'total_gross' => $entities['amount'] ?? 0,
                'description' => $entities['description'] ?? 'AI Generated Expense',
                'category' => $entities['category'] ?? 'General',
                'status' => 'draft',
                'created_by' => $user->id,
                'expense_date' => now(),
            ];

            $expense = $this->expenseService->create($expenseData);

            return new AiResponse(
                type: 'expense_created',
                data: [
                    'expense' => $expense,
                    'redirect_url' => "/expenses/{$expense->id}"
                ],
                message: "Expense created successfully for {$entities['vendor_name']} - Amount: $" . 
                        number_format($entities['amount'] ?? 0, 2)
            );

        } catch (\Exception $e) {
            return new AiResponse(
                type: 'error',
                message: 'Failed to create expense: ' . $e->getMessage(),
                success: false
            );
        }
    }
}

// ============================================================================
// 7. Show Data Actions
// ============================================================================

namespace App\Domain\Ai\Actions;

use App\Domain\Ai\Contracts\AiActionInterface;
use App\Domain\Ai\DTOs\IntentResult;
use App\Domain\Ai\DTOs\AiResponse;
use App\Domain\Auth\Models\User;
use App\Domain\Invoice\Models\Invoice;
use App\Domain\Expense\Models\Expense;
use App\Domain\Contractor\Models\Contractor;
use App\Domain\Product\Models\Product;

class ShowInvoicesAction implements AiActionInterface
{
    public function canHandle(IntentResult $intent): bool
    {
        return $intent->intent === 'SHOW_INVOICES';
    }

    public function execute(IntentResult $intent, User $user): AiResponse
    {
        $entities = $intent->entities;
        
        $query = Invoice::where('tenant_id', $user->tenant_id)
            ->with(['contractor', 'creator']);

        // Apply filters based on entities
        if (!empty($entities['status'])) {
            $query->where('status', $entities['status']);
        }
        
        if (!empty($entities['date_range'])) {
            $query->whereBetween('created_at', [
                now()->subDays(30), now()
            ]);
        }

        $invoices = $query->latest()->limit(50)->get();

        return new AiResponse(
            type: 'show_invoices',
            data: [
                'invoices' => $invoices,
                'total_count' => $invoices->count(),
                'total_amount' => $invoices->sum('total_gross'),
                'filters_applied' => $entities
            ],
            message: "Found {$invoices->count()} invoices"
        );
    }
}

class ShowExpensesAction implements AiActionInterface
{
    public function canHandle(IntentResult $intent): bool
    {
        return $intent->intent === 'SHOW_EXPENSES';
    }

    public function execute(IntentResult $intent, User $user): AiResponse
    {
        $entities = $intent->entities;
        
        $query = Expense::where('tenant_id', $user->tenant_id)
            ->with(['creator']);

        if (!empty($entities['category'])) {
            $query->where('category', 'like', '%' . $entities['category'] . '%');
        }

        $expenses = $query->latest()->limit(50)->get();

        return new AiResponse(
            type: 'show_expenses',
            data: [
                'expenses' => $expenses,
                'total_count' => $expenses->count(),
                'total_amount' => $expenses->sum('total_gross')
            ],
            message: "Found {$expenses->count()} expenses"
        );
    }
}

class ShowContractorsAction implements AiActionInterface
{
    public function canHandle(IntentResult $intent): bool
    {
        return $intent->intent === 'SHOW_CONTRACTORS';
    }

    public function execute(IntentResult $intent, User $user): AiResponse
    {
        $contractors = Contractor::where('tenant_id', $user->tenant_id)
            ->with(['addresses', 'bankAccounts'])
            ->latest()
            ->limit(50)
            ->get();

        return new AiResponse(
            type: 'show_contractors',
            data: [
                'contractors' => $contractors,
                'total_count' => $contractors->count()
            ],
            message: "Found {$contractors->count()} contractors"
        );
    }
}

class ShowProductsAction implements AiActionInterface
{
    public function canHandle(IntentResult $intent): bool
    {
        return $intent->intent === 'SHOW_PRODUCTS';
    }

    public function execute(IntentResult $intent, User $user): AiResponse
    {
        // Assuming you have a Product model
        $products = Product::where('tenant_id', $user->tenant_id)
            ->latest()
            ->limit(50)
            ->get();

        return new AiResponse(
            type: 'show_products',
            data: [
                'products' => $products,
                'total_count' => $products->count()
            ],
            message: "Found {$products->count()} products"
        );
    }
}

// ============================================================================
// 8. Change Route Action
// ============================================================================

namespace App\Domain\Ai\Actions;

use App\Domain\Ai\Contracts\AiActionInterface;
use App\Domain\Ai\DTOs\IntentResult;
use App\Domain\Ai\DTOs\AiResponse;
use App\Domain\Auth\Models\User;

class ChangeRouteAction implements AiActionInterface
{
    private array $routeMap = [
        'dashboard' => '/dashboard',
        'invoices' => '/invoices',
        'expenses' => '/expenses',
        'contractors' => '/contractors',
        'products' => '/products',
        'projects' => '/projects',
        'tasks' => '/tasks',
        'profile' => '/profile',
        'settings' => '/settings',
    ];

    public function canHandle(IntentResult $intent): bool
    {
        return $intent->intent === 'CHANGE_ROUTE';
    }

    public function execute(IntentResult $intent, User $user): AiResponse
    {
        $entities = $intent->entities;
        $targetPage = strtolower($entities['target_page'] ?? '');
        
        // Find matching route
        $route = null;
        foreach ($this->routeMap as $key => $path) {
            if (str_contains($targetPage, $key)) {
                $route = $path;
                break;
            }
        }

        if (!$route) {
            return new AiResponse(
                type: 'error',
                message: "I couldn't find the page '{$targetPage}'. Available pages: " . 
                        implode(', ', array_keys($this->routeMap)),
                success: false
            );
        }

        return new AiResponse(
            type: 'route_change',
            route: $route,
            message: "Navigating to {$targetPage}..."
        );
    }
}

// ============================================================================
// 9. Action Dispatcher
// ============================================================================

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Contracts\AiActionInterface;
use App\Domain\Ai\DTOs\IntentResult;
use App\Domain\Ai\DTOs\AiResponse;
use App\Domain\Auth\Models\User;

class ActionDispatcher
{
    private array $actions;

    public function __construct()
    {
        $this->actions = [
            app(\App\Domain\Ai\Actions\CreateInvoiceAction::class),
            app(\App\Domain\Ai\Actions\CreateExpenseAction::class),
            app(\App\Domain\Ai\Actions\ShowInvoicesAction::class),
            app(\App\Domain\Ai\Actions\ShowExpensesAction::class),
            app(\App\Domain\Ai\Actions\ShowContractorsAction::class),
            app(\App\Domain\Ai\Actions\ShowProductsAction::class),
            app(\App\Domain\Ai\Actions\ChangeRouteAction::class),
        ];
    }

    public function dispatch(IntentResult $intent, User $user): AiResponse
    {
        foreach ($this->actions as $action) {
            if ($action->canHandle($intent)) {
                return $action->execute($intent, $user);
            }
        }

        return new AiResponse(
            type: 'error',
            message: 'No action handler found for intent: ' . $intent->intent,
            success: false
        );
    }
}

// ============================================================================
// 10. Enhanced AI Chat Controller
// ============================================================================

namespace App\Domain\Ai\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Ai\Services\IntentClassificationService;
use App\Domain\Ai\Services\ActionDispatcher;
use App\Domain\Ai\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiChatController extends Controller
{
    public function __construct(
        private IntentClassificationService $intentService,
        private ActionDispatcher $actionDispatcher,
        private OpenRouterService $openRouterService
    ) {}

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = $request->input('message');
        $user = auth()->user();

        try {
            // Classify the intent
            $intent = $this->intentService->classifyIntent($message);

            // Check if this requires an action
            if ($intent->requiresAction()) {
                $response = $this->actionDispatcher->dispatch($intent, $user);
                
                return response()->json([
                    'type' => 'action_response',
                    'intent' => $intent->toArray(),
                    'response' => $response->toArray()
                ]);
            }

            // Fall back to regular chat
            $chatResponse = $this->handleGeneralChat($message, $user);
            
            return response()->json([
                'type' => 'chat_response',
                'message' => $chatResponse
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Sorry, I encountered an error processing your request.',
                'debug' => app()->hasDebugModeEnabled() ? $e->getMessage() : null
            ], 500);
        }
    }

    private function handleGeneralChat(string $message, $user): string
    {
        $context = $this->buildContext($user);
        
        $systemPrompt = "
        You are a helpful AI assistant for a business management application.
        
        Current context:
        - User: {$user->name}
        - Company: {$user->tenant->name}
        - Available actions: Create invoices/expenses, Show data, Navigate pages
        
        Respond helpfully and suggest specific actions the user can take.
        Keep responses concise and actionable.
        ";

        return $this->openRouterService->sendMessage($systemPrompt, $message);
    }

    private function buildContext($user): array
    {
        return [
            'user_name' => $user->name,
            'tenant_name' => $user->tenant->name,
            'recent_invoices_count' => $user->tenant->invoices()->recent()->count(),
            'pending_tasks_count' => $user->tenant->tasks()->pending()->count(),
        ];
    }

    public function stopStreaming(Request $request): JsonResponse
    {
        // Implementation for stopping streaming responses
        return response()->json(['status' => 'stopped']);
    }
}

// ============================================================================
// 11. Service Provider Registration
// ============================================================================

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Ai\Services\IntentClassificationService;
use App\Domain\Ai\Services\ActionDispatcher;

class AiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(IntentClassificationService::class);
        $this->app->singleton(ActionDispatcher::class);
    }

    public function boot()
    {
        //
    }
}

// ============================================================================
// 12. Routes (add to routes/api.php)
// ============================================================================

/*
Route::middleware('auth:api')->prefix('ai')->group(function () {
    Route::post('chat', [AiChatController::class, 'chat']);
    Route::post('chat/stop', [AiChatController::class, 'stopStreaming']);
});
*/
