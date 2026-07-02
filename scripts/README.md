# Skrypty uruchomieniowe SaaSBase

## Dostępne skrypty

### `./up.sh` - Wersja DEV
Uruchamia środowisko deweloperskie z lokalnym MinIO.

**Używa:**
- `docker-compose.yml`
- `.env` (local)

**Serwisy:**
- PostgreSQL
- Redis
- MinIO (lokalny S3)
- Mailpit
- Soketi

**Uruchomienie:**
```bash
./scripts/up.sh -d
```

---

### `./up-ovh.sh` - Wersja OVH/Produkcja ⭐
Uruchamia środowisko produkcyjne używające zewnętrznego RustFS.

**Używa:**
- `docker-compose.ovh.yml`
- `.env.ovh` (production)

**Serwisy:**
- PostgreSQL
- Redis
- Mailpit
- ~~MinIO~~ (używa RustFS przez sieć `rustfs-network`)
- ~~Soketi~~ (zakomentowane)

**Uruchomienie:**
```bash
./scripts/up-ovh.sh -d
```

**Wymagania:**
- Sieć Docker `rustfs-network` musi istnieć
- RustFS musi być uruchomiony
- Bucket `saasbase` musi być utworzony w RustFS

---

### `./createMinioBucket.sh`
Tworzy bucket `saasbase` w lokalnym MinIO.

**Używaj tylko z wersją DEV (`./up.sh`)**

```bash
./scripts/createMinioBucket.sh
```

---

## Wybór wersji

- **Deweloper lokalny**: użyj `./up.sh` (z MinIO)
- **Serwer OVH/produkcja**: użyj `./up-ovh.sh` (z RustFS)
