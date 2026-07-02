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

- [x] Multi-tenancy: `TenantScope`, `GlobalOrCurrentTenantScope`, `BelongsToTenant`, `Tenant::$BYPASSED_TENANT_ID`
- [x] Bazowe klasy: `BaseModel`, `BaseDataDTO`, `BaseFormRequest`
- [x] Auth (JWT, rejestracja, reset hasła, OAuth Google/GitHub)
- [x] Rights (uprawnienia, role, stanowiska)
- [x] Common (`HasIndexQuery`, `HasActivityLogging`, `HasMediaSignedUrls`)

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
| 0 — Fundamenty | **Ukończona — WYMAGA PILNEJ NAPRAWY** | 2026-07-02 | 6 critical, 6 high, ~10 medium/low. Wzorzec: autoryzacja nieegzekwowana w kilku miejscach; jeden przeciek tenant-log; multi-tenancy rdzeń OK poza bypassTenant |
| 1 — Integracje | W trakcie | 2026-07-02 | Płatności/finanse ukończone: 4 critical (w tym karta płatnicza w plaintext w logach + brak izolacji tenantów w Subscription). Rejestry/tożsamość + KSeF/OCR/AI w toku |
| 2 — Core biznesowy | Nierozpoczęta | — | — |
| 3 — Wspierające | Nierozpoczęta | — | — |
| 4 — Frontend | Nierozpoczęta | — | — |
| 5 — Synteza | Nierozpoczęta | — | — |

## Findings

*(uzupełniane w trakcie — każda faza dopisuje sekcję z listą problemów, posortowaną wg wagi)*

### Naprawione critical findings z Fazy 0 (2026-07-02)

Wszystkie 7 unikalnych critical findings z Fazy 0 naprawione bezpośrednio (nie przez agenty — ręcznie, z weryfikacją składni `php -l`):

1. **`Tenant::bypassTenant()`** — dodano `try/finally`, stan już nie "zatrzaskuje się" przy wyjątku.
2. **`TenantPolicy::update/delete`** — teraz wymaga roli Owner/Admin, nie tylko członkostwa.
3. **`RoleController`** — dodano autoryzację (Owner/Admin) w `store`/`update`/`destroy`; `index()` dodatkowo jawnie scope'owany tenantem (choć `Role` i tak ma `IsGlobalOrBelongsToTenant`, więc to była redundancja, nie luka).
4. **`TenantInvitationController::send`** — dodano autoryzację (Owner/Admin), `role` w `SendInvitationRequest` waliduje teraz przeciw `RoleName` enum zamiast dowolnego stringa.
5. **`ActivityLogController::index`** — `$query` z filtrem `tenant_id` jest teraz faktycznie przekazywany do `getIndexPaginator()`.
6. **2FA nieegzekwowane** — nowy middleware `mfa` (`EnsureTwoFactorVerified`) odrzuca żądania z tokenem `mfa=1` (2FA włączone, niezaliczone). Podpięty w głównych grupach tras (`routes/api.php`, `routes/api/user.php`, `routes/api/invitations.php`).
7. **OAuth account takeover** — `OAuthController::callback` linkuje tożsamość przez `OAuthAccount` (provider + provider_user_id) zamiast logować po samym e-mailu; jeśli e-mail już istnieje w systemie bez linku, użytkownik jest odsyłany z błędem zamiast być zalogowanym do cudzego konta.

**Efekt uboczny odkryty przy naprawie:** Spatie Permission ma włączone `teams` (`team_foreign_key = tenant_id`), ale `setPermissionsTeamId()` był wołany tylko w seederze, nigdy w runtime — więc `assignRole()`/`hasRole()` operowały na pustym/nieprawidłowym kontekście tenanta w całej aplikacji. Dodano `App\Domain\Rights\Support\TenantScopedRoles` — pomocnik do poprawnego (jawnie tenant-scoped) przypisywania i sprawdzania ról, użyty we wszystkich powyższych fixach oraz podpięty w `UserTenant::boot()` i `User::assignToPosition()` (miejsca zapisu ról). **To punktowa naprawa tylko w dotkniętych miejscach — reszta aplikacji nadal nie ma globalnego mechanizmu ustawiającego team ID per-request; jeśli w przyszłości pojawią się inne miejsca wołające `assignRole()`/`hasRole()` bezpośrednio, będą miały ten sam problem.** Warto rozważyć osobny follow-up: albo globalny middleware ustawiający team ID (wymaga starannego zbadania kolejności middleware), albo konsekwentne użycie `TenantScopedRoles` wszędzie.

**Nienaprawione (świadomie odłożone, HIGH/MEDIUM z Fazy 0):** rate limiting na auth endpoints, enumeracja userów przez reset hasła, bug `birthDate` w rejestracji, IDOR w `ApplicationInvitationController`, plaintext API keys, `UserSession.revoked_at` niesprawdzane, `SignedImageUrlGenerator` gubiący TTL, `RoleName::fromCaseInsensitive` zepsute, martwy kod w `ChatMessage`/`ChatParticipant`, brak walidacji tenanta w `DirectMessageController::createRoom`, endpointy admina z niedziałającym bypassem — wracamy do nich po Fazie 1, albo wcześniej jeśli priorytet się zmieni.

### Faza 1 — Płatności/finanse (Stripe/Subscription, Financial, Exchanges/NBP)

**Ocena ogólna: najpoważniejsza sekcja audytu jak dotąd.** Realne pieniądze + dane kart płatniczych + ten sam wzorzec braku RBAC/tenant-scopingu co w Fazie 0, tym razem bez żadnego pokrycia testami.

- **[CRITICAL]** `app/Domain/Subscription/DTOs/PaymentDetailsDTO.php`, `StoreSubscriptionRequest.php:24-26`, `StripePaymentService.php:16-47` — backend przyjmuje surowy numer karty + CVC od klienta i przekazuje do Stripe zamiast tokenizacji po stronie klienta (Stripe.js/Elements). To zakres zgodności PCI-DSS SAQ D (audytowany, kosztowny) zamiast SAQ A. Każdy request z kartą przechodzi przez serwery aplikacji, jej logi, load balancery.
- **[CRITICAL]** `app/Domain/Subscription/Actions/CreateSubscriptionAction.php:59-66` — przy wyjątku podczas tworzenia subskrypcji cały DTO (włącznie z numerem karty i CVC) jest serializowany do `Log::error()`. Karta odrzucona przez Stripe (częsty, normalny przypadek) = pełny PAN+CVC w logach w plaintext.
- **[CRITICAL]** `SubscriptionController`, `AddonPurchaseController`, `SubscriptionInvoiceController` — modele `Subscription`/`BillingCustomer`/`SubscriptionInvoice`/`AddonPurchase` bez `tenant_id`/tenant-scope. `index()` zwraca dane wszystkich tenantów bez filtra; `show/update/destroy` bez sprawdzenia właściciela. Dowolny user dowolnej firmy widzi/anuluje/zmienia subskrypcje wszystkich innych tenantów w systemie.
- **[CRITICAL]** `StoreSubscriptionRequest.php:20` — walidacja `billingCustomerId` to tylko `exists:billing_customers,id`, bez sprawdzenia właściciela — można doczepić nową subskrypcję do cudzego `BillingCustomer`.
- **[HIGH]** `StripeWebhookController.php:46-124` — podpis webhooka weryfikowany poprawnie, ale brak dedup po `event.id`. Stripe gwarantuje at-least-once delivery; przy pierwszym podpiętym listenerze (patrz niżej) retry zdubluje efekty (np. wysyłkę maila).
- **[HIGH]** Brak RBAC w całej domenie Subscription — każdy member (nie tylko Owner/Admin) może anulować subskrypcję, zmienić plan, kupić addon.
- **[HIGH]** Zdarzenia domenowe (`InvoicePaid`, `InvoicePaymentFailed`, `SubscriptionCreated/Cancelled/Updated`) są dispatchowane, ale nie mają ani jednego listenera — brak powiadomień o nieudanej płatności/anulowaniu. "Stripe billing" oznaczone w README jako zrobione jest funkcjonalnie niedokończone.
- **[HIGH]** Zero testów automatycznych dla całej domeny Stripe/Subscription — najbardziej krytycznej finansowo integracji w systemie.
- **[HIGH]** `app/Domain/Financial/Controllers/VatRateController.php:59-78` — stawki VAT (globalne, współdzielone przez wszystkich tenantów) może tworzyć/usuwać dowolny zalogowany user — brak policy/roli.
- **[MEDIUM]** Błąd zaokrąglania float→cents w `StripeService::formatAmount` (dziś dead code, ale gotowa mina).
- **[MEDIUM]** `PaymentMethodController` bez policy — dowolny member tenanta zarządza metodami płatności.
- **[MEDIUM]** Import kursów NBP (`ImportExchangeRatesJob`) nie waliduje wartości kursu przed zapisem, brak retry/backoff przy awarii NBP, niespójny klucz dedup vs. brak unique constraint w DB (ryzyko duplikatów przy równoległym uruchomieniu).
- **Weryfikacja README:** 11.1 (plany) potwierdzone; 11.3 (Stripe billing) mylące — działa podstawowy flow, ale podważone przez CRITICAL findings i brak listenerów; 11.4 (auto-renewal) i 11.5 (account lockout) faktycznie brak — potwierdzone jako nieoznaczone `[x]`, zgodne z README. W efekcie plany/limity subskrypcji dziś **niczego nie ograniczają**.

**Nie uruchomiono testów/PHPStan** — brak `vendor/` w tym środowisku (nie zainstalowano zależności). Zweryfikowano tylko składnię (`php -l`, czysto). **Zalecenie: przed merge uruchomić pełny `composer install && artisan test && phpstan analyse` lokalnie/w CI.**

### Faza 0 — Multi-tenancy i klasy bazowe

**Ocena ogólna:** rdzeń mechanizmu (proste `WHERE tenant_id`, fail-closed sentinel `'none'`, spójny `BaseModel`) jest solidny. Jeden krytyczny bug punktowy + kilka niedokończonych fragmentów.

- **[CRITICAL]** `app/Domain/Tenant/Models/Tenant.php:295-305` — `Tenant::bypassTenant()` bez `try/finally`. Wyjątek wewnątrz callbacka trwale "zatrzaskuje" `Tenant::$BYPASSED_TENANT_ID` na ID tenanta do restartu procesu. Ponieważ kolejki idą przez Horizon (długożyjące procesy), kolejne joby bez zalogowanego usera (np. `FinishOcrJob`, `ImportExchangeRatesJob`) mogą czytać/zapisywać dane losowego, przeciekniętego tenanta. Trigger: wyjątek w dowolnym z 6 kroków `InitializeTenantDefaults::execute()` (rejestracja tenanta). Naprawa: trywialna (`try/finally`), priorytet: natychmiastowy, przed dalszą pracą.
- **[HIGH]** `app/Domain/Admin/Products/Controllers/AdminProductController.php` i `AdminContractorController.php` (`show/update/destroy`) — próbują `withoutGlobalScope(TenantScope::class)` w ciele metody, ale route-model-binding rozwiązuje się wcześniej (ze scope'em aktywnym) — więc admin nie może zarządzać zasobami innego tenanta mimo takiego zamiaru w kodzie. Brak testów na `routes/api/admin.php`.
- **[HIGH]** `app/Domain/Common/Models/OcrRequest.php` — ma `tenant_id`, ale brak `BelongsToTenant`/`IsGlobalOrBelongsToTenant`. Nieaktywne dziś (brak bezpośredniego route-bindingu), ale otwarta furtka na przyszłość; autor zostawił `// TODO: Add where tenant_id`.
- **[MEDIUM]** `app/Domain/Chat/Models/ChatMessage.php`, `ChatParticipant.php` — `BelongsToTenant` zaimportowany, ale użycie zakomentowane. Bezpieczeństwo dziś opiera się wyłącznie na ręcznych sprawdzeniach w kontrolerze.
- **[MEDIUM]** `app/Domain/Chat/Controllers/DirectMessageController.php:32` (`createRoom`) — brak walidacji, że drugi user należy do tego samego tenanta.
- **[LOW]** `app/Domain/Approval/Models/ApprovalExpenseExecution.php:55`, `ApprovalWorkflowStep.php:48` — relacje z `withoutGlobalScopes()`, same modele bez własnego scope'u (ryzyko architektoniczne, nie aktywny wyciek).
- **[LOW]** `tests/Unit/Domain/Tenant/BelongsToTenantTest.php:44` — ustawia `Tenant::$BYPASSED_TENANT_ID` bez resetu w `tearDown()`.

### Faza 0 — Rights (uprawnienia, role)

**Wzorzec:** system RBAC istnieje jako "rusztowanie" (Role/Permission/seeder), ale w praktyce jest podłączony tylko punktowo — większość endpointów sprawdza wyłącznie członkostwo w tenancie, nie rolę/uprawnienie.

- **[CRITICAL]** `app/Domain/Tenant/Policies/TenantPolicy.php:19-22` — `update`/`delete` sprawdzają tylko członkostwo, nie rolę. Dowolny user (nawet bez uprawnień) może `DELETE /v1/tenants/{tenant}` — skasować całą firmę — albo edytować jej dane.
- **[CRITICAL]** `app/Domain/Rights/Controllers/RoleController.php` — zero autoryzacji (brak policy/gate, `authorize()` zawsze `true`). Każdy user może tworzyć/edytować/usuwać dowolne role w tenancie, w tym nadawać sobie permissions.
- **[CRITICAL]** `app/Domain/Tenant/Controllers/TenantInvitationController.php:53-59` — jawny `// TODO: Add authorization check`, `role` z requestu niewalidowany przeciw liście ról. Dowolny user może zaprosić samego siebie (drugi e-mail) z `role: "Admin"` i po `accept()` stać się globalnym Adminem tenanta — pełna eskalacja uprawnień.
- **[HIGH]** `app/Domain/Tenant/Models/Tenant.php:295-305` — ten sam `bypassTenant()` bez `try/finally` co w sekcji multi-tenancy (potwierdzone niezależnie przez drugi agent).
- **[MEDIUM]** `app/Domain/Tenant/Controllers/OrganizationUnitController.php:91-103` (`assignUserToUnit`) — brak autoryzacji, IDOR: dowolny user może przypisać dowolnego innego użytkownika tenanta do stanowiska. Dziś częściowo nieszkodliwe (brak API tworzącego `role_name` na stanowisku), ale gotowa ścieżka eskalacji na przyszłość.
- **[MEDIUM]** `app/Domain/Rights/Enums/RoleName.php:15-18` — `fromCaseInsensitive()` zepsute (lowercase vs case-sensitive `tryFrom`), zawsze zwraca fallback `User`. Martwy kod, nieużywany.
- **[LOW]** Walidacja `permissions.*` w `StoreRoleRequest`/`UpdateRoleRequest` pomija tenant-scoping modelu `Permission`.
- **[LOW]** Brak testów dla `RoleController`, `TenantPolicy`, `TenantInvitationController::send` — krytyczne ścieżki niepokryte.

### Faza 0 — Common (`HasIndexQuery`, `HasActivityLogging`, `HasMediaSignedUrls`)

- **[CRITICAL]** `app/Domain/Common/Controllers/ActivityLogController.php:44-53` — filtr `where('tenant_id', ...)` buduje `$query`, ale wywołanie `getIndexPaginator($request)` nie przekazuje go dalej (buduje nowy Builder od zera) — filtr jest martwym kodem. `GET /v1/logs` zwraca activity log **wszystkich tenantów** dla dowolnego zalogowanego usera. Porównanie z poprawnym wzorcem obok (`TenantActivityLogController::index`, przekazuje `query:`) potwierdza, że to przeoczenie/regres, nie decyzja projektowa.
- **[HIGH]** `app/Domain/Common/Traits/HasActivityLog.php:14-21` — `logOnly(['*'])` na modelach z danymi wrażliwymi (numery IBAN kontrahentów, dane kontaktowe, kwoty faktur) — w połączeniu z powyższym CRITICAL, te dane są czytelne dla obcych tenantów.
- **[MEDIUM]** `app/Domain/Common/Support/SignedImageUrlGenerator.php:9-26` — gubi parametr `expiration` przy wywołaniu `RelativeUrlSigner::generate()`; deklarowane 60s ważności linku do pliku, realnie 15 min (domyślna wartość).
- **[LOW]** `HasIndexQuery`/filtry (`AdvancedFilter`, `ComboSearchFilter`) — bez zastrzeżeń, nazwy kolumn z allow-list w kontrolerach, nie z requestu; brak potwierdzonej SQL injection.
- **[LOW]** Brak testów dla `HasIndexQuery`, `ActivityLogController` (test feature na `/v1/logs` wyłapałby powyższy CRITICAL), `HasMediaSignedUrls`.

### Faza 0 — Auth

**Wzorzec:** dużo "rusztowania" pod właściwe zabezpieczenia (modele, DTO, enumy), ale kluczowe spięcia łączące je z faktyczną egzekucją nie są dokończone.

- **[CRITICAL]** `AuthController.php:36-62` + `JwtHelper.php:54-66` — 2FA jest kosmetyczne. Login wydaje pełnoprawny token niezależnie od tego, czy user ma włączone 2FA; claim `mfa` w JWT nigdzie nie jest egzekwowany (brak middleware). Znajomość samego hasła wystarcza mimo włączonego 2FA.
- **[CRITICAL]** `app/Domain/Auth/Controllers/OAuthController.php:23-45` — logowanie po samym e-mailu (`firstOrCreate(['email' => ...])`), model `OAuthAccount` istnieje ale nigdy nie jest używany do linkowania tożsamości. Ryzyko przejęcia konta lokalnego przez OAuth z tym samym e-mailem. Dodatkowo: `stateless()` (brak ochrony CSRF w handshake), JWT w query string URL przekierowania (trafia do historii/logów).
- **[HIGH]** Brak rate limitingu na `login`, `register`, `forgot-password`, `reset-password`, `oauth/*`, `2fa/verify` — brute-force bez żadnej blokady poza reCAPTCHA v3.
- **[HIGH]** `PasswordResetController.php:35-48` — enumeracja userów przez różne kody/treści odpowiedzi (400 vs 200) w zależności od istnienia e-maila.
- **[HIGH]** `AuthController.php:83-91` (`register()`) — bug typu: `isset($validated['birth_date'])` ale odczyt `$validated['birthDate']` (camelCase/snake_case mismatch) → `new \DateTime(null)` → `TypeError` przy zapisie do DTO. **Rejestracja z polem `birthDate` zwraca 500** — feature oznaczony w README jako zrobiony faktycznie nie działa.
- **[HIGH]** `app/Domain/Auth/Controllers/ApplicationInvitationController.php` — IDOR/BOLA: `index()` zwraca wszystkie zaproszenia w systemie; `cancel()`/`resend()` bez weryfikacji właściciela; `accept()`/`reject()` bez sprawdzenia `email === user.email` przed zmianą stanu. Trasy poza grupą `is_active` — działa nawet dla niezatwierdzonych kont.
- **[HIGH]** `app/Domain/Auth/Controllers/ApiKeyController.php` + `ApiKeyResource.php:24` — klucze API przechowywane i zwracane jawnym tekstem przy każdym odczycie (nie tylko raz przy tworzeniu).
- **[MEDIUM]** `logout()` odwołuje tylko access token, nie refresh token; `UserSession.revoked_at` nigdzie nie jest sprawdzane przy autoryzacji (kosmetyczne); `UserSession::isCurrent()` ma zepsute porównanie (zawsze `false`).
- **[MEDIUM]** Słaba polityka haseł (`min:8`, brak `Password::uncompromised()`), niespójna między rejestracją a resetem.
- **[MEDIUM]** Zmiana e-maila konta bez ponownej weryfikacji (`// TODO: Email/Phone confirmation` w kodzie).
- **[MEDIUM]** Wyłączenie 2FA bez ponownego uwierzytelnienia (samo `auth:api` wystarcza).
- **[MEDIUM]** Brak throttlingu na panelu admina (`AdminAuthController`).
- **[LOW]** Kilka mniejszych: token weryfikacji e-mail bez `expires_at` i bez `hash_equals()`, `remember` ignorowany przy refresh, status zaproszeń jako string zamiast enum, martwy import `RespondsWithToken` w `OAuthController`.
- **Testy:** praktycznie zerowe pokrycie krytycznych ścieżek (`login`, `register`, `refresh`, `logout`, `OAuth`, `2FA`, `forgot/reset-password`) — stąd żaden z powyższych bugów nie został wyłapany.
