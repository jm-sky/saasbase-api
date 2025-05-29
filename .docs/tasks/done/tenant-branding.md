# Tenant branding
Konfiguracja wyglądu i identyfikacji wizualnej tenanta w aplikacji, dokumentach oraz wiadomościach e-mail.

## MODEL: TenantBranding
Przechowuje dane służące do personalizacji wyglądu aplikacji i dokumentów.

- `logo_key`: string  
    Klucz pliku logo w systemie plików (np. S3). Używany w interfejsie i dokumentach.

- `favicon_key`: string  
    Klucz pliku favicony (opcjonalnie).

- `color_primary`: string  
    Główny kolor identyfikacyjny UI (np. przyciski, linki, nagłówki).

- `color_secondary`: string  
    Dodatkowy kolor wspierający (np. tła, obramowania).

- `short_name`: string  
    Skrócona nazwa tenanta, wyświetlana w menu lub nagłówkach.

- `custom_font_key`: string  
    (Opcjonalnie) Klucz do niestandardowej czcionki, np. z pliku `.woff`.

- `theme`: string (enum: "light", "dark", "system")  
    Preferowany motyw kolorystyczny interfejsu.

- `pdf_logo_key`: string  
    Klucz do logo używanego w PDF-ach (faktury, oferty).

- `pdf_accent_color`: string  
    Kolor akcentowy w dokumentach PDF.

- `email_header_image_key`: string  
    Klucz do obrazka nagłówkowego w e-mailach.

- `email_signature_html`: string  
    HTML stopki dodawanej do e-maili systemowych.

---

# Tenant public profile
Publiczna wizytówka tenanta widoczna dla użytkowników spoza organizacji.

## MODEL: TenantPublicProfile
Zawiera dane marketingowe i lokalizacyjne tenanta.

- `public_name`: string  
    Nazwa wyświetlana publicznie. Musi być podobna (fuzzy) do oficjalnej nazwy z modelu `Tenant`.

- `description`: text  
    Opis działalności organizacji.

- `website_url`: string  
    Adres strony internetowej.

- `social_links`: json  
    Lista linków do mediów społecznościowych (np. `{ platform: "linkedin", url: "..." }`).

- `banner_image_key`: string  
    Klucz do pliku banera (np. na stronie publicznej profilu).

- `public_logo_key`: string  
    Klucz do pliku z logo wyświetlanego w profilu publicznym.

- `visible`: boolean  
    Czy profil jest publicznie widoczny.

- `industry`: string  
    Branża, np. "IT", "Budownictwo", "E-commerce".

- `location_city`: string  
    Miasto siedziby.

- `location_country`: string  
    Kraj, np. „PL”.

- `address`: text  
    Pełny adres organizacji (np. do wyświetlenia lub na potrzeby faktur).

---

# Uwagi dotyczące spójności danych

- `slug`, `vat_id`, `regon`, i inne dane oficjalne powinny znajdować się w modelu `Tenant`, jako że są wykorzystywane również poza kontekstem publicznego profilu.
- Dla pola `public_name` warto dodać walidację „fuzzy similarity” względem `Tenant.name`, szczególnie jeśli `name` pochodzi z oficjalnego źródła (np. API CEIDG/KRS).
- Adresy plików przechowujemy jako klucze (`*_key`) w S3 lub innym storage. Endpoint API odpowiadający za profil może je zamieniać na signed URLs przy pobieraniu.
- Można rozważyć model/kolumnę z info jakie dane pochodzą z oficjalnego źródła (API rządowego), np.`{ source: "MF", fields: ["name", "vat_id"], confirmed_at: dateTime }`
