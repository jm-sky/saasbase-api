```php
<?php

// resources/lang/en/invoices.php
return [
    'invoice' => 'INVOICE',
    'from' => 'From:',
    'to' => 'To:',
    'bill_to' => 'Bill To:',
    'ship_to' => 'Ship To:',
    'date' => 'Date:',
    'due_date' => 'Due Date:',
    'issue_date' => 'Issue Date:',
    'invoice_number' => 'Invoice Number:',
    'description' => 'Description',
    'quantity' => 'Qty',
    'unit_price' => 'Unit Price',
    'total_net' => 'Net Amount',
    'total_vat' => 'VAT Amount',
    'total_gross' => 'Gross Amount',
    'subtotal' => 'Subtotal:',
    'vat' => 'VAT',
    'total' => 'Total:',
    'vat_summary' => 'VAT Summary',
    'vat_rate' => 'VAT Rate',
    'net_amount' => 'Net Amount',
    'vat_amount' => 'VAT Amount',
    'gross_amount' => 'Gross Amount',
    'payment_terms' => 'Payment Terms:',
    'payment_method' => 'Payment Method:',
    'payment_status' => 'Payment Status:',
    'bank_account' => 'Bank Account:',
    'iban' => 'IBAN:',
    'swift' => 'SWIFT:',
    'bank_name' => 'Bank:',
    'notes' => 'Notes:',
    'signature' => 'Signature:',
    'authorized_by' => 'Authorized by:',
    'received_by' => 'Received by:',
    'page' => 'Page',
    'currency' => 'Currency:',
    'exchange_rate' => 'Exchange Rate:',
    'tax_id' => 'Tax ID:',
    'reference' => 'Reference:',
    'paid_amount' => 'Paid Amount:',
    'paid_date' => 'Paid Date:',
    'company_details' => 'Company Details',
    'payment_details' => 'Payment Details',
    'invoice_details' => 'Invoice Details',
    
    // Template Editor
    'template_editor' => 'Invoice Template Editor',
    'template_name' => 'Template Name',
    'template_name_placeholder' => 'Enter template name...',
    'template_description_placeholder' => 'Optional description...',
    'load_template' => 'Load Template',
    'select_template' => 'Select a template...',
    'system_templates' => 'System Templates',
    'user_templates' => 'User Templates',
    'template_content' => 'Template Content',
    'template_content_placeholder' => 'Enter your Handlebars template here...',
    'click_preview' => 'Click "Preview" to see your template',
    'enter_template_content' => 'Please enter template content first',
    'template_saved_successfully' => 'Template saved successfully!',
    
    // Preview Options
    'preview_options' => 'Preview Options',
    'include_logo' => 'Include Logo',
    'include_signatures' => 'Include Signatures',
    'color_schemes' => 'Color Schemes',
    'accent_color' => 'Accent Color',
    'secondary_color' => 'Secondary Color',
    'date_format' => 'Date Format',
    
    // Helper Functions
    'available_helpers' => 'Available Helper Functions',
    'translations' => 'Translations',
    'translatable_text' => 'Translatable text',
    'from_label' => '"From:" label',
    'to_label' => '"To:" label',
    'total_label' => '"Total:" label',
    'invoice_data' => 'Invoice Data',
    'seller_name' => 'Seller company name',
    'buyer_name' => 'Buyer company name',
    'formatted_total' => 'Formatted total amount',
    'conditionals' => 'Conditional Logic',
    'conditional_blocks' => 'Conditional blocks',
    'loop_arrays' => 'Loop through arrays',
    'images_media' => 'Images & Media',
    'check_logo_exists' => 'Check if logo exists',
    'display_logo' => 'Display logo image',
    'display_signature' => 'Display signature image',
    
    // Advanced Features
    'advanced_features' => 'Advanced Template Features',
    'multilingual' => 'Multilingual Support',
    'custom_colors' => 'Custom Colors',
    'accent_background' => 'Accent background color',
    'accent_text_color' => 'Accent text color',
    'accent_border' => 'Accent border color',
    'secondary_text' => 'Secondary text color',
    'invoice_structure' => 'Invoice Structure',
    'line_items' => 'Line Items',
    'loop_line_items' => 'Loop through line items',
    'item_description' => 'Item description',
    'formatted_net_total' => 'Formatted net total',
    'vat_summary_loop' => 'Loop through VAT summary',
    'signatures_section' => 'Signatures',
    'signature_check' => 'Check if signatures enabled',
    'issuer_name' => 'Issuer signature name',
    'receiver_name' => 'Receiver signature name',
    'payment_info_check' => 'Check if payment info exists',
    'exchange_info_check' => 'Check if exchange info exists',
    'description_check' => 'Check if description exists',
    'page_numbering' => 'Page Numbering',
    'current_page' => 'Current page number',
    'total_pages' => 'Total pages count',
    'auto_footer' => 'Automatically added to footer',
    'payment_details' => 'Payment Details',
    'bank_iban' => 'Bank IBAN number',
    'paid_amount' => 'Formatted paid amount',
    
    // Important Notes
    'important_notes' => 'Important Notes',
    'note_calculations' => 'All calculations are done server-side, templates receive formatted values',
    'note_media_urls' => 'Media URLs are automatically generated from Spatie Media Library',
    'note_tenant_isolation' => 'Templates are isolated per tenant for security',
    'note_page_numbering' => 'Page numbering is automatically handled by PDF generator',
    
    // Draft functionality
    'load_unsaved_draft' => 'Load unsaved draft?',
    
    // Language
    'language' => 'Language',
    'currency' => 'Currency',
    'timezone' => 'Timezone',
    
    // Errors
    'template_not_found' => 'Template not found',
    'unauthorized_template_access' => 'You can only access your own templates',
    'preview_failed' => 'Preview generation failed',
    'save_failed' => 'Failed to save template',
    'failed_to_load_templates' => 'Failed to load templates',
    'failed_to_load_template' => 'Failed to load template',
];

// resources/lang/pl/invoices.php
return [
    'invoice' => 'FAKTURA',
    'from' => 'Od:',
    'to' => 'Do:',
    'bill_to' => 'Fakturowane do:',
    'ship_to' => 'Wysyłane do:',
    'date' => 'Data:',
    'due_date' => 'Termin płatności:',
    'issue_date' => 'Data wystawienia:',
    'invoice_number' => 'Numer faktury:',
    'description' => 'Opis',
    'quantity' => 'Ilość',
    'unit_price' => 'Cena jedn.',
    'total_net' => 'Kwota netto',
    'total_vat' => 'Kwota VAT',
    'total_gross' => 'Kwota brutto',
    'subtotal' => 'Suma częściowa:',
    'vat' => 'VAT',
    'total' => 'Suma:',
    'vat_summary' => 'Podsumowanie VAT',
    'vat_rate' => 'Stawka VAT',
    'net_amount' => 'Kwota netto',
    'vat_amount' => 'Kwota VAT',
    'gross_amount' => 'Kwota brutto',
    'payment_terms' => 'Warunki płatności:',
    'payment_method' => 'Sposób płatności:',
    'payment_status' => 'Status płatności:',
    'bank_account' => 'Rachunek bankowy:',
    'iban' => 'IBAN:',
    'swift' => 'SWIFT:',
    'bank_name' => 'Bank:',
    'notes' => 'Uwagi:',
    'signature' => 'Podpis:',
    'authorized_by' => 'Autoryzowane przez:',
    'received_by' => 'Odebrane przez:',
    'page' => 'Strona',
    'currency' => 'Waluta:',
    'exchange_rate' => 'Kurs wymiany:',
    'tax_id' => 'NIP:',
    'reference' => 'Referencja:',
    'paid_amount' => 'Kwota zapłacona:',
    'paid_date' => 'Data płatności:',
    'company_details' => 'Dane firmy',
    'payment_details' => 'Szczegóły płatności',
    'invoice_details' => 'Szczegóły faktury',
    
    // Template Editor
    'template_editor' => 'Edytor Szablonów Faktur',
    'template_name' => 'Nazwa Szablonu',
    'template_name_placeholder' => 'Wprowadź nazwę szablonu...',
    'template_description_placeholder' => 'Opcjonalny opis...',
    'load_template' => 'Wczytaj Szablon',
    'select_template' => 'Wybierz szablon...',
    'system_templates' => 'Szablony Systemowe',
    'user_templates' => 'Szablony Użytkownika',
    'template_content' => 'Zawartość Szablonu',
    'template_content_placeholder' => 'Wprowadź swój szablon Handlebars tutaj...',
    'click_preview' => 'Kliknij "Podgląd" aby zobaczyć szablon',
    'enter_template_content' => 'Proszę najpierw wprowadzić zawartość szablonu',
    'template_saved_successfully' => 'Szablon został pomyślnie zapisany!',
    
    // Preview Options
    'preview_options' => 'Opcje Podglądu',
    'include_logo' => 'Dołącz Logo',
    'include_signatures' => 'Dołącz Podpisy',
    'color_schemes' => 'Schematy Kolorów',
    'accent_color' => 'Kolor Akcentu',
    'secondary_color' => 'Kolor Drugorzędny',
    'date_format' => 'Format Daty',
    
    // Helper Functions
    'available_helpers' => 'Dostępne Funkcje Pomocnicze',
    'translations' => 'Tłumaczenia',
    'translatable_text' => 'Tekst do tłumaczenia',
    'from_label' => 'Etykieta "Od:"',
    'to_label' => 'Etykieta "Do:"',
    'total_label' => 'Etykieta "Suma:"',
    'invoice_data' => 'Dane Faktury',
    'seller_name' => 'Nazwa firmy sprzedawcy',
    'buyer_name' => 'Nazwa firmy kupującego',
    'formatted_total' => 'Sformatowana kwota całkowita',
    'conditionals' => 'Logika Warunkowa',
    'conditional_blocks' => 'Bloki warunkowe',
    'loop_arrays' => 'Pętla przez tablice',
    'images_media' => 'Obrazy i Media',
    'check_logo_exists' => 'Sprawdź czy logo istnieje',
    'display_logo' => 'Wyświetl logo',
    'display_signature' => 'Wyświetl podpis',
    
    // Advanced Features
    'advanced_features' => 'Zaawansowane Funkcje Szablonu',
    'multilingual' => 'Wsparcie Wielojęzyczne',
    'custom_colors' => 'Kolory Niestandardowe',
    'accent_background' => 'Kolor tła akcentu',
    'accent_text_color' => 'Kolor tekstu akcentu',
    'accent_border' => 'Kolor obramowania akcentu',
    'secondary_text' => 'Kolor tekstu drugorzędnego',
    'invoice_structure' => 'Struktura Faktury',
    'line_items' => 'Pozycje Faktury',
    'loop_line_items' => 'Pętla przez pozycje faktury',
    'item_description' => 'Opis pozycji',
    'formatted_net_total' => 'Sformatowana suma netto',
    'vat_summary_loop' => 'Pętla przez podsumowanie VAT',
    'signatures_section' => 'Podpisy',
    'signature_check' => 'Sprawdź czy podpisy są włączone',
    'issuer_name' => 'Nazwa podpisu wystawcy',
    'receiver_name' => 'Nazwa podpisu odbiorcy',
    'payment_info_check' => 'Sprawdź czy istnieją informacje o płatności',
    'exchange_info_check' => 'Sprawdź czy istnieją informacje o wymianie',
    'description_check' => 'Sprawdź czy istnieje opis',
    'page_numbering' => 'Numerowanie Stron',
    'current_page' => 'Numer bieżącej strony',
    'total_pages' => 'Liczba wszystkich stron',
    'auto_footer' => 'Automatycznie dodane do stopki',
    'payment_details' => 'Szczegóły Płatności',
    'bank_iban' => 'Numer IBAN banku',
    'paid_amount' => 'Sformatowana zapłacona kwota',
    
    // Important Notes
    'important_notes' => 'Ważne Uwagi',
    'note_calculations' => 'Wszystkie obliczenia są wykonywane po stronie serwera, szablony otrzymują sformatowane wartości',
    'note_media_urls' => 'Adresy URL mediów są automatycznie generowane z Spatie Media Library',
    'note_tenant_isolation' => 'Szablony są izolowane per tenant dla bezpieczeństwa',
    'note_page_numbering' => 'Numerowanie stron jest automatycznie obsługiwane przez generator PDF',
    
    // Draft functionality
    'load_unsaved_draft' => 'Wczytać niezapisany projekt?',
    
    // Language
    'language' => 'Język',
    'currency' => 'Waluta',
    'timezone' => 'Strefa Czasowa',
    
    // Errors
    'template_not_found' => 'Szablon nie znaleziony',
    'unauthorized_template_access' => 'Możesz uzyskać dostęp tylko do swoich szablonów',
    'preview_failed' => 'Generowanie podglądu nie powiodło się',
    'save_failed' => 'Zapisanie szablonu nie powiodło się',
    'failed_to_load_templates' => 'Nie udało się wczytać szablonów',
    'failed_to_load_template' => 'Nie udało się wczytać szablonu',
];

// resources/lang/uk/invoices.php
return [
    'invoice' => 'РАХУНОК-ФАКТУРА',
    'from' => 'Від:',
    'to' => 'До:',
    'bill_to' => 'Рахунок до:',
    'ship_to' => 'Доставка до:',
    'date' => 'Дата:',
    'due_date' => 'Термін оплати:',
    'issue_date' => 'Дата видачі:',
    'invoice_number' => 'Номер рахунку:',
    'description' => 'Опис',
    'quantity' => 'Кількість',
    'unit_price' => 'Ціна за од.',
    'total_net' => 'Сума нетто',
    'total_vat' => 'Сума ПДВ',
    'total_gross' => 'Сума брутто',
    'subtotal' => 'Проміжна сума:',
    'vat' => 'ПДВ',
    'total' => 'Загалом:',
    'vat_summary' => 'Підсумок ПДВ',
    'vat_rate' => 'Ставка ПДВ',
    'net_amount' => 'Сума нетто',
    'vat_amount' => 'Сума ПДВ',
    'gross_amount' => 'Сума брутто',
    'payment_terms' => 'Умови оплати:',
    'payment_method' => 'Спосіб оплати:',
    'payment_status' => 'Статус оплати:',
    'bank_account' => 'Банківський рахунок:',
    'iban' => 'IBAN:',
    'swift' => 'SWIFT:',
    'bank_name' => 'Банк:',
    'notes' => 'Примітки:',
    'signature' => 'Підпис:',
    'authorized_by' => 'Авторизовано:',
    'received_by' => 'Отримано:',
    'page' => 'Сторінка',
    'currency' => 'Валюта:',
    'exchange_rate' => 'Курс обміну:',
    'tax_id' => 'Податковий номер:',
    'reference' => 'Довідка:',
    'paid_amount' => 'Сплачена сума:',
    'paid_date' => 'Дата оплати:',
    'company_details' => 'Деталі компанії',
    'payment_details' => 'Деталі оплати',
    'invoice_details' => 'Деталі рахунку',
    
    // Template Editor
    'template_editor' => 'Редактор Шаблонів Рахунків',
    'template_name' => 'Назва Шаблону',
    'template_name_placeholder' => 'Введіть назву шаблону...',
    'template_description_placeholder' => 'Опціональний опис...',
    'load_template' => 'Завантажити Шаблон',
    'select_template' => 'Оберіть шаблон...',
    'system_templates' => 'Системні Шаблони',
    'user_templates' => 'Шаблони Користувача',
    'template_content' => 'Вміст Шаблону',
    'template_content_placeholder' => 'Введіть ваш шаблон Handlebars тут...',
    'click_preview' => 'Натисніть "Попередній перегляд" щоб побачити шаблон',
    'enter_template_content' => 'Будь ласка, спочатку введіть вміст шаблону',
    'template_saved_successfully' => 'Шаблон успішно збережено!',
    
    // Preview Options
    'preview_options' => 'Опції Попереднього Перегляду',
    'include_logo' => 'Включити Логотип',
    'include_signatures' => 'Включити Підписи',
    'color_schemes' => 'Кольорові Схеми',
    'accent_color' => 'Акцентний Колір',
    'secondary_color' => 'Вторинний Колір',
    'date_format' => 'Формат Дати',
    
    // Helper Functions
    'available_helpers' => 'Доступні Допоміжні Функції',
    'translations' => 'Переклади',
    'translatable_text' => 'Текст для перекладу',
    'from_label' => 'Мітка "Від:"',
    'to_label' => 'Мітка "До:"',
    'total_label' => 'Мітка "Загалом:"',
    'invoice_data' => 'Дані Рахунку',
    'seller_name' => 'Назва компанії продавця',
    'buyer_name' => 'Назва компанії покупця',
    'formatted_total' => 'Відформатована загальна сума',
    'conditionals' => 'Умовна Логіка',
    'conditional_blocks' => 'Умовні блоки',
    'loop_arrays' => 'Цикл через масиви',
    'images_media' => 'Зображення та Медіа',
    'check_logo_exists' => 'Перевірити чи існує логотип',
    'display_logo' => 'Відобразити логотип',
    'display_signature' => 'Відобразити підпис',
    
    // Advanced Features
    'advanced_features' => 'Розширені Функції Шаблону',
    'multilingual' => 'Багатомовна Підтримка',
    'custom_colors' => 'Користувацькі Кольори',
    'accent_background' => 'Колір фону акценту',
    'accent_text_color' => 'Колір тексту акценту',
    'accent_border' => 'Колір межі акценту',
    'secondary_text' => 'Колір вторинного тексту',
    'invoice_structure' => 'Структура Рахунку',
    'line_items' => 'Позиції Рахунку',
    'loop_line_items' => 'Цикл через позиції рахунку',
    'item_description' => 'Опис позиції',
    'formatted_net_total' => 'Відформатована сума нетто',
    'vat_summary_loop' => 'Цикл через підсумок ПДВ',
    'signatures_section' => 'Підписи',
    'signature_check' => 'Перевірити чи увімкнені підписи',
    'issuer_name' => 'Ім\'я підпису видавця',
    'receiver_name' => 'Ім\'я підпису отримувача',
    'payment_info_check' => 'Перевірити чи існує інформація про оплату',
    'exchange_info_check' => 'Перевірити чи існує інформація про обмін',
    'description_check' => 'Перевірити чи існує опис',
    'page_numbering' => 'Нумерація Сторінок',
    'current_page' => 'Номер поточної сторінки',
    'total_pages' => 'Загальна кількість сторінок',
    'auto_footer' => 'Автоматично додається до нижнього колонтитулу',
    'payment_details' => 'Деталі Оплати',
    'bank_iban' => 'Номер IBAN банку',
    'paid_amount' => 'Відформатована сплачена сума',
    
    // Important Notes
    'important_notes' => 'Важливі Примітки',
    'note_calculations' => 'Всі розрахунки виконуються на стороні сервера, шаблони отримують відформатовані значення',
    'note_media_urls' => 'URL медіа автоматично генеруються з Spatie Media Library',
    'note_tenant_isolation' => 'Шаблони ізольовані per tenant для безпеки',
    'note_page_numbering' => 'Нумерація сторінок автоматично обробляється генератором PDF',
    
    // Draft functionality
    'load_unsaved_draft' => 'Завантажити незбережений проект?',
    
    // Language
    'language' => 'Мова',
    'currency' => 'Валюта',
    'timezone' => 'Часовий Пояс',
    
    // Errors
    'template_not_found' => 'Шаблон не знайдено',
    'unauthorized_template_access' => 'Ви можете отримати доступ лише до своїх шаблонів',
    'preview_failed' => 'Генерація попереднього перегляду не вдалася',
    'save_failed' => 'Не вдалося зберегти шаблон',
    'failed_to_load_templates' => 'Не вдалося завантажити шаблони',
    'failed_to_load_template' => 'Не вдалося завантажити шаблон',
];

// resources/lang/ru/invoices.php
return [
    'invoice' => 'СЧЕТ-ФАКТУРА',
    'from' => 'От:',
    'to' => 'Кому:',
    'bill_to' => 'Счет к:',
    'ship_to' => 'Доставка к:',
    'date' => 'Дата:',
    'due_date' => 'Срок оплаты:',
    'issue_date' => 'Дата выдачи:',
    'invoice_number' => 'Номер счета:',
    'description' => 'Описание',
    'quantity' => 'Количество',
    'unit_price' => 'Цена за ед.',
    'total_net' => 'Сумма нетто',
    'total_vat' => 'Сумма НДС',
    'total_gross' => 'Сумма брутто',
    'subtotal' => 'Промежуточная сумма:',
    'vat' => 'НДС',
    'total' => 'Итого:',
    'vat_summary' => 'Итоги НДС',
    'vat_rate' => 'Ставка НДС',
    'net_amount' => 'Сумма нетто',
    'vat_amount' => 'Сумма НДС',
    'gross_amount' => 'Сумма брутто',
    'payment_terms' => 'Условия оплаты:',
    'payment_method' => 'Способ оплаты:',
    'payment_status' => 'Статус оплаты:',
    'bank_account' => 'Банковский счет:',
    'iban' => 'IBAN:',
    'swift' => 'SWIFT:',
    'bank_name' => 'Банк:',
    'notes' => 'Примечания:',
    'signature' => 'Подпись:',
    'authorized_by' => 'Авторизовано:',
    'received_by' => 'Получено:',
    'page' => 'Страница',
    'currency' => 'Валюта:',
    'exchange_rate' => 'Курс обмена:',
    'tax_id' => 'Налоговый номер:',
    'reference' => 'Справка:',
    'paid_amount' => 'Оплаченная сумма:',
    'paid_date' => 'Дата оплаты:',
    'company_details' => 'Реквизиты компании',
    'payment_details' => 'Детали оплаты',
    'invoice_details' => 'Детали счета',
    
    // Template Editor
    'template_editor' => 'Редактор Шаблонов Счетов',
    'template_name' => 'Название Шаблона',
    'template_name_placeholder' => 'Введите название шаблона...',
    'template_description_placeholder' => 'Опциональное описание...',
    'load_template' => 'Загрузить Шаблон',
    'select_template' => 'Выберите шаблон...',
    'system_templates' => 'Системные Шаблоны',
    'user_templates' => 'Шаблоны Пользователя',
    'template_content' => 'Содержимое Шаблона',
    'template_content_placeholder' => 'Введите ваш шаблон Handlebars здесь...',
    'click_preview' => 'Нажмите "Предварительный просмотр" чтобы увидеть шаблон',
    'enter_template_content' => 'Пожалуйста, сначала введите содержимое шаблона',
    'template_saved_successfully' => 'Шаблон успешно сохранен!',
    
    // Preview Options
    'preview_options' => 'Опции Предварительного Просмотра',
    'include_logo' => 'Включить Логотип',
    'include_signatures' => 'Включить Подписи',
    'color_schemes' => 'Цветовые Схемы',
    'accent_color' => 'Акцентный Цвет',
    'secondary_color' => 'Вторичный Цвет',
    'date_format' => 'Формат Даты',
    
    // Helper Functions
    'available_helpers' => 'Доступные Вспомогательные Функции',
    'translations' => 'Переводы',
    'translatable_text' => 'Переводимый текст',
    'from_label' => 'Метка "От:"',
    'to_label' => 'Метка "Кому:"',
    'total_label' => 'Метка "Итого:"',
    'invoice_data' => 'Данные Счета',
    'seller_name' => 'Название компании продавца',
    'buyer_name' => 'Название компании покупателя',
    'formatted_total' => 'Отформатированная общая сумма',
    'conditionals' => 'Условная Логика',
    'conditional_blocks' => 'Условные блоки',
    'loop_arrays' => 'Цикл через массивы',
    'images_media' => 'Изображения и Медиа',
    'check_logo_exists' => 'Проверить существует ли логотип',
    'display_logo' => 'Отобразить логотип',
    'display_signature' => 'Отобразить подпись',
    
    // Advanced Features
    'advanced_features' => 'Расширенные Функции Шаблона',
    'multilingual' => 'Многоязычная Поддержка',
    'custom_colors' => 'Пользовательские Цвета',
    'accent_background' => 'Цвет фона акцента',
    'accent_text_color' => 'Цвет текста акцента',
    'accent_border' => 'Цвет границы акцента',
    'secondary_text' => 'Цвет вторичного текста',
    'invoice_structure' => 'Структура Счета',
    'line_items' => 'Позиции Счета',
    'loop_line_items' => 'Цикл через позиции счета',
    'item_description' => 'Описание позиции',
    'formatted_net_total' => 'Отформатированная сумма нетто',
    'vat_summary_loop' => 'Цикл через итоги НДС',
    'signatures_section' => 'Подписи',
    'signature_check' => 'Проверить включены ли подписи',
    'issuer_name' => 'Имя подписи выдавшего',
    'receiver_name' => 'Имя подписи получателя',
    'payment_info_check' => 'Проверить существует ли информация об оплате',
    'exchange_info_check' => 'Проверить существует ли информация об обмене',
    'description_check' => 'Проверить существует ли описание',
    'page_numbering' => 'Нумерация Страниц',
    'current_page' => 'Номер текущей страницы',
    'total_pages' => 'Общее количество страниц',
    'auto_footer' => 'Автоматически добавляется в нижний колонтитул',
    'payment_details' => 'Детали Оплаты',
    'bank_iban' => 'Номер IBAN банка',
    'paid_amount' => 'Отформатированная оплаченная сумма',
    
    // Important Notes
    'important_notes' => 'Важные Примечания',
    'note_calculations' => 'Все расчеты выполняются на стороне сервера, шаблоны получают отформатированные значения',
    'note_media_urls' => 'URL медиа автоматически генерируются из Spatie Media Library',
    'note_tenant_isolation' => 'Шаблоны изолированы per tenant для безопасности',
    'note_page_numbering' => 'Нумерация страниц автоматически обрабатывается генератором PDF',
    
    // Draft functionality
    'load_unsaved_draft' => 'Загрузить несохраненный проект?',
    
    // Language
    'language' => 'Язык',
    'currency' => 'Валюта',
    'timezone' => 'Часовой Пояс',
    
    // Errors
    'template_not_found' => 'Шаблон не найден',
    'unauthorized_template_access' => 'Вы можете получить доступ только к своим шаблонам',
    'preview_failed' => 'Генерация предварительного просмотра не удалась',
    'save_failed' => 'Не удалось сохранить шаблон',
    'failed_to_load_templates' => 'Не удалось загрузить шаблоны',
    'failed_to_load_template' => 'Не удалось загрузить шаблон',
];

// resources/lang/en/common.php
return [
    'loading' => 'Loading...',
    'preview' => 'Preview',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'create' => 'Create',
    'update' => 'Update',
    'description' => 'Description',
    'name' => 'Name',
    'actions' => 'Actions',
    'status' => 'Status',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'yes' => 'Yes',
    'no' => 'No',
    'confirm' => 'Confirm',
    'success' => 'Success',
    'error' => 'Error',
    'warning' => 'Warning',
    'info' => 'Info',
];

// resources/lang/pl/common.php
return [
    'loading' => 'Ładowanie...',
    'preview' => 'Podgląd',
    'save' => 'Zapisz',
    'cancel' => 'Anuluj',
    'edit' => 'Edytuj',
    'delete' => 'Usuń',
    'create' => 'Utwórz',
    'update' => 'Aktualizuj',
    'description' => 'Opis',
    'name' => 'Nazwa',
    'actions' => 'Akcje',
    'status' => 'Status',
    'active' => 'Aktywny',
    'inactive' => 'Nieaktywny',
    'yes' => 'Tak',
    'no' => 'Nie',
    'confirm' => 'Potwierdź',
    'success' => 'Sukces',
    'error' => 'Błąd',
    'warning' => 'Ostrzeżenie',
    'info' => 'Informacja',
];

// resources/lang/uk/common.php
return [
    'loading' => 'Завантаження...',
    'preview' => 'Попередній перегляд',
    'save' => 'Зберегти',
    'cancel' => 'Скасувати',
    'edit' => 'Редагувати',
    'delete' => 'Видалити',
    'create' => 'Створити',
    'update' => 'Оновити',
    'description' => 'Опис',
    'name' => 'Назва',
    'actions' => 'Дії',
    'status' => 'Статус',
    'active' => 'Активний',
    'inactive' => 'Неактивний',
    'yes' => 'Так',
    'no' => 'Ні',
    'confirm' => 'Підтвердити',
    'success' => 'Успіх',
    'error' => 'Помилка',
    'warning' => 'Попередження',
    'info' => 'Інформація',
];

// resources/lang/ru/common.php
return [
    'loading' => 'Загрузка...',
    'preview' => 'Предварительный просмотр',
    'save' => 'Сохранить',
    'cancel' => 'Отменить',
    'edit' => 'Редактировать',
    'delete' => 'Удалить',
    'create' => 'Создать',
    'update' => 'Обновить',
    'description' => 'Описание',
    'name' => 'Название',
    'actions' => 'Действия',
    'status' => 'Статус',
    'active' => 'Активный',
    'inactive' => 'Неактивный',
    'yes' => 'Да',
    'no' => 'Нет',
    'confirm' => 'Подтвердить',
    'success' => 'Успех',
    'error' => 'Ошибка',
    'warning' => 'Предупреждение',
    'info' => 'Информация',
];

// resources/lang/en/errors.php
return [
    'template_not_found' => 'Template not found',
    'unauthorized_template_access' => 'You can only access your own templates',
    'preview_failed' => 'Preview generation failed',
    'save_failed' => 'Failed to save template',
    'failed_to_load_templates' => 'Failed to load templates',
    'failed_to_load_template' => 'Failed to load template',
    'no_tenant_context' => 'No tenant context available',
    'invalid_template_data' => 'Invalid template data provided',
    'template_compilation_error' => 'Template compilation error',
];

// resources/lang/pl/errors.php
return [
    'template_not_found' => 'Szablon nie znaleziony',
    'unauthorized_template_access' => 'Możesz uzyskać dostęp tylko do swoich szablonów',
    'preview_failed' => 'Generowanie podglądu nie powiodło się',
    'save_failed' => 'Zapisanie szablonu nie powiodło się',
    'failed_to_load_templates' => 'Nie udało się wczytać szablonów',
    'failed_to_load_template' => 'Nie udało się wczytać szablonu',
    'no_tenant_context' => 'Brak kontekstu najemcy',
    'invalid_template_data' => 'Podano nieprawidłowe dane szablonu',
    'template_compilation_error' => 'Błąd kompilacji szablonu',
];

// resources/lang/uk/errors.php
return [
    'template_not_found' => 'Шаблон не знайдено',
    'unauthorized_template_access' => 'Ви можете отримати доступ лише до своїх шаблонів',
    'preview_failed' => 'Генерація попереднього перегляду не вдалася',
    'save_failed' => 'Не вдалося зберегти шаблон',
    'failed_to_load_templates' => 'Не вдалося завантажити шаблони',
    'failed_to_load_template' => 'Не вдалося завантажити шаблон',
    'no_tenant_context' => 'Немає контексту орендаря',
    'invalid_template_data' => 'Надано недійсні дані шаблону',
    'template_compilation_error' => 'Помилка компіляції шаблону',
];

// resources/lang/ru/errors.php
return [
    'template_not_found' => 'Шаблон не найден',
    'unauthorized_template_access' => 'Вы можете получить доступ только к своим шаблонам',
    'preview_failed' => 'Генерация предварительного просмотра не удалась',
    'save_failed' => 'Не удалось сохранить шаблон',
    'failed_to_load_templates' => 'Не удалось загрузить шаблоны',
    'failed_to_load_template' => 'Не удалось загрузить шаблон',
    'no_tenant_context' => 'Нет контекста арендатора',
    'invalid_template_data' => 'Предоставлены недействительные данные шаблона',
    'template_compilation_error' => 'Ошибка компиляции шаблона',
];
```
