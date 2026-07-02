# Lokalna weryfikacja jakości (PHPStan + PHPUnit)

**Data:** 2026-07-02 (wieczór, rerun po migracji `contacts`)  
**Branch:** `claude/saasbase-project-review-0p5z11`  
**Kontener:** `./vendor/bin/sail exec -T laravel.test` (serwis `laravel.test`)  
**Baza testowa:** PostgreSQL `testing` (Sail)

## Podsumowanie (ostatni run)

| Komenda | Skrypt Composer | Exit code | Wynik |
|---------|-----------------|-----------|--------|
| PHPStan | `composer larastan` | **1** | **3 błędy** (bez zmian vs poprzedni run) |
| PHPUnit | `php artisan test` | **2** | **6 failed, 269 passed, 31 skipped** (40.00 s) |

Pełne logi surowe:

- [`REVIEW_LOCAL_QUALITY_RUN/phpstan.txt`](REVIEW_LOCAL_QUALITY_RUN/phpstan.txt) — run z 2026-07-02 (wieczór, przed migracją)
- [`REVIEW_LOCAL_QUALITY_RUN/artisan-test.txt`](REVIEW_LOCAL_QUALITY_RUN/artisan-test.txt) — **rerun po dodaniu migracji `contacts`**

### Naprawa w tej sesji

Dodano brakującą migrację [`database/migrations/2024_04_14_000153_create_contacts_table.php`](../database/migrations/2024_04_14_000153_create_contacts_table.php) (chronologicznie po `contractor_contact_people`, przed backfillem `2025_07_10_000030_...`). Efekt: migracje kończą się poprawnie, testy przechodzą z **55 → 269 passed**.

## PHPStan (Larastan)

**Konfiguracja:** `phpstan.neon`  
**Pliki:** 1150 przeanalizowanych (run z wieczora przed rerunem testów)

**Błędy (3):** `app/Domain/Approval/Services/ApprovalResolutionService.php`

| Linia | Opis |
|-------|------|
| 142 | Call to an undefined method `Illuminate\Database\Eloquent\Relations\HasMany::active()` |
| 148 | j.w. |
| 198 | j.w. |

## PHPUnit (`artisan test`) — rerun 2026-07-02

**6 failed, 269 passed, 31 skipped, 1317 assertions, Duration: 40.00s**

### Pozostałe 6 failure (nie związane z brakiem tabeli `contacts`)

| Test | Przyczyna |
|------|-----------|
| `ProcessContractorRegistryConfirmationJobTest` | `Attempt to read property "status" on array` w `ProcessContractorRegistryConfirmationJob.php:155` — regresja po zmianie formatu `result` w jobie (Faza 1 fix) |
| `ApplicationInvitationControllerTest` ×2 (`accept`, `reject`) | 500 — `JWTException: Token could not be parsed` w `EnsureTwoFactorVerified` middleware; test używa `actingAs()` zamiast JWT |
| `SkillCategoryApiTest` ×3 (`create`, `update`, `delete`) | 403 — testy nie uwzględniają nowej autoryzacji Owner/Admin z review (Faza 3 Grupa A) |

### 31 skipped

Głównie testy EDoreczenia (`markTestSkipped`), Contact search (Scout), PKWiU/import i inne świadomie pominięte w suite.

### Uwagi środowiskowe

- Ostrzeżenia PostgreSQL: `database "testing" has no actual collation version` — kosmetyka.
- Lokalnie: jeśli `.env.testing` ma `DB_PASSWORD=secret`, a Postgres dev ma inne hasło — testy wymagają zgodności haseł dla połączeń TCP (`laravel.test` → `pgsql`). Rerun wykonano z `DB_PASSWORD` z `.env`.

## Wnioski dla merge / review

1. **Migracja `contacts`:** naprawiona — backfill `2025_07_10_000030_...` nie blokuje już `RefreshDatabase`.
2. **PHPStan:** nadal **3 błędy** w `ApprovalResolutionService` — do naprawy przed merge.
3. **PHPUnit:** **6 testów** do dostosowania (job registry / JWT w invitation tests / autoryzacja Skills) — nie blokuje migracji, ale suite nie jest w pełni zielony.
4. **Backfill na produkcji:** nadal przetestować na kopii realnych danych (`DISTINCT ON` / `user_tenants`).

## Komendy odtwarzające

```bash
./vendor/bin/sail exec -T laravel.test composer larastan \
  2>&1 | tee REVIEW_LOCAL_QUALITY_RUN/phpstan.txt

# lokalnie: upewnij się, że DB_PASSWORD w .env.testing = .env (lub przekaż env)
DB_PASS=$(grep '^DB_PASSWORD=' .env | cut -d= -f2-)
./vendor/bin/sail exec -T -e DB_PASSWORD="$DB_PASS" laravel.test php artisan test \
  2>&1 | tee REVIEW_LOCAL_QUALITY_RUN/artisan-test.txt
```

## Historia

| Data | Zakres | Wynik |
|------|--------|--------|
| 2026-07-02 (rano) | `composer csf` + `composer larastan` | CSF: 4 pliki auto-fix; PHPStan OK (1149 plików) — **przed** commitami Fazy 2 |
| 2026-07-02 (wieczór) | `composer larastan` + `artisan test` po `git pull` | PHPStan: 3 błędy; PHPUnit: **251 failed / 55 passed** (brak tabeli `contacts`) |
| 2026-07-02 (wieczór, rerun) | migracja `2024_04_14_000153_create_contacts_table` + `artisan test` | PHPUnit: **6 failed / 269 passed / 31 skipped** |
