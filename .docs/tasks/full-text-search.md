## 🔍 Task: Full-Text Search Implementation (Meilisearch + Laravel Scout)

### 🎯 Goal

Implement full-text search in the application using **Meilisearch** + **Laravel Scout**, with:

- `docker-compose` setup for Meilisearch
- Scout integration in Laravel
- Making the following models searchable:
  - `Contractor`
  - `Product`
  - `Invoice`
  - `User`
  - `Contact`
- A dedicated `search(string $query)` method in each controller
- REST endpoint: `GET /api/{model}/search?q=...`

---

### ✅ Step-by-Step

#### 1. 🐳 Add Meilisearch to `docker-compose`

```yaml
# docker-compose.override.yml or docker-compose.yml
services:
  meilisearch:
    image: getmeili/meilisearch:latest
    ports:
      - "7700:7700"
    environment:
      MEILI_NO_ANALYTICS: "true"
      MEILI_MASTER_KEY: "masterKey"
