# Standardized Filtering & Sorting in API Endpoints

## Overview

All index methods in CRUD controllers support **standardized filtering and sorting** using [Spatie Query Builder](https://spatie.be/docs/laravel-query-builder/v5/introduction).  
This enables flexible, consistent, and powerful querying for all API consumers.

### Key Features

- **Field-based filtering** with custom operators per column
- **Combo search** across multiple fields with a single parameter
- **Consistent query parameter structure** for all endpoints
- **Extensible**: Easily add new filters or customize per model

---

## Filtering

### 1. Combo Search (`search` param)

Use the `search` query parameter to search across multiple fields at once.  
This is powered by our custom `ComboSearchFilter`.

**Example:**
```
GET /api/users?search=John
```
_Searches all configured fields (e.g., `name`, `email`, etc.) for the value `John`._

### 2. Column Filtering (`filter[column]`)

Filter by any individual column using the `filter[column]=value` syntax.  
For advanced filtering, use operators: `filter[column][operator]=value`.

This is powered by our custom `AdvancedFilter`, supporting a wide range of operators.

**Examples:**

- **Simple equality:**
  ```
  GET /api/users?filter[status]=active
  ```
- **With operator:**
  ```
  GET /api/users?filter[created_at][gte]=2024-01-01
  GET /api/users?filter[role][in]=admin,editor
  GET /api/users?filter[score][between]=10,20
  GET /api/users?filter[email][like]=@gmail.com
  ```

#### Supported Operators

| Operator      | Example Param                      | Description                        |
|---------------|------------------------------------|------------------------------------|
| (none)        | `filter[age]=30`                   | Equals                             |
| eq            | `filter[age][eq]=30`               | Equals                             |
| ne / neq      | `filter[age][ne]=30`               | Not equals                         |
| gt            | `filter[age][gt]=18`               | Greater than                       |
| gte           | `filter[age][gte]=18`              | Greater than or equal              |
| lt            | `filter[age][lt]=65`               | Less than                          |
| lte           | `filter[age][lte]=65`              | Less than or equal                 |
| in            | `filter[role][in]=admin,editor`    | In list                            |
| nin / notin   | `filter[role][nin]=guest,blocked`  | Not in list                        |
| between       | `filter[score][between]=10,20`     | Between two values                 |
| like          | `filter[email][like]=@gmail.com`   | Contains (SQL LIKE)                |
| nlike/notlike | `filter[email][nlike]=@spam.com`   | Does not contain                   |
| startswith    | `filter[name][startswith]=A`       | Starts with                        |
| endswith      | `filter[name][endswith]=son`       | Ends with                          |
| null          | `filter[deleted_at][null]`         | Is null                            |
| notnull       | `filter[deleted_at][notnull]`      | Is not null                        |
| nullish       | `filter[foo][nullish]`             | Is null or empty string            |
| regex         | `filter[username][regex]=^admin`   | Regex match                        |

---

## Sorting

Sort results by any allowed field using the `sort` parameter.

**Examples:**
```
GET /api/users?sort=created_at
GET /api/users?sort=-name
```
- Prefix with `-` for descending order.

---

## Implementation Notes

- **ComboSearchFilter**: Used for the `search` param, applies an `OR`-based search across multiple fields.
- **AdvancedFilter**: Used for all `filter[column]` params, supports both simple and advanced operators.
- **Extensibility**: To add new filterable fields or operators, update the relevant controller or filter class.

---

## Example Request

```
GET /api/products?search=phone&filter[price][gte]=100&filter[category][in]=electronics,appliances&sort=-price
```

---

## References

- [Spatie Query Builder Docs](https://spatie.be/docs/laravel-query-builder/v5/introduction)
- See `app/Domain/Common/Filters/AdvancedFilter.php` and `ComboSearchFilter.php` for implementation details. 
