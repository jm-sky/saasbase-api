# Plan przeglądu SaaSBase (backend + frontend + integracje)

**Data utworzenia:** 2026-07-02
**Model wykonawczy:** Claude Sonnet 5
**Repozytoria:** `jm-sky/saasbase-api` (Laravel/DDD), `jm-sky/saasbase-web` (Vue 3/TS)
**Branch:** `claude/saasbase-project-review-0p5z11`

## Kontekst / diagnoza wyjściowa

Projekt pisany w dużej mierze z pomocą AI przez ok. rok, solo, pod presją czasu. Modele sprzed roku
wymagały więcej kontroli człowieka niż obecne — część rzeczy była poprawiana "łata na łacie". Architektura
(DDD/hexagonal, generator `make:domain-model`, PHPStan, PHP-CS-Fixer) jest sensowna i spójna — problem leży
w nierównym dokończeniu funkcji i niskim pokryciu testami (49 plików testów na 969 plików PHP), a nie w
złym wyborze frameworka. Decyzja: **nie przepisujemy od zera**, robimy pełny review każdej domeny i każdej
integracji, żeby zlokalizować i naprawić realne problemy.

## Metodologia

Domena po domenie, w blokach po 3-4 równolegle (subagenty), każda domena dostaje ten sam checklist.
Na końcu osobny etap syntezy — spójność wzorców i przepływów między domenami, którego nie da się złapać
patrząc na jedną domenę naraz. Wyniki każdej fazy zapisywane jako findings (per domena/integracja),
zbiorczo podsumowywane na koniec fazy.

---

## Checklist — domena (backend)

1. **Zgodność z architekturą DDD** — Actions/Controllers/DTOs/Enums/Models/Requests/Resources/Services/Traits, odstępstwa od wzorca
2. **Multi-tenancy** — `TenantScope`/`BelongsToTenant` wszędzie gdzie trzeba; bezpieczeństwo `Tenant::bypassTenant()`
3. **Autoryzacja** — Policy istnieją i są faktycznie egzekwowane (nie tylko zdefiniowane)
4. **Walidacja** — kompletność FormRequests, edge case'y (null, puste stringi, limity, typy)
5. **Integralność danych** — FK, kaskady, unikalność, transakcje
6. **Pokrycie testami** — realne czy pozorne
7. **Ślady "AI na szybko"** — martwy kod, nieużywane importy, sprzeczne konwencje, TODO/FIXME, duplikacja logiki
8. **Zgodność z README** — czy featury `[x]` faktycznie działają end-to-end

## Checklist — integracja (rozszerzony, wyższe ryzyko)

Wszystko z listy domenowej +

9. **Credentiale** — przechowywanie per-tenant, szyfrowanie w spoczynku, brak w logach/exceptions
10. **Odporność na awarię** — timeout/429/5xx zewnętrznego API — retry, fallback, komunikat dla usera
11. **Walidacja odpowiedzi zewnętrznej** — dane z REGON/VIES/KSeF/Stripe walidowane przed zapisem
12. **Idempotencja** — webhooki Stripe, ponowne wysyłki do KSeF
13. **Audytowalność** — log każdej operacji finansowej/prawnej
14. **Zgodność RODO/prawna** — REGON/Biała Lista/KSeF jako systemy rządowe

## Checklist — frontend (dodatkowo do domenowej)

15. **Zgodność kontraktu z API** — typy/serwisy frontendowe zgadzają się z Resources/DTO backendu
16. **Obsługa błędów i stanów ładowania** — czy każdy serwis/store ma spójną obsługę
17. **i18n** — czy teksty faktycznie przechodzą przez `t()`, nie ma hardcoded stringów

---

## Fazy

### Faza 0 — Fundamenty
*Priorytet najwyższy: błąd tu unieważnia poprawność wszystkiego innego.*

- [ ] Multi-tenancy: `TenantScope`, `GlobalOrCurrentTenantScope`, `BelongsToTenant`, `Tenant::$BYPASSED_TENANT_ID`
- [ ] Bazowe klasy: `BaseModel`, `BaseDataDTO`, `BaseFormRequest`
- [ ] Auth (JWT, rejestracja, reset hasła, OAuth Google/GitHub)
- [ ] Rights (uprawnienia, role, stanowiska)
- [ ] Common (`HasIndexQuery`, `HasActivityLogging`, `HasMediaSignedUrls`)

### Faza 1 — Integracje zewnętrzne
*Najwyższe ryzyko: pieniądze, prawo, bezpieczeństwo.*

- [ ] Billing/Subscription → Stripe
- [ ] Financial → kursy walut, stawki VAT
- [ ] IbanInfo → IBAN API
- [ ] IdentityCheck → REGON, VIES, Biała Lista MF
- [ ] Invoice → KSeF
- [ ] EDoreczenia → e-doręczenia (eDO Post)
- [ ] Expense → Azure Document Intelligence (OCR)
- [ ] Ai → OpenRouter (chat AI)
- [ ] Exchanges → kursy wymiany

### Faza 2 — Domeny biznesowe core

- [ ] Contractors
- [ ] Invoice
- [ ] Expense
- [ ] Products
- [ ] Projects
- [ ] Tenant

### Faza 3 — Systemy wspierające

- [ ] Approval (workflow engine)
- [ ] Template (szablony faktur, PDF)
- [ ] ShareToken
- [ ] Feeds
- [ ] Chat
- [ ] Calendar
- [ ] Skills
- [ ] Export
- [ ] Admin
- [ ] Users

### Faza 4 — Frontend (lustro backendu + spójność kontraktu)

- [ ] auth, account, tenant
- [ ] invoice, expense, product, project, contractor
- [ ] financial, identityConfirmation, subscription
- [ ] chat, feed, task, tags, skill, comment, invitations
- [ ] rights, shared, utils

### Faza 5 — Synteza całości
*Osobny przebieg — wymaga trzymania całego obrazu naraz, nie da się per-domena.*

- [ ] Spójność wzorców między domenami — które odstają od konwencji generatora `make:domain-model`
- [ ] Przepływy end-to-end: faktura (utworzenie → szablon → PDF → KSeF → płatność)
- [ ] Przepływy end-to-end: wydatek (OCR → alokacja → approval → płatność)
- [ ] Mapa zależności między domenami (cykliczne/nieoczekiwane sprzężenia)
- [ ] Realna mapa pokrycia testami — gdzie są dziury
- [ ] Duplikacja logiki między domenami

---

## Status wykonania

| Faza | Status | Data | Notatki |
|------|--------|------|---------|
| 0 — Fundamenty | Nierozpoczęta | — | — |
| 1 — Integracje | Nierozpoczęta | — | — |
| 2 — Core biznesowy | Nierozpoczęta | — | — |
| 3 — Wspierające | Nierozpoczęta | — | — |
| 4 — Frontend | Nierozpoczęta | — | — |
| 5 — Synteza | Nierozpoczęta | — | — |

## Findings

*(uzupełniane w trakcie — każda faza dopisuje sekcję z listą problemów, posortowaną wg wagi)*
