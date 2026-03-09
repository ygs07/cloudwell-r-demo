# Deployment Guide

## Overview

This document describes how the Referral API is deployed, how background
jobs are processed, and the limitations of the current deployment setup.

------------------------------------------------------------------------

# Hosting Environment

The application is deployed on **Railway**.

Railway is used for: - Application hosting - Database hosting -
Environment variable management - Deployment from the Git repository

The deployment runs a standard **Laravel production environment**.

------------------------------------------------------------------------

# Application Stack

| Component | Technology |
|---|---|
| Backend | PHP (Laravel) |
| Database | PostgreSQL (Railway managed) |
| Queue Driver | Database |
| Hosting Platform | Railway |



------------------------------------------------------------------------

# Deployment Process

Deployment is handled automatically through **Railway's Git
integration**.

### Steps

1.  Code is pushed to the repository.
2.  Railway detects the change.
3.  A new build is triggered.
4.  Dependencies are installed.
5.  The application is deployed.

Typical production commands executed include:

    composer install --no-dev --optimize-autoloader
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache

------------------------------------------------------------------------

# Queue Processing

The application uses **Laravel Queues** for background jobs such as:

-   Referral triaging
-   Asynchronous processing
-   Future background tasks

Current configuration:

    QUEUE_CONNECTION=database

This means jobs are stored in the **jobs database table**.

------------------------------------------------------------------------

# Running Queue Workers

Queue workers must be running for jobs to be processed.

Worker command:

    php artisan queue:work

On Railway, the queue worker runs as a **separate service or process**.

Example worker command:

    php artisan queue:work --tries=3 --timeout=90

### Explanation

  Option         Description
  -------------- --------------------------------------------
  --tries=3      Retry failed jobs up to 3 times
  --timeout=90   Kill job if it runs longer than 90 seconds

------------------------------------------------------------------------

# Job Failure Handling

If a job fails after the configured number of attempts:

-   It is stored in the **failed_jobs table**
-   The job's `failed()` method logs the failure

Example logging: - `triage_failed` audit log entry - Error message
stored in metadata

------------------------------------------------------------------------

# Current Limitations

## 1. Database Queue Driver

The application currently uses the **database queue driver**, which has
several limitations.

### Limitations

-   Slower than dedicated queue systems
-   Database locking can occur under high load
-   Not ideal for large-scale background processing
-   Workers must continuously poll the database

Recommended upgrade for production scaling:

    QUEUE_CONNECTION=redis

Benefits:

-   Faster job processing
-   Better scalability
-   More efficient worker management

------------------------------------------------------------------------

## 2. Worker Persistence

Queue workers on Railway may restart during:

-   Deployments
-   Platform restarts
-   Worker crashes

If a worker stops running, **queued jobs will remain unprocessed** until
a worker starts again.

More robust setups may use:

-   Supervisor
-   Dedicated background workers
-   Laravel Horizon (when Redis is used)

------------------------------------------------------------------------

## 3. No Queue Monitoring

Currently there is **no real-time queue monitoring**.

Possible improvements:

-   Laravel Horizon
-   Queue dashboards
-   Alerts for failed jobs

------------------------------------------------------------------------

# Future Improvements

### Queue System

Move from:

    database

to:

    redis

### Queue Monitoring

Introduce:

- Laravel Horizon
- Worker health monitoring

### Infrastructure

Potential improvements:

- Dedicated worker containers
- Auto-scaling workers
- Better observability

---

# Summary

| Area | Current Setup | Recommended Improvement |
|---|---|---|
| Queue Driver | Database | Redis |
| Worker Management | Manual worker process | Supervisor / Horizon |
| Queue Monitoring | None | Horizon dashboard |
| Scalability | Limited | High |
