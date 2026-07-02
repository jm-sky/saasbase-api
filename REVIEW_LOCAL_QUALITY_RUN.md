# Lokalna weryfikacja jakości (PHPStan + PHPUnit)

**Data:** 2026-07-02 (wieczór, po `git pull`)  
**Branch:** `claude/saasbase-project-review-0p5z11`  
**Commit (HEAD po pull):** `d8057bc`  
**Kontener:** `./vendor/bin/sail exec -T laravel.test` (serwis `laravel.test`)  
**Baza testowa:** PostgreSQL `testing` (Sail)

## Podsumowanie

| Komenda | Skrypt Composer | Exit code | Wynik |
|---------|-----------------|-----------|--------|
| PHPStan | `composer larastan` (`composer analyse` nie istnieje w `composer.json`) | **1** | **3 błędy** |
| PHPUnit | `php artisan test` | **2** | **251 failed, 55 passed** (634.54 s) |

Pełne logi surowe:

- [`REVIEW_LOCAL_QUALITY_RUN/phpstan.txt`](REVIEW_LOCAL_QUALITY_RUN/phpstan.txt)
- [`REVIEW_LOCAL_QUALITY_RUN/artisan-test.txt`](REVIEW_LOCAL_QUALITY_RUN/artisan-test.txt)

## PHPStan (Larastan)

**Konfiguracja:** `phpstan.neon`  
**Pliki:** 1150 przeanalizowanych

**Błędy (3):** `app/Domain/Approval/Services/ApprovalResolutionService.php`

| Linia | Opis |
|-------|------|
| 142 | Call to an undefined method `Illuminate\Database\Eloquent\Relations\HasMany::active()` |
| 148 | j.w. |
| 198 | j.w. |

Prawdopodobna przyczyna: refaktor w ramach review brancha — relacja zwraca `HasMany`, a kod woła scope `active()` zdefiniowany na innym typie relacji / modelu.

## PHPUnit (`artisan test`)

**251 failed, 55 passed, 57 assertions, Duration: 634.54s**

### Dominująca przyczyna awarii

Migracja **`2025_07_10_000030_backfill_addresses_and_bank_accounts_tenant_id.php`** (dodana w ostatnich commitach review) odwołuje się do tabeli `contacts`, która **nie istnieje** w świeżej bazie testowej po migracjach:

```
SQLSTATE[42P01]: Undefined table: relation "contacts" does not exist
LINE 3:     FROM contacts AS owner
```

Wywołanie: `backfillFromOwner(..., Contact::class, 'contacts')` w `up()` — linie 39, 53.

Efekt: `RefreshDatabase` w `tests/TestCase.php` nie kończy migracji → **~251 testów pada już w `setUp()`** z `QueryException`, zanim dojdzie do asercji biznesowych.

### Testy, które przeszły (55)

Głównie testy **bez** pełnego cyklu DB przez `RefreshDatabase` albo czysto jednostkowe bez migracji, m.in.:

- `ApprovalResolutionServiceSimpleTest` (tylko `assertInstanceOf` / `method_exists`)
- `ValidAdvancedFilterRuleTest`
- `DataComparatorServiceTest` (mocki, bez DB)
- część innych testów jednostkowych bez pełnego seeda

### Uwagi środowiskowe

- Ostrzeżenia PostgreSQL: `database "testing" has no actual collation version` — kosmetyka, nie blokuje testów.
- W kontenerze: `git dubious ownership` przy `composer` — nie blokuje analizy.
- Lokalnie `.env.testing` musi mieć `DB_PASSWORD` zgodne z `.env` (TCP z `laravel.test` → `pgsql`); domyślne `secret` z repo nie pasuje do instancji Postgres z niestandardowym hasłem dev.

## Wnioski dla merge / review

1. **PHPStan:** naprawić 3 błędy w `ApprovalResolutionService` przed merge (scope `active()` na relacji).
2. **Migracja backfill:** warunkować backfill `Contact` / sprawdzić `Schema::hasTable('contacts')` albo poprawić kolejność/nazwę tabeli — **inaczej CI i lokalne testy pozostaną czerwone**.
3. **Pełny `artisan test` uruchomiony po raz pierwszy na tym branchu** — wcześniejszy run (2026-07-02 rano) obejmował tylko CS Fixer + PHPStan bez PHPUnit.

## Komendy odtwarzające

```bash
./vendor/bin/sail exec -T laravel.test composer larastan \
  2>&1 | tee REVIEW_LOCAL_QUALITY_RUN/phpstan.txt

./vendor/bin/sail artisan test \
  2>&1 | tee REVIEW_LOCAL_QUALITY_RUN/artisan-test.txt
```

## Historia

| Data | Zakres | Wynik |
|------|--------|--------|
| 2026-07-02 (rano) | `composer csf` + `composer larastan` | CSF: 4 pliki auto-fix; PHPStan OK (1149 plików) — **przed** ostatnimi commitami z Fazy 2 |
| 2026-07-02 (wieczór) | `composer larastan` + `artisan test` po `git pull` | PHPStan: 3 błędy; PHPUnit: 251 failed / 55 passed |
