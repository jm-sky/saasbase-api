# 💰 Kalkulacja kosztów infrastruktury aplikacji

---

## 🧪 Wersja MVP (testowa)

| Komponent          | Koszt (zł / mies.) | Uwagi |
|--------------------|--------------------|-------|
| Hosting (Cyber_UP) | 35,83              | 430 zł / rok |
| Domena (.pl)       | 5                  | ok. 60 zł / rok |
| Baza danych        | w cenie hostingu   | MySQL |
| Redis              | 0                  | Darmowy zewnętrzny |
| WebSockets         | 0–20               | Fly.io, darmowy lub tani |
| Storage (Storj)    | 0                  | 150 GB za darmo |
| SMTP               | 0                  | W cenie hostingu |
| Backup             | 0                  | – |
| Monitoring / WAF   | 0                  | Ręczne / minimalne |
| Azure AI           | –                  | Nie używane w MVP |
| **Suma**           | **40–60 zł**       | w zależności od WebSocketów |

---

## 🚀 Wersja Produkcyjna

| Komponent              | Koszt (zł / mies.) | Uwagi |
|------------------------|--------------------|-------|
| VPS (OVH, SSD 1)       | 21                 | 25,99 zł brutto / mies. |
| Domena (.pl)           | 5                  | ok. 60 zł / rok |
| Baza danych            | w cenie VPS        | PostgreSQL / MySQL |
| Redis                  | 0                  | Lokalnie na VPS |
| WebSockets             | 0                  | Hostowane na VPS |
| Storage (Storj)        | 0                  | Darmowe 150 GB |
| SMTP (EmailLabs)       | 49                 | Plan podstawowy |
| Backup                 | 1                  | np. automatyczny snapshot VPS |
| Monitoring / WAF       | 0                  | Podstawowy firewall VPS |
| Azure AI (Document Intelligence) | 40.60       | 1000 x Invoice, 100 x Read |
| **Suma**               | **116.60 zł**      | Całość miesięcznie netto |

---

## 👤 Koszt dodatkowego użytkownika

| Komponent       | Koszt (zł / mies.) | Uwagi |
|-----------------|--------------------|-------|
| Storage (Storj) | 20                 | Przy przekroczeniu 150 GB |
| **Suma**        | **20 zł**          | Per użytkownik (jeśli dedykowany storage) |

---

## 🔐 Monitoring i bezpieczeństwo

| Kategoria           | Narzędzie       | Status w MVP | Status w Produkcji | Koszt (zł / mies.) | Uwagi |
|---------------------|------------------|--------------|---------------------|----------------------|-------|
| Błędy aplikacji     | **Sentry**       | ✅ Tak        | ✅ Tak              | 0 zł (do limitu)     | 5000 eventów / mies. za darmo |
| Monitoring stanu    | UptimeRobot      | Opcjonalnie  | ✅ Tak              | 0 zł (plan darmowy)  | 1-min. interwał w płatnym |
| WAF / Firewall      | OVH VPS          | Brak         | Podstawowy          | 0 zł                 | Firewall systemowy (iptables) |
| Zewnętrzny WAF/CDN  | Cloudflare       | Opcjonalnie  | Opcjonalnie         | 0 zł                 | Darmowy plan z ochroną DDoS |
| Alerty i statusy    | Email / Discord  | Ręczne       | Ręczne lub integracje | 0 zł              | Możliwe przez webhook/Sentry |

---

## 🧾 Koszt Azure Document Intelligence

| Model                 | Ilość stron | Stawka (USD)         | Szacunkowy koszt (USD) | Koszt (PLN przy ~4 zł/USD) |
|-----------------------|-------------|-----------------------|--------------------------|-----------------------------|
| **Prebuilt Invoice**  | 1000        | $10 / 1000 stron      | $10                     | **40 zł**                   |
| **Read (OCR)**        | 100         | $1.50 / 1000 stron    | $0.15                   | **~0.60 zł**                |
| **RAZEM**             | —           | —                     | **$10.15**              | **~40.60 zł**               |

---

## 📌 Podsumowanie

- MVP: **40–60 zł / mies.**
- Produkcja (bez Azure): **76 zł / mies.** (~912 zł / rok)
- Produkcja + Azure AI: **116.60 zł / mies.** (~1399 zł / rok)
- Dodatkowy użytkownik (z własnym storage): **+20 zł / mies.** (~240 zł / rok)

---

### 💼 Rentowność przy planie **Basic (50 zł/mc)**

| Liczba użytkowników | Przychód / mc | Przychód / rok | Koszty / rok | Zysk / Strata / rok |
|---------------------|----------------|----------------|---------------|----------------------|
| 1                   | 50 zł          | 600 zł         | 1399 zł       | **–799 zł**          |
| 2                   | 100 zł         | 1200 zł        | 1399 zł       | **–199 zł**          |
| 3                   | 150 zł         | 1800 zł        | 1399 zł       | **+401 zł**          |
| 4                   | 200 zł         | 2400 zł        | 1399 zł       | **+1001 zł**         |
| 5                   | 250 zł         | 3000 zł        | 1399 zł       | **+1601 zł**         |

---

### 💼 Rentowność przy planie **Pro (100 zł/mc)**

| Liczba użytkowników | Przychód / mc | Przychód / rok | Koszty / rok | Zysk / Strata / rok |
|---------------------|----------------|----------------|---------------|----------------------|
| 1                   | 100 zł         | 1200 zł        | 1399 zł       | **–199 zł**          |
| 2                   | 200 zł         | 2400 zł        | 1399 zł       | **+1001 zł**         |
| 3                   | 300 zł         | 3600 zł        | 1399 zł       | **+2201 zł**         |
| 4                   | 400 zł         | 4800 zł        | 1399 zł       | **+3401 zł**         |
| 5                   | 500 zł         | 6000 zł        | 1399 zł       | **+4601 zł**         |

> ✅ Rentowność roczna osiągana przy:
> - **3+ userach na planie Basic**
> - **2+ userach na planie Pro**

