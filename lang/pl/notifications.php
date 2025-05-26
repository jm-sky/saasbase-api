<?php

return [
    'tenant_invitation' => [
        'subject' => 'Zostałeś zaproszony do dołączenia do organizacji',
        'greeting' => 'Witaj!',
        'intro' => 'Zostałeś zaproszony do dołączenia do organizacji w SaaSBase.',
        'tenant_info' => 'Organizacja: :name',
        'role_info' => 'Rola: :role',
        'accept_button' => 'Akceptuj zaproszenie',
        'ignore_info' => 'Jeśli nie spodziewałeś się tego zaproszenia, możesz zignorować ten e-mail.',
        'accepted' => [
            'title' => 'Twoje zaproszenie zostało zaakceptowane!',
            'message' => ':name dołączył do :tenant jako :role!',
        ],
        'rejected' => [
            'title' => 'Twoje zaproszenie zostało odrzucone!',
            'message' => ':name odrzucił Twoje zaproszenie do dołączenia do :tenant jako :role!',
        ],
    ],
    'application_invitation' => [
        'subject' => 'Zostałeś zaproszony do dołączenia do SaaSBase',
        'greeting' => 'Witaj!',
        'intro' => 'Zostałeś zaproszony do dołączenia do SaaSBase.',
        'accept_button' => 'Akceptuj zaproszenie',
        'ignore_info' => 'Jeśli nie spodziewałeś się tego zaproszenia, możesz zignorować ten e-mail.',
    ],
    'password' => [
        'changed' => [
            'subject' => 'Hasło zostało zmienione w :app!',
            'greeting' => 'Cześć :name,',
            'message' => 'Twoje hasło zostało zmienione w :app.',
            'warning' => 'Jeśli to nie Ty zmieniłeś hasło, skontaktuj się z pomocą techniczną.',
            'help' => 'Jeśli masz jakiekolwiek pytania, śmiało pytaj.',
            'title' => 'Hasło zostało zmienione!',
        ],
        'reset' => [
            'subject' => 'Powiadomienie o resetowaniu hasła',
            'message' => 'Otrzymujesz ten e-mail, ponieważ otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta.',
            'button' => 'Resetuj hasło',
            'expiry' => 'Ten link do resetowania hasła wygaśnie za :count minut.',
            'ignore' => 'Jeśli nie prosiłeś o resetowanie hasła, nie musisz nic robić.',
        ],
    ],
    'email_verification' => [
        'subject' => 'Weryfikacja adresu e-mail',
    ],
    'welcome' => [
        'subject' => 'Witaj w :app!',
        'greeting' => 'Cześć :name,',
        'message' => 'Dziękujemy za rejestrację w :app.',
        'excitement' => 'Cieszymy się, że jesteś z nami i nie możemy się doczekać, co zbudujesz.',
        'dashboard_button' => 'Przejdź do panelu',
        'help' => 'Jeśli masz jakiekolwiek pytania, śmiało pytaj.',
        'title' => 'Witaj w :app!',
        'notification_message' => 'Witaj :name, cieszymy się, że jesteś z nami!',
    ],
];
