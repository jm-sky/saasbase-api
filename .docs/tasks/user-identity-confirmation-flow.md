# ✅ Zadanie: Confirm User Identity (podpis EPUAP)

## 🎯 Cel funkcji
Pozwolić użytkownikowi potwierdzić swoją tożsamość przez podpisanie dokumentu PDF przy użyciu EPUAP (kwalifikowany podpis elektroniczny). Dokument jest generowany przez system, a podpis jest później automatycznie weryfikowany.

---

## 🔁 Przebieg (UX / Flow)

1. Użytkownik otwiera stronę **Potwierdzenie tożsamości**.
2. Widzi przycisk: **„Pobierz oświadczenie do podpisania”**.
3. Kliknięcie:
   - wysyła request do backendu,
   - backend generuje PDF z danymi użytkownika oraz tokenem i timestampem,
   - plik PDF trafia do kolekcji `identity_confirmation_template` w Media Library użytkownika.
4. Frontend dostaje URL do pliku i wyświetla przycisk **„Pobierz PDF”**.
5. Użytkownik podpisuje PDF przez EPUAP.
6. Użytkownik przesyła podpisany plik przez formularz (upload).
7. Backend:
   - weryfikuje podpis (PAdES),
   - porównuje dane w pliku z profilem użytkownika,
   - jeśli OK: zapisuje plik w `identity_confirmation_final` i czyści poprzedni,
   - zwraca status (zaufany / niezaufany) + szczegóły na frontend.

---

## 🧱 Dane zawarte w PDF

Generowane na podstawie `auth()->user()`:

- `first_name`
- `last_name`
- `birth_date`
- `pesel`
- `confirmation_token` (UUID lub losowy hash)
- `generated_at` (timestamp UTC)
- `app_name` (np. `config('app.name')`)

📄 Przykład tekstu w PDF:
> „Oświadczam, że ja, **Jan Kowalski** (PESEL: XXX), urodzony dnia YYY, potwierdzam moją tożsamość na potrzeby aplikacji **TwojaAppka**.  
> Data i godzina wygenerowania: 2025-06-06 14:32  
> Unikalny identyfikator: **IDENTITY-123e4567-e89b-12d3-a456-426614174000**”

---

## 📂 Kolekcje Media (Spatie)

- `identity_confirmation_template` – PDF do podpisania, 1 plik (ostatni)
- `identity_confirmation_final` – podpisany PDF, 1 plik (można wersjonować)

---

## ⚙️ Endpointy API (Laravel)

### `POST /api/identity/confirmation/template`

Generuje PDF i zapisuje go w `identity_confirmation_template`.

**Response:**
```json
{
  "file_url": "https://app.com/media/xyz.pdf",
  "expires_at": "2025-06-06T13:00:00Z"
}
```

---

### `POST /api/identity/confirmation/submit`

Upload podpisanego PDF.

**Request:**
- Multipart z kluczem `file`

**Akcje:**
- Walidacja podpisu (czy kwalifikowany, zaufany, ważny)
- Ekstrakcja danych z podpisu i PDF
- Porównanie z profilem użytkownika
- Zapis do `identity_confirmation_final`, usunięcie z `identity_confirmation_template`

**Response:**
```json
{
  "status": "verified",
  "confirmed": {
    "full_name": true,
    "pesel": true,
    "birth_date": true
  },
  "signature_info": {
    "issuer": "Ministerstwo Cyfryzacji",
    "timestamp": "2025-06-06T12:00:00Z"
  }
}
```

---

## 🧰 Wewnętrzna klasa serwisowa

- `App\Services\PdfSignatureVerifierService` – odpowiada za walidację podpisu i ekstrakcję danych

---

## 🖼 Frontend (Vue 3 + Axios)

1. `api.post('/identity/confirmation/template')` (api używa baseUrl) 
2. Pokazanie linku do pobrania PDF
3. Formularz uploadu podpisanego pliku
4. `api.postForm('/identity/confirmation/submit', { file })`
5. Wyświetlenie statusu i szczegółów podpisu

---

## 🧪 Testy

- [ ] Czy PDF generuje się poprawnie z danymi użytkownika
- [ ] Czy podpisany plik jest poprawnie weryfikowany
- [ ] Czy dane z podpisu pasują do danych profilu
- [ ] Czy media są poprawnie przypisywane i czyszczone

---

## 🧭 Możliwe rozszerzenia

- ⏳ Token z TTL (np. 1h ważności)
- ♻️ Możliwość wygenerowania nowego szablonu
- 🧾 Historia weryfikacji (wersjonowanie podpisów)