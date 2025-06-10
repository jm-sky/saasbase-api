<?php

namespace App\Domain\IdentityCheck\Models;

use App\Domain\Common\Models\BaseModel;
use App\Domain\IdentityCheck\Enums\IdentityCheckMethod;
use App\Domain\IdentityCheck\Enums\IdentityCheckPurpose;
use App\Domain\IdentityCheck\Enums\IdentityCheckStatus;
use Carbon\Carbon;

/**
 * @property string               $id              Unique identifier for the identity check.
 * @property string               $verifiable_type Polymorphic target: `"User"` or `"Tenant"`.
 * @property string               $verifiable_id   ID of the user or tenant being verified.
 * @property IdentityCheckPurpose $purpose         What is being verified: `identity`, `official_data`, `ownership`.
 * @property IdentityCheckMethod  $method          How verification was performed (see "Verification Methods" below).
 * @property IdentityCheckStatus  $status          Current status: `pending`, `verified`, `rejected`.
 * @property ?Carbon              $verified_at     Timestamp when verification was successfully completed.
 * @property ?string              $verified_by     ID of the admin who verified it (if applicable).
 * @property ?string              $rejected_reason Reason for rejection, if verification failed.
 * @property array                $data            Captured metadata or evidence (e.g. matched names, registry links, confidence scores).
 * @property Carbon               $created_at      When the check was initiated.
 * @property Carbon               $updated_at      Last modification timestamp.
 *
 * Verification Methods:
 * - For Users:
 *   - `bank_transfer` — Verified using bank account name and number.
 *   - `edoreczenia` — Verified by sending a secure message via e-Doręczenia.
 *   - `epuap` — Verified via login or document issued through ePUAP.
 *   - `manual` — Manually verified by platform staff using submitted documents.
 * - For Tenants:
 *   - `bank_transfer` — Verified via bank account ownership matched with registry.
 *   - `ceidg` — Company data verified against CEIDG registry, optionally matched to a user.
 *   - `edoreczenia` — Verified via e-Doręczenia address associated with the tenant.
 *   - `epuap` — Verified via company-related ePUAP document.
 *   - `ksef_token` — Verified through successful authentication using KSeF token.
 *   - `manual` — Verified manually based on uploaded or researched documents.
 */
class IdentityCheck extends BaseModel
{
    protected $fillable = [
        'verifiable_type',
        'verifiable_id',
        'purpose',
        'method',
        'status',
        'verified_at',
        'verified_by',
        'rejected_reason',
        'data',
    ];

    protected $casts = [
        'status'      => IdentityCheckStatus::class,
        'purpose'     => IdentityCheckPurpose::class,
        'method'      => IdentityCheckMethod::class,
        'data'        => 'array',
        'verified_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];
}
