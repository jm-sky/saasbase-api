# Modele użytkownika – rozszerzone dane i ustawienia (lista pól)

> :white_check_mark: Status: Done

---

> Valid models definitions are located here: `.docs/tasks/user-models.md`


## 1. UserPreferences – Preferencje aplikacji

> Model: `user_preferences`

- **language** (string) – wybrany język interfejsu użytkownika  
- **decimal_separator** (string) – separator dziesiętny (np. "." lub ",")  
- **date_format** (string) – format daty (np. DD.MM.YYYY)  
- **dark_mode** (boolean) – czy używać trybu ciemnego  
- **sound_enabled** (boolean) – czy aplikacja może odtwarzać dźwięki  
- **table_settings** (JSON) – ustawienia widoków tabel: kolumny, sortowanie, szybkie akcje  

**Status:** nowe pole, warto trzymać w osobnym modelu lub jako JSON w `users.settings`.

---

## 2. UserProfile – Profil publiczny

> Model: `user_profiles`

- **bio / description** (text) – krótki opis lub biogram  
- **location** (string) – miasto, region  
- **birth_date** (date) – data urodzenia  
- **position** (string) – rola zawodowa, np. "Tester", "Project Manager"  
- **website** (string) – URL do strony użytkownika  
- **social_links** (JSON) – lista linków do social media  

---

## 3. UserPrivacy – Ustawienia prywatności

> Model: `user_preferences`

- **is_profile_public** (boolean) – czy profil jest widoczny publicznie  
- **field_visibility** (JSON) – widoczność poszczególnych pól (np. bio, email, location) z zakresem: ukryte, publiczne w ramach tenant, publiczne globalnie  
- **visibility_per_tenant** (JSON) – nadpisania widoczności dla konkretnego tenant'a  


---

## 4. UserStatus – Status obecności i konta

> Another model and table?

- **presence_status** (enum) – np. active, away, busy, ill, traveling, do_not_disturb  
- **status_message** (string) – wiadomość widoczna przy statusie  
- **account_status** (enum) – status konta: active, pending, blocked  

**Status:** częściowo ujęte, np. `account_status`, ale `presence_status` i `status_message` to nowość.

---

# Podsumowanie

Nowe modele/kategorie do uwzględnienia:

- UserPreferences – ustawienia aplikacji (tryb ciemny, dźwięki, język, formaty)  
- UserProfile – dane publiczne i społecznościowe  
- UserPrivacy – kontrola prywatności pól  
- UserStatus – status aktywności i obecności  

---

## Oryginalne dane źródłowe, pomysł (archiwalnie):

- język
- separator dziesiętny
- format daty
- dark mode
- Czy profil jest publiczny
- które pola są publiczne (można zakres: ukryte, publiczne w ramach tenant, publiczne globalnie) (ew. dla którego tenant są publiczne) 
- bio / description
- location 
- birth date 
- position (developer, tester, accountant...) 
- website 
- social links 
- status - active, away, busy, ill, traveling, don't disturb...?
- status message
- account status - active / pending / blocked (lub: is active, is pending, is blocked?)
- ustawienia tabel danych (per widok) (domyślne filtry, sortowanie, kolejność i widoczność kolumn, szybkie akcje)
- dźwięki aplikacji 