## 🛡️ Wdrożenie filtrowania wulgarnych treści (PL/EN/RU/UA)  
**Cel:** Zapewnić filtrowanie treści zawierających niepożądane/wulgarne słowa w aplikacji (komentarze, opisy itp.), z obsługą wielu języków i gotowymi listami słów.  

---

### ✅ Zakres
1. **Instalacja paczki `wpm/laravel-profanity-filter`**  
   - Dokumentacja: https://github.com/WPMedia/laravel-profanity-filter

2. **Pobranie i integracja słowników z repozytorium LDNOOBW:**  
   Źródło: [https://github.com/LDNOOBW/List-of-Dirty-Naughty-Obscene-and-Otherwise-Bad-Words](https://github.com/LDNOOBW/List-of-Dirty-Naughty-Obscene-and-Otherwise-Bad-Words)
   - Języki:
     - `en`: `en`
     - `pl`: `pl`
     - `ru`: `ru`
     - `ua`: `uk` (ukraiński)

3. **Umieszczenie słowników w katalogu:**
   ```
   storage/app/badwords/
   ├── en.txt
   ├── pl.txt
   ├── ru.txt
   └── ua.txt
   ```

4. **Publikacja i edycja konfiguracji:**
   - Plik `config/profanity-filter.php`
   - Wskazanie ścieżek do plików słów:
     ```php
     'lang' => ['en', 'pl', 'ru', 'ua'],
     'custom_badwords_path' => [
         'en' => storage_path('app/badwords/en.txt'),
         'pl' => storage_path('app/badwords/pl.txt'),
         'ru' => storage_path('app/badwords/ru.txt'),
         'ua' => storage_path('app/badwords/ua.txt'),
     ],
     ```

5. **Zastosowanie walidatora (np. w `FormRequest`)**
   - Reguła dla pola `content`, `description`, `comment` itp.:
     ```php
     use WPM\LaravelProfanityFilter\ProfanityFilter;

     function rules(): array
     {
         return [
             'content' => ['required', function ($attribute, $value, $fail) {
                 if ((new ProfanityFilter())->hasProfanity($value)) {
                     $fail('Treść zawiera niedozwolone słowa.');
                 }
             }]
         ];
     }
     ```

6. **(Opcjonalnie) Asynchroniczna obsługa**
   - Utworzenie `job`a, np. `DetectProfanityJob`, który:
     - Przyjmuje model i pole tekstowe
     - Sprawdza czy treść zawiera niedozwolone słowa
     - Oznacza wpis jako wymagający moderacji (`is_flagged = true`)
     - Może być uruchamiany po zapisaniu modelu (np. `observer`)

---

### 📂 Pliki do utworzenia
- `storage/app/badwords/{en,pl,ru,ua}.txt`
- `app/Jobs/DetectProfanityJob.php` (jeśli używany)

---

### ⚙️ Dodatkowe uwagi
- Maskowanie wulgaryzmów (`filterText()`) można wdrożyć później, w zależności od decyzji o prezentacji treści.
- W przyszłości możliwe rozszerzenie o:
  - Panel admina do edycji list słów
  - Obsługę dynamicznych słowników z bazy danych
  - Moderację ręczną oznaczonych treści

---

### ⏳ Szacowany czas:
- Implementacja podstawowa: 1–2h  
- Integracja jobów i obserwatorów: dodatkowe 1–2h