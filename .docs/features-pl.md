# SaaSBase – Feature To-Do List

## 1. Autoryzacja
- [x] 1.1 Rejestracja użytkownika  
- [x] 1.2 Logowanie (hasło)  
- [x] 1.3 OAuth (Google, GitHub)  
- [x] 1.4 Zaproszenia użytkowników w ramach tenant  

## 2. Tenant  
- [x] 2.1 Rejestracja tenanta  
- [x] 2.2 Adresy tenanta  
- [x] 2.3 Konta bankowe tenanta  
- [x] 2.4 Profil publiczny tenanta  
- [x] 2.5 Branding tenanta  
- [x] 2.6 Struktura organizacji tenanta  
- [x] 2.7 Zaproszenia w ramach tenanta  
- [ ] 2.8 Konfiguracja integracji (tenant podaje własne credentials):  
  - [x] 2.8.1 Azure Intelligence Studio  
  - [x] 2.8.2 KSeF (Polski system e-faktur)  
  - [x] 2.8.3 E-doręczenia  
  - [ ] 2.8.4 Integracja z pocztą e-mail  
  - [ ] 2.8.5 Integracja z Google Kalendarzem  

## 3. Integracje  
- [x] 3.1 REGON (integracja)  
- [x] 3.2 VIES (integracja)  
- [x] 3.3 Ministerstwo Finansów - Biała Lista Podatników VAT (integracja)  
- [x] 3.4 IBAN API (integracja)  
- [x] 3.5 Azure Intelligence Studio (integracja)  

## 4. Kontrahenci  
- [x] 4.1 Lista kontrahentów z mechanizmem przeszukiwania i paginacji (wspólny mechanizm z Common)  
- [x] 4.2 Adresy kontrahentów  
- [x] 4.3 Konta bankowe kontrahentów  
- [x] 4.4 Osoby kontaktowe (powiązane z domeną Globalne Kontakty)  
- [x] 4.5 Obrazek/logo kontrahenta  
- [x] 4.6 Registry Confirmation (potwierdzenia zgodności z rejestrami REGON, VIES, Ministerstwo Finansów)  
- [x] 4.7 Mechanizm tagów/etykiet (wspólny z Fakturami/Kosztami i Produktami)  
- [x] 4.8 Mechanizm komentarzy przy kontrahentach  
- [ ] 4.9 Eksport do Excela (lista kontrahentów)  

## 5. Common / Shared Functionalities  
- [x] 5.1 Wspólny mechanizm przeszukiwania i paginacji dla list (wsparcie operatorów porównania, filtrów, sortowania)  
- [ ] 5.2 Universal "Link with..." funkcjonalność z polimorficznym modelem (np. `Linkable`)  
- [ ] 5.3 Universal reminders system (polimorficzny model, np. `Reminder`)  
- [ ] 5.4 Powiadomienia w systemie (w tym na żywo, WebSockets)  

## 6. Faktury / Koszty  
- [x] 6.1 Przeglądanie listy faktur i kosztów z filtrowaniem i przeszukiwaniem (wspólny mechanizm z Common)  
- [ ] 6.2 Podstawowe funkcje modułu faktur/kosztów:  
  - [ ] 6.2.1 Tworzenie, edycja, usuwanie  
  - [ ] 6.2.2 Statusy faktur/kosztów  
- [ ] 6.3 Akcje na fakturach/kosztach:  
  - [ ] 6.3.1 Zmiana statusu  
  - [ ] 6.3.2 Kopiowanie / klonowanie  
  - [ ] 6.3.3 Wysyłka e-mail  
  - [ ] 6.3.4 Wysyłka do KSeF  
  - [ ] 6.3.5 Generowanie PDF i PDF duplikatu  
  - [ ] 6.3.6 Dołączanie / generowanie płatności  
  - [ ] 6.3.7 Ustawianie i zarządzanie przypomnieniami  
  - [ ] 6.3.8 Powiązania z innymi encjami:  
    - Projekt  
    - Użytkownik  
    - Kontrahent  
  - [ ] 6.3.9 Faktury cykliczne / okresowe z konfiguracją szablonu  
  - [ ] 6.3.10 Udostępnianie linku publicznego  
  - [ ] 6.3.11 Eksport do paczki przelewów bankowych (np. Videotel)  
  - [ ] 6.3.12 Grupowe / masowe akcje:  
    - Wystawianie wielu  
    - Usuwanie wielu  
    - Wysyłka e-mail masowa  
    - Wysyłka do KSeF masowa  
    - Zmiana statusu masowa  
- [x] 6.4 Mechanizm tagów/etykiet (wspólny z Kontrahentami i Produktami)  
- [x] 6.5 Eksport do Excela (lista faktur/kosztów)  

## 7. Produkty  
- [x] 7.1 Przeglądanie listy produktów z filtrowaniem i przeszukiwaniem (wspólny mechanizm z Common)  
- [x] 7.2 Dodawanie, edycja i usuwanie produktów  
- [x] 7.3 Obsługa obrazków produktów  
- [x] 7.4 Mechanizm tagów/etykiet (wspólny z Kontrahentami i Fakturami/Kosztami)  
- [x] 7.5 Mechanizm komentarzy przy produktach  
- [x] 7.6 Eksport do Excela (lista produktów)  

## 8. Globalne Kontakty (In progress)  
- [ ] 8.1 Zarządzanie globalnymi kontaktami (tworzenie, edycja, usuwanie)  
- [ ] 8.2 Powiązanie globalnych kontaktów z kontrahentami  
- [ ] 8.3 Integracja z systemem adresów i osób kontaktowych  
- [ ] 8.4 Przeszukiwanie i paginacja globalnych kontaktów  

## 9. Komunikacja i Chat  
- [ ] 9.1 System chatu pomiędzy użytkownikami  
- [x] 9.2 Chat AI (integracja z OpenRouter)  

## 10. Projekty  
- [ ] 10.1 Zarządzanie projektami (tworzenie, edycja, usuwanie)  
- [ ] 10.2 Zarządzanie zadaniami w projektach  
- [ ] 10.3 Rejestracja czasu pracy (timesheet)  

## 11. Subscription  
- [x] 11.1 Różne plany subskrypcyjne (np. Free, Basic, Pro, Enterprise)  
- [ ] 11.2 Zarządzanie subskrypcją w panelu użytkownika  
- [x] 11.3 Płatności online przez Stripe (karty kredytowe, faktury)  
- [ ] 11.4 Automatyczne odnawianie subskrypcji  
- [ ] 11.5 Blokady funkcji po wygaśnięciu płatności  
