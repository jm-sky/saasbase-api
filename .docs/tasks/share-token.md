### ShareToken

Token pozwalający na dostęp do określonych zasobów aplikacji poprzez specjalny link. Może być stosowany do udostępniania różnych modeli (faktury, pliki, raporty itd.).

#### Pola

- `id`: `uuid` – Unikalny identyfikator tokenu.
- `token`: `string` – Właściwy token używany w URL-ach (np. `/share/{token}`); powinien być unikalny.
- `shareable_type`: `string` – Nazwa klasy modelu powiązanego (polimorficzne).
- `shareable_id`: `uuid` lub `int` – ID powiązanego modelu.
- `only_for_authenticated`: `boolean` – Czy link działa tylko dla zalogowanych użytkowników.
- `expires_at`: `datetime|null` – Data wygaśnięcia tokenu. Jeśli `null`, token nie wygasa automatycznie.
- `last_used_at`: `datetime|null` – Data ostatniego użycia tokenu.
- `usage_count`: `integer` – Liczba razy, kiedy token został użyty.
- `max_usage`: `integer|null` – Opcjonalne ograniczenie liczby użyć. `null` = brak limitu.
- `created_at`: `datetime` – Data utworzenia.
- `updated_at`: `datetime` – Data ostatniej modyfikacji.

#### Zastosowanie

- Udostępnienie publiczne lub prywatne (np. faktury PDF, dokumenty).
- Możliwość tworzenia wielu tokenów dla jednego modelu.
- Link może wygasać czasowo lub po określonej liczbie użyć.
- Weryfikacja, czy użytkownik musi być zalogowany (`only_for_authenticated`).
- Przydatne logowanie użycia (`last_used_at`, `usage_count`) np. do audytu.

#### Przykład URL-a

```
https://app.example.com/share/{token}
```

#### Uwaga techniczna

W Laravelu warto dodać relację:
```php
public function shareable(): MorphTo
{
    return $this->morphTo();
}
```
oraz indeks na `token` (unikalny) i `expires_at` dla czyszczenia przeterminowanych tokenów.