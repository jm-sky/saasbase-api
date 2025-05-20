# Modele konta użytkownika (Multi-tenant, Laravel-style)

Każdy model zawiera: `nazwa pola`, `typ`, `opis`, `zastosowanie`. Używany jest `tenant_id` zamiast `company_id`.

---

## Model: `users`

> Looks good

- `id`: UUID  
  Unikalny identyfikator użytkownika.

- `first_name`: string  
  Imię użytkownika. Używane w interfejsie i komunikacji.

- `last_name`: string  
  Nazwisko użytkownika.

- `email`: string  
  Adres e-mail użytkownika. Może służyć do logowania, komunikacji i weryfikacji.

- `email_verified_at`: datetime (nullable)  
  Data potwierdzenia adresu e-mail.

- `phone_number`: string (nullable, unikalny)  
  Numer telefonu. Może służyć jako login lub alternatywny kanał kontaktu.

- `phone_verified_at`: datetime (nullable)  
  Data potwierdzenia numeru telefonu.

- `password`: string (hash)  
  Hasło użytkownika, przechowywane w formie hasha.

- `is_active`: boolean  
  Czy konto użytkownika jest aktywne. Używane do dezaktywacji kont bez ich usuwania.

- `remember_token`: string

- `created_at`: timestamp  
  Data utworzenia konta.

- `updated_at`: timestamp  
  Data ostatniej modyfikacji konta.

- `deleted_at`: timestamp  

---

## Model: `user_profiles`

> `.docs/tasks/user-preferences.md` at 2. UserProfile – Profil publiczny

> Need to check

- `id`: UUID  
  Identyfikator profilu.

- `user_id`: UUID  
  Relacja do użytkownika.

- `avatar_url`: string  
  URL do avatara użytkownika. Używany w profilu.

- `bio`: text  
  Opis / krótkie bio. Widoczne w profilu.

- `location`: string  
  Miasto / kraj. Informacja lokalizacyjna.

- `birth_date`: date
  data urodzenia  

- `position`: string
  rola zawodowa, np. "Tester", "Project Manager"  

- `website`: string
  URL do strony użytkownika  

- `social_links`: JSON  
  Linki do mediów społecznościowych.

---

## Model: `user_preferences`

- `language`: string
   Wybrany język interfejsu użytkownika (i.e. "pl", "en")

- `decimal_separator`: string
   Separator dziesiętny (np. "." lub ",")  

- `date_format`: string
   Format daty (np. DD.MM.YYYY)  

- `dark_mode`: string
   > Można nazwać inaczej
   Czy używać trybu ciemnego (system, dark, light)

- `is_sound_enabled`: boolean
   Czy aplikacja może odtwarzać dźwięki  

- `is_profile_public`: boolean
   czy profil jest widoczny publicznie  

- `field_visibility`: JSON
   Widoczność poszczególnych pól (np. bio, email, location) z 
   zakresem: ukryte, publiczne w ramach tenant, publiczne globalnie
   
   Maybe something like that, or in a better way?
   
   `{ bio: 'public', location: 'tenant', birth_date: 'hidden' }`

- `visibility_per_tenant`: JSON
   Nadpisania widoczności dla konkretnego tenant'a  

---

## Model: `user_table_settings`

> For later

- `id`: UUID  
  Identyfikator ustawień

- `user_id`: UUID  

- `entity`: string  
  EniEntity name, i.e. contractors, products

- `name`: string  
  Nullable label

- `config`: json  
  Filters, colum order and visibility, sorting

- `is_default`: boolean  

---


## Model: `notification_settings`

> Check needed

- `id`: UUID  
  Identyfikator ustawień notyfikacji.

- `user_id`: UUID  
  Relacja do użytkownika.

- `channel`: string  
  Kanał (`email`, `sms`, `app`).

- `setting_key`: string  
  Typ notyfikacji (`task_assigned`, `invoice_paid`).

- `enabled`: boolean  
  Czy dana notyfikacja jest aktywna.

---

## Model: `api_keys`

> Already implemented

- `id`: UUID  
  Identyfikator klucza API.

- `tenant_id`: UUID  
  Przypisanie do najemcy (organizacji).

- `user_id`: UUID  
  Właściciel klucza API.

- `name`: string  
  Nazwa klucza (np. „Integracja z Zapier”).

- `key`: string  
  Wartość klucza API. Służy do autoryzacji.

- `scopes`: JSON  
  Lista uprawnień (np. `["read", "write"]`).

- `last_used_at`: datetime  
  Data ostatniego użycia.

---

## Model: `trusted_devices`

> Need to implement

- `id`: UUID  
  Unikalny identyfikator urządzenia.

- `user_id`: UUID  
  Relacja do użytkownika.

- `device_name`: string
  Nazwa urządzenia

- `browser`: string
  Przeglądarka

- `os`: string
  System operacyjny

- `location`: string
  Lokalizacja (np. miasto, kraj)

- `last_active_at`: datetime
  Data ostatniej aktywności

- `ip_address`: string  
  Adres IP urządzenia przy rejestracji.

- `created_at`: datetime  
  Data dodania urządzenia jako zaufanego.

---

## Model: `security_events`

> Can be implemented later

- `id`: UUID  
  Unikalny identyfikator zdarzenia.

- `user_id`: UUID  
  Kto wygenerował zdarzenie.

- `event_type`: string  
  Typ zdarzenia (`login_failed`, `password_change`).

- `ip_address`: string  
  Adres IP z którego wystąpiło zdarzenie.

- `created_at`: datetime  
  Czas wystąpienia zdarzenia.

---

## Model: `billing_info`

> May be implemented later

- `id`: UUID  
  Identyfikator danych do faktury.

- `tenant_id`: UUID  
  Organizacja, do której należą dane.

- `user_id`: UUID (opcjonalnie)  
  Właściciel (jeśli osobisty billing).

- `company_name`: string  
  Nazwa firmy.

- `vat_id`: string  
  Numer VAT.

- `address`: text  
  Adres siedziby.

- `country`: string  
  Kraj.

---

## Model: `invitations`

User can invite someone to join our app. Those invitations are outside tenants scope.

User (inviter) should be able to resend and cancel invitation.

Invited user should be able to accept or reject invitation.

> Note: Model below should be checked.

- `id`: UUID  
  Identyfikator zaproszenia.

- `inviter_id`: UUID  
  User who sent invitation.

- `email`: string  
  E-mail of invited person.

- `token`: string  
  Token rejestracyjny.

- `status`: string  
  Status (`pending`, `accepted`, `rejected`, `revoked`).

- `accepted_at`: datetime

- `expires_at`: datetime

- `created_at`: timestamp/datetime
- `updated_at`: timestamp/datetime
