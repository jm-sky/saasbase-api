<?php

namespace App\Domain\Expense\Controllers;

use App\Domain\Approval\Actions\ProcessApprovalDecisionAction;
use App\Domain\Approval\Actions\StartApprovalWorkflowAction;
use App\Domain\Approval\Enums\ApprovalDecision;
use App\Domain\Approval\Models\ApprovalExpenseExecution;
use App\Domain\Expense\Models\Expense;
use App\Domain\Expense\Requests\ProcessApprovalDecisionRequest;
use App\Domain\Expense\Resources\ApprovalExecutionResource;
use App\Domain\Expense\Resources\PendingApprovalsResource;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ExpenseApprovalController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private StartApprovalWorkflowAction $startAction,
        private ProcessApprovalDecisionAction $processAction
    ) {
    }

    /**
     * Get pending approvals for the current user.
     */
    public function pendingApprovals(Request $request): AnonymousResourceCollection
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user = Auth::user();

        // Get all pending executions where the user is an approver for the current step
        $pendingExecutions = ApprovalExpenseExecution::with([
            'expense',
            'workflow',
            'currentStep.approvers',
            'decisions.approver',
        ])
            ->pending()
            ->whereHas('currentStep', function ($query) use ($user) {
                $query->whereHas('approvers', function ($approverQuery) use ($user) {
                    // This is a simplified check - the actual logic should use ApprovalResolutionService
                    // For now, we'll include all pending approvals and filter them properly in the resource
                    $approverQuery->where('approver_type', 'user')
                        ->where('approver_value', $user->id)
                    ;
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('perPage', 15))
        ;

        return PendingApprovalsResource::collection($pendingExecutions);
    }

    /**
     * Get approval history for the current user.
     */
    public function approvalHistory(Request $request): AnonymousResourceCollection
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user = Auth::user();

        // Get all executions where the user has made decisions
        $executionsWithDecisions = ApprovalExpenseExecution::with([
            'expense',
            'workflow',
            'decisions' => function ($query) use ($user) {
                $query->where('approver_id', $user->id);
            },
            'decisions.step',
        ])
            ->whereHas('decisions', function ($query) use ($user) {
                $query->where('approver_id', $user->id);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate($request->get('perPage', 15))
        ;

        return ApprovalExecutionResource::collection($executionsWithDecisions);
    }

    /**
     * Get approval details for a specific expense.
     */
    public function show(Expense $expense): JsonResponse
    {
        $execution = $expense->approvalExecution()
            ->with([
                'workflow',
                'currentStep.approvers',
                'decisions.approver',
                'decisions.step',
            ])
            ->first()
        ;

        if (!$execution) {
            return response()->json([
                'message' => 'No approval workflow found for this expense',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new ApprovalExecutionResource($execution),
        ]);
    }

    /**
     * Start approval workflow for an expense.
     */
    public function startApproval(Expense $expense): JsonResponse
    {
        $this->authorize('update', $expense);

        /** @var \App\Domain\Auth\Models\User $user */
        $user = Auth::user();

        if (!$this->startAction->canStartApproval($expense)) {
            return response()->json([
                'message' => 'Cannot start approval for this expense',
                'reason'  => $this->startAction->getCannotStartReason($expense),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $execution = $this->startAction->execute($expense, $user->id);

            if (!$execution) {
                return response()->json([
                    'message' => 'Expense was auto-approved (no workflow required)',
                    'data'    => [
                        'autoApproved'   => true,
                        'approvalStatus' => $expense->fresh()->approval_status->value,
                    ],
                ]);
            }

            return response()->json([
                'message' => 'Approval workflow started successfully',
                'data'    => new ApprovalExecutionResource($execution->load([
                    'workflow',
                    'currentStep.approvers',
                    'decisions.approver',
                ])),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to start approval workflow',
                'error'   => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Process an approval decision (approve or reject).
     */
    public function processDecision(ProcessApprovalDecisionRequest $request, Expense $expense): JsonResponse
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user = Auth::user();

        /** @var ApprovalExpenseExecution $execution */
        $execution = $expense->approvalExecution()->with(['workflow', 'currentStep.approvers'])->pending()->first(); // @phpstan-ignore-line

        if (!$execution) {
            return response()->json([
                'message' => 'No pending approval workflow found for this expense',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$this->processAction->canUserMakeDecision($execution, $user)) {
            return response()->json([
                'message' => 'You are not authorized to make a decision on this approval',
                'reason'  => $this->processAction->getCannotDecideReason($execution, $user),
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();

        try {
            $decision = ApprovalDecision::from($validated['decision']);
            $notes    = $validated['notes'] ?? null;

            $decisionRecord = $this->processAction->execute($execution, $user, $decision, $notes);

            // Reload execution with fresh data
            $execution->refresh();
            $execution->load([
                'workflow',
                'currentStep.approvers',
                'decisions.approver',
                'decisions.step',
                'expense',
            ]);

            return response()->json([
                'message' => 'Decision recorded successfully',
                'data'    => [
                    'decision'  => [
                        'id'        => $decisionRecord->id,
                        'decision'  => $decisionRecord->decision->value,
                        'notes'     => $decisionRecord->notes,
                        'decidedAt' => $decisionRecord->decided_at->toIso8601String(),
                    ],
                    'execution' => new ApprovalExecutionResource($execution),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Invalid decision',
                'error'   => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process decision',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check if current user can approve a specific expense.
     */
    public function canApprove(Expense $expense): JsonResponse
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user = Auth::user();

        /** @var ApprovalExpenseExecution $execution */
        $execution = $expense->approvalExecution()->with(['workflow', 'currentStep.approvers'])->pending()->first(); // @phpstan-ignore-line

        if (!$execution) {
            return response()->json([
                'data' => [
                    'canApprove' => false,
                    'reason'     => 'No pending approval workflow found',
                ],
            ]);
        }

        $canApprove = $this->processAction->canUserMakeDecision($execution, $user);
        $reason     = $canApprove ? null : $this->processAction->getCannotDecideReason($execution, $user);

        return response()->json([
            'data' => [
                'canApprove' => $canApprove,
                'reason'     => $reason,
                'execution'  => [
                    'id'              => $execution->id,
                    'status'          => $execution->status->value,
                    'currentStepName' => $execution->currentStep?->name,
                ],
            ],
        ]);
    }
}
