## 🛡️ Wdrożenie filtrowania wulgarnych treści (PL/EN/RU/UA)  
**Cel:** Zapewnić filtrowanie treści zawierających niepożądane/wulgarne słowa w aplikacji (komentarze, opisy itp.), z obsługą wielu języków i gotowymi listami słów.  

---

### ✅ Zakres
1. **Instalacja paczki `consoletvs/profanity`**  
   - Dokumentacja: https://github.com/ConsoleTVs/Profanity
   - Pakiet zawiera gotowe listy słów w wielu językach

2. **Utworzenie własnego słownika wulgaryzmów:**
   - Plik `storage/app/profanity/dictionary.json` zawiera słowa w językach:
     - `en`: angielski
     - `pl`: polski
     - `ru`: rosyjski
     - `ua`: ukraiński
   - Format słownika:
     ```json
     [
         {
             "language": "en",
             "word": "xxx"
         },
         {
             "language": "pl",
             "word": "xxx"
         }
     ]
     ```

3. **Publikacja konfiguracji:**
   ```bash
   php artisan vendor:publish --provider="ConsoleTVs\Profanity\ProfanityServiceProvider"
   ```

4. **Zastosowanie walidatora (np. w `FormRequest`)**
   - Reguła dla pola `content`, `description`, `comment` itp.:
     ```php
     use App\Rules\NoProfanity;

     function rules(): array
     {
         return [
             'content' => ['required', new NoProfanity()]
         ];
     }
     ```
   - Zaimplementowano w:
     - [x] StoreFeedRequest
     - [x] ProductCommentRequest
     - [x] ContractorCommentRequest

5. **(Opcjonalnie) Asynchroniczna obsługa**
   - Utworzono `job`a `DetectProfanityJob`, który:
     - Przyjmuje model i pole tekstowe
     - Sprawdza czy treść zawiera niedozwolone słowa
     - Oznacza wpis jako wymagający moderacji (`is_flagged = true`)
     - Może być uruchamiany po zapisaniu modelu (np. `observer`)
   - Dodano trait `HasProfanityCheck` do modeli:
     - [x] Feed
     - [x] Comment (używany przez ProductComment i ContractorComment)

---

### 📂 Utworzone pliki
- [x] `app/Services/ProfanityFilterService.php`
- [x] `app/Rules/NoProfanity.php`
- [x] `app/Traits/HasProfanityCheck.php`
- [x] `app/Jobs/DetectProfanityJob.php`
- [x] `storage/app/profanity/dictionary.json`

---

### ⚙️ Dodatkowe uwagi
- Maskowanie wulgaryzmów (`filterText()`) można wdrożyć później, w zależności od decyzji o prezentacji treści.
- W przyszłości możliwe rozszerzenie o:
  - Panel admina do edycji list słów
  - Obsługę dynamicznych słowników z bazy danych
  - Moderację ręczną oznaczonych treści

---

### ⏳ Szacowany czas:
- Implementacja podstawowa: 1h  
- Integracja jobów i obserwatorów: dodatkowe 1h
