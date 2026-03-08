# Cloud Well Referrals Demo

## Project Overview
Cloud Well Referrals Demo is a Laravel-based API application designed to manage medical or clinical referrals. It provides endpoints to create, triage, and manage the lifecycle of patient referrals. Key features include an automated async triage system (which evaluates priority and context to transition statuses) and granular status tracking (e.g., Pending, Triaged, Urgent, Cancelled) along with comprehensive audit logging for each referral action.

## Prerequisites
Before you begin, ensure you have the following installed on your local machine:
- **PHP >= 8.4**
- **Composer**
- DB Server: **SQLite** (recommended for quick local dev) or MySQL/PostgreSQL
- **[Laravel Herd](https://herd.laravel.com/)** or Valet (optional, for macOS users)

## Local Setup Instructions (Without Docker)

1. **Clone the repository (if applicable):**
   ```bash
   git clone <repository-url>
   cd cloud-well-referrals-demo
   ```

2. **Install PHP Dependencies:**
   ```bash
   composer install
   ```

3. **Configure Environment variables:**
   Copy the example environment file and create your local `.env`:
   ```bash
   cp .env.example .env
   ```
   *Note: Ensure your `DB_CONNECTION` in `.env` is set correctly. For SQLite, setting `DB_CONNECTION=sqlite` and removing DB_HOST etc. is enough in modern Laravel.*

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Create the SQLite database (If using SQLite):**
   ```bash
   touch database/database.sqlite
   ```

## How to Run Migrations
To set up the database tables (Users, Patients, Referrals, Audit Logs, etc.), run the following Artisan command:
```bash
php artisan migrate --seed
```
*Note: The `--seed` flag will populate the database with initial factory data if your seeders are configured.*

## How to Run the App
To start the local development server, run:
```bash
php artisan serve
```
The application will be accessible at `http://localhost:8000`.

*(Alternatively, if you are using Laravel Herd, the app is automatically served at `http://cloud-well-referrals-demo.test` or whichever local domain you configured).*

## How to Run the Queue Worker
The application uses async jobs to triage incoming referrals immediately after creation. To process these jobs, you need to run the queue worker:
```bash
php artisan queue:work
```
*Make sure your `QUEUE_CONNECTION` in the `.env` file is set to `database` (or Redis, etc.) rather than `sync` if you want to observe true asynchronous behavior.*

## How to Run Tests
The project includes a robust test suite, particularly Feature tests validating Referral creation, async triage, cancellation rules, and authentication.

To run the entire test suite:
```bash
php artisan test
```

To run specifically the Referral tests:
```bash
php artisan test --filter ReferralTest
```

## Sample API Requests

### 1. Create a New Referral
**Endpoint:** `POST /api/v1/referrals`
**Authentication:** Required (Sanctum Bearer Token)
**Headers:** 
- `Accept: application/json`
- `Idempotency-Key: <unique-string>` *(Required for creating a new referral)*

**Patient Object Reference (Valid Values for Blood Group & Genotype):**
- **Blood Group:** 1 (A+), 2 (A-), 3 (B+), 4 (B-), 5 (AB+), 6 (AB-), 7 (O+), 8 (O-)
- **Genotype:** 1 (AA), 2 (AS), 3 (SS), 4 (AC), 5 (SC), 6 (CC)

**Required Fields:**
- `patient.patient_number`
- `referral_reason`
- `priority`
- `referring_party.system_id`
- `referring_party.name`
- `referring_party.type`

**Request Body:**
```json
{
  "patient": {
    "patient_number": "PT-123456",
    "date_of_birth": "1990-01-01",
    "weight": "75kg",
    "blood_group": 1,
    "genotype": 1
  },
  "referral_reason": "Patient complains of chronic chest pain.",
  "priority": 1,
  "referring_party": {
    "system_id": "SYS-999",
    "name": "General Hospital",
    "type": "hospital"
  },
  "optional_notes": "Please expedite if possible."
}
```

```

**Sample Success Response (201 Created):**
```json
{
  "message": "Referral created successfully",
  "data": {
    "id": 1,
    "patient_id": 1,
    "referral_reason": "Patient complains of chronic chest pain.",
    "priority": "ROUTINE",
    "referring_party_id": 1,
    "optional_notes": "Please expedite if possible.",
    "created_at": "2026-03-08T12:00:00.000000Z",
    "updated_at": "2026-03-08T12:00:00.000000Z",
    "status": "RECEIVED",
    "patient": {
      "id": 1,
      "patient_number": "PT-123456",
      "date_of_birth": "1990-01-01",
      "weight": "75kg",
      "blood_group": "A+",
      "genotype": "AA"
    },
    "referring_party": {
      "id": 1,
      "system_id": "SYS-999",
      "name": "General Hospital",
      "type": "hospital"
    }
  }
}
```

### 2. Cancel a Triaged Referral
**Endpoint:** `PATCH /api/v1/referrals/{referral_id}/cancel`
**Authentication:** Required (Sanctum Bearer Token)
**Headers:** `Accept: application/json`

*Note: A referral can ONLY be cancelled if it has been marked as `triaged` (status: TRIAGING). Non-triaged referrals will return a `400 Bad Request` with an appropriate message.*

**Required Fields:**
- `cancellation_reason` (string, required)

**Request Body:**
```json
{
  "cancellation_reason": "Patient no longer requires this referral."
}
```

**Sample Failure Response (Non-Triaged Referral - 400 Bad Request):**
```json
{
    "message": "Referral cannot be cancelled"
}
```

```

**Sample Success Response (200 OK):**
```json
{
  "message": "Referral cancelled successfully",
  "data": {
    "id": 1,
    "patient_id": 1,
    "referral_reason": "Patient complains of chronic chest pain.",
    "priority": "ROUTINE",
    "referring_party_id": 1,
    "optional_notes": "Please expedite if possible.",
    "created_at": "2026-03-08T12:00:00.000000Z",
    "updated_at": "2026-03-08T12:10:00.000000Z",
    "status": "CANCELLED"
  }
}
```

### 3. List Referrals
**Endpoint:** `GET /api/v1/referrals`
**Headers:** `Accept: application/json`
**Parameters (Optional Filtering):** 
- `status`: Status Name (e.g., RECEIVED, TRIAGING)
- `priority`: Priority Name (e.g., ROUTINE, URGENT)
- `patient_id`: Integer
- `referring_party_id`: Integer



```

**Sample Success Response (200 OK - Paginated):**
```json
{
  "data": [
    {
      "id": 1,
      "patient_id": 1,
      "referral_reason": "Patient complains of chronic chest pain.",
      "priority": "ROUTINE",
      "referring_party_id": 1,
      "optional_notes": "Please expedite if possible.",
      "created_at": "2026-03-08T12:00:00.000000Z",
      "updated_at": "2026-03-08T12:00:00.000000Z",
      "status": "RECEIVED"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/referrals?page=1",
    "last": "http://localhost:8000/api/v1/referrals?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://localhost:8000/api/v1/referrals?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "path": "http://localhost:8000/api/v1/referrals",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```
