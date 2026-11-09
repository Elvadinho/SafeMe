# SafeMe API (MVP)

Base URL: `http://127.0.0.1:8000/api/v1`. All answers are JSON. Send `Accept: application/json`.
Logged-in calls send the header `Authorization: Bearer <token>`.

Phone numbers can be written any way (`6 90 00 00 00`, `+237690000000`, ...): they are
always saved as `+237XXXXXXXXX`. Phone numbers are **never** returned by public endpoints.

## How the code is organised

```
Request (validation)  →  Controller (HTTP only)  →  Service (business logic)  →  Model (database)
```

| Folder | Role |
|---|---|
| `app/` | Core: users, roles, login, shared helpers (`Support/Phone`, `Support/Text`, `Support/Geo`, `Services/FileService`) |
| `Modules/Children/` | Missing children: reports, alerts, sightings, moderation |
| `Modules/Documents/` | Lost & found documents with automatic matching |
| `config/safeme.php` | Every setting (limits, search zone, upload sizes) |

## Auth

| Method | URL | Who | Body |
|---|---|---|---|
| POST | `/auth/register` | anyone | name, email, phone?, password, password_confirmation |
| POST | `/auth/login` | anyone | email, password → `{ user, token }` |
| GET | `/auth/me` | logged in | |
| POST | `/auth/logout` | logged in | |

## Missing children

**Reporter (parent / guardian): no account**

| Method | URL | Body |
|---|---|---|
| POST | `/reports` | *multipart*: child_name, child_age, child_gender?, child_photo, description?, last_seen_at, last_seen_lat, last_seen_lng, last_seen_place?, reporter_name, reporter_phone, relationship (`parent`/`guardian`), declaration=1, documents[0][type], documents[0][file] … → `{ public_id, status, missing_documents }` |
| POST | `/reports/{public_id}/follow` | reporter_phone → status, documents, all sightings, rejection reason |
| POST | `/reports/{public_id}/documents` | *multipart*: reporter_phone, documents[…] (only while pending) |

Document types: `birth_certificate`, `reporter_id`, `guardianship_order`, `photo_with_child`.
A parent needs `birth_certificate` + `reporter_id`. A guardian needs `guardianship_order` + `reporter_id`.

**Public**

| Method | URL | Notes |
|---|---|---|
| GET | `/alerts?lat=&lng=&radius_km=` | approved alerts + `search_zone` (centre + radius growing 2 km/hour) + `distance_km` |
| GET | `/alerts/{public_id}` | one alert + search zone + confirmed sightings |
| GET | `/alerts/{public_id}/photo` | the child's photo (only when approved; admins always) |
| POST | `/alerts/{public_id}/sightings` | *multipart*: lat, lng, seen_at, note?, photo?, witness_phone? |

**Admin / police** (role `admin` or `super_admin`)

| Method | URL |
|---|---|
| GET | `/admin/reports?status=pending` (also `approved`, `rejected`, `found`, `closed`) |
| GET | `/admin/reports/{public_id}` (reporter phone, documents, all sightings) |
| GET | `/admin/reports/{public_id}/documents/{id}` (opens the proof file) |
| POST | `/admin/reports/{public_id}/approve` |
| POST | `/admin/reports/{public_id}/reject` (reason) |
| POST | `/admin/reports/{public_id}/found` |
| POST | `/admin/reports/{public_id}/close` |
| POST | `/admin/sightings/{id}/confirm` · `/dismiss` |
| GET | `/admin/sightings/{id}/photo` |

Allowed status changes: `pending → approved | rejected`, `approved → found | closed`. Anything else answers **409**.

## Lost & found documents

| Method | URL | Who | Body |
|---|---|---|---|
| GET | `/found-documents?name=&doc_type=` | anyone | name required → masked results (`A****** T******`) |
| POST | `/found-documents` | anyone | doc_type, owner_name, number_last_digits?, found_city, found_place?, found_at, finder_name, finder_phone |
| POST | `/found-documents/{public_id}/follow` | the finder | phone → matches, owner contact once confirmed |
| POST | `/lost-documents` | anyone | doc_type, owner_name, number_last_digits?, lost_city?, owner_phone → `matches_found` |
| POST | `/lost-documents/{public_id}/follow` | the owner | phone → matches, finder contact once confirmed |
| GET | `/admin/document-matches?status=pending` | admin | both sides with phones |
| POST | `/admin/document-matches/{id}/confirm` · `/reject` | admin | |
| POST | `/admin/found-documents/{public_id}/returned` | admin | |

Doc types: `national_id`, `passport`, `birth_certificate`, `driving_licence`, `diploma`, `student_card`, `other`.
Matching = same type + same name (word order, accents and capitals ignored) + same last digits when both sides gave them.

## Super admin

| Method | URL | Body |
|---|---|---|
| GET | `/admin/users?role=&search=` | |
| POST | `/admin/users/admins` | name, email, phone?, password (12+) |
| PATCH | `/admin/users/{id}/role` | role = `admin` or `citizen` |

## Status codes

`201` created · `401` not logged in · `403` wrong role · `404` not found (or wrong phone) ·
`409` action not allowed in this status · `422` validation errors · `429` too many requests.

## Run it

```bash
php artisan migrate
php artisan db:seed      # creates the super admin from SUPER_ADMIN_* in .env
php artisan serve
php artisan test         # 36 tests
```
