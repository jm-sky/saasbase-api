# Lokalna weryfikacja jakości (CS Fixer + PHPStan)

**Data:** 2026-07-02  
**Branch:** `claude/saasbase-project-review-0p5z11`  
**Kontener:** `docker compose exec laravel.test` (serwis `laravel.test`)  
**Wykonawca:** lokalny Docker (dev)

## Środowisko Docker

| Krok | Wynik |
|------|--------|
| `./scripts/up.sh -d` | **Częściowy błąd** — `saasbase-api-rustfs-1` nie wystartował: port `127.0.0.1:9000` zajęty przez istniejący kontener `rustfs-server` (zewnętrzny RustFS na sieci `rustfs-network`). |
| Obejście | `docker network create rustfs-network` (jeśli brak), potem `docker compose up -d --no-deps laravel.test`. Pozostałe serwisy (pgsql, redis, mailpit, soketi, meilisearch) wystartowały. |
| Uwaga | Orphan: `saasbase-api-app-1` (stara nazwa serwisu). Opcjonalnie: `docker compose up -d --remove-orphans` po weryfikacji. |
| Ostrzeżenia | `WWWUSER` / `WWWGROUP` nie ustawione w shellu hosta; w kontenerze composer działał poprawnie. Git w kontenerze: `dubious ownership` na `/var/www/html` (kosmetyka, nie blokuje CS/PHPStan). |

## PHP CS Fixer — `composer csf`

**Exit code:** 0  
**Skan:** 1262 pliki  
**Naprawione automatycznie:** 4 pliki (wyrównanie kolumn / drobne style)

| Plik | Zmiana |
|------|--------|
| `app/Providers/AuthServiceProvider.php` | Wyrównanie mapy `$policies` (w tym `TenantIntegrationPolicy`). |
| `app/Domain/Subscription/Requests/StoreSubscriptionRequest.php` | Wyrównanie kluczy reguł i komunikatów walidacji. |
| `app/Domain/Subscription/Actions/CreateSubscriptionAction.php` | Wyrównanie kluczy tablicy w `Log::error()`. |
| `database/migrations/2025_07_10_000010_fix_tenant_integrations_credentials_encryption.php` | Docblock `/**` → `/*`; `catch (\Throwable)` → `catch (Throwable)` (globalna klasa PHP). |

**Dla agenta:** te 4 pliki powinny trafić do commita razem z tym dokumentem (brak dalszej ręcznej poprawki poza review diffu CS Fixera).

## PHPStan (Larastan) — `composer larastan`

**Exit code:** 0  
**Konfiguracja:** `phpstan.neon` (poziom zgodny z projektem)  
**Wynik:** `[OK] No errors` (1149 plików przeanalizowanych)

**Dla agenta:** zaktualizować w `REVIEW_PLAN.md` zdanie *„Nie uruchomiono testów/PHPStan”* (ok. linia 297) — PHPStan wykonany lokalnie w kontenerze z pełnym `vendor/`. Testy PHPUnit (`./vendor/bin/sail artisan test`) **nadal nie uruchomione** w tej sesji.

## Zalecane kolejne kroki (agent)

1. ~~Commit poprawek CSF + ten plik~~ (wykonane w ramach tego zadania użytkownika, jeśli push poszedł).
2. Uruchomić pełny zestaw testów w Sail/Docker: `./vendor/bin/sail artisan test` (lub `docker compose exec laravel.test php artisan test`).
3. Rozstrzygnąć konflikt portu RustFS: albo nie startować `rustfs` z compose gdy działa `rustfs-server`, albo zmienić `FORWARD_MINIO_PORT` w `.env`.
4. Opcjonalnie: ustawić `WWWUSER`/`WWWGROUP` w `.env` i `safe.directory` w obrazie dev, żeby uciszyć ostrzeżenia.

## Komendy odtwarzające

```bash
docker network inspect rustfs-network >/dev/null 2>&1 || docker network create rustfs-network
./scripts/up.sh -d
# jeśli rustfs nie wstanie z powodu portu 9000:
docker compose up -d --no-deps laravel.test

docker compose exec -T laravel.test composer csf
docker compose exec -T laravel.test composer larastan
```
