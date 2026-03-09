# Architecture & Design Document

This document outlines the architectural decisions, database schema choices, and technical approaches taken while building the Cloud Well Referrals Demo API.

## 1. Key Design Decisions

- **Polymorphic User Model**: The `User` model was made polymorphic to seamlessly accommodate potential standalone logins for different actor types, specifically `Staff` (e.g., doctors, admins) and `Patient`. This allows future extensibility if patients need to log in to view their own referrals.
- **Dedicated Patient Model**: Introduced a `Patient` model to centralize all patient-related data. A unique `patient_number` serves as the primary identifier across the system. This model also captures essential bio-data (weight, blood group, genotype) to aid in clinical triage.
- **Referring Parties Tracking**: Created a `referring_parties` table to store the origin of a referral (e.g., clinics, external hospitals) alongside an external `system_id`. This allows mapping referrals to both source systems and patients while serving as a powerful filter dimension.
- **Polymorphic Audit Logging**: Rather than creating action-specific log tables, a polymorphic `AuditLog` model was created. This allows any entity in the system (like changing a `Referral` status to triaged or cancelled) to easily record historical state changes in a single, unified structure, and it leaves room for logging other models. 
- **Separation of Concerns**: The application strictly separates routing, validation, and presentation:
  - **Controllers**: Thin controllers that primarily delegate logic.
  - **Form Requests**: Dedicated request classes handle complex payload validation.
  - **API Resources**: Resource classes transform database models into standardized JSON structures, formatting relationships cleanly.
  - **Route Modularity**: API routes are broken down into granular versioned files (e.g., `v1/referrals.php`) and required in the base application route file to prevent a monolithic routes file.

## 2. Schema Choices

- **Dedicated Tables per Entity**: Every distinct domain concept (Patients, Referrals, Referring Parties, Audit Logs, Staff) has its own dedicated database table ensuring normalization.
- **Foreign Key Constraints**: Strict relational integrity is enforced at the database level using foreign key constraints (`constrained()->cascadeOnDelete()`). This prevents orphaned records if a parent entity (like a Patient or Referring Party) is removed.

## 3. Queue / Job Design

- **Asynchronous Triage**: To ensure the API remains highly responsive, the triage logic (evaluating priority scores and keyword triggers in the referral reason) has been abstracted into an asynchronous `TriageReferral` job.
- **Event-Driven Dispatch**: The job is automatically dispatched via model lifecycle hooks (or via Controller logic) immediately after a referral is successfully created. This prevents the initial `POST` request from hanging while complex medical business rules are evaluated in the background.

## 4. Authentication Approach

- **Token-Based Auth**: The API leverages **Laravel Sanctum** to handle authentication.
- **Bare minimum token Abilities**: The tokens issued by sanctum grants only the abilities needed to perform actions
- **Bearer Tokens**: Users (Staff/Patients) are issued lightweight API tokens upon login. These Bearer tokens are required to access protected routes (like creating or cancelling referrals), ensuring a stateless, scalable authorization mechanism well-suited for modern SPAs or mobile clients.

## 5. Tradeoffs & Future Improvements

**Tradeoffs Made:**
- *Basic Triage Rules:* The current asynchronous triage logic is hardcoded into the `TriageReferral` job (e.g., specific priority integers or checking for the word "emergency"). While fast to implement, it lacks dynamic configurability.
- *Default Queue Driver:* Depending on local setups, the queue driver might just be running synchronously or via the DB. For large-scale, a robust in-memory datastore is preferred.
- *Permission Granularity:* Currently, Sanctum verifies generic authentication, but granular Role-Based Access Control (e.g., distinguishing an Admin from a regular Nurse cancelling a referral) is not deeply modeled yet.
- *Lingering Referrals:* Currenlty simply rejecting a referral leads to the end of the lifecycle of a referral, but an admin or someone should be able to send back a referral to the originator to edit, then send it back 
- *Assumed Structure and Existence of other systems:* Currently unaware of the structure of other internal systems, and if, for example, they share common information with referral system information like Patient details, I'd rather leverage that than just simply getting the Patient's details from the request
- *Patient and Referring Party:* Referring Party and Patient details should always be gotten from the authenticated user.

**What I would improve with more time:**
- **Rules Engine for Triage**: Implement a dedicated Rules Engine or integrate a package so that clinical triage rules can be configured via a database UI rather than changing code.
- **Redis Queue implementation**: strictly enforce Redis for the queue worker to handle high volumes of concurrent referrals efficiently.
- **API Documentation Generation**: Integrate a package like Scribe or L5-Swagger to auto-generate OpenAPI/Swagger documentation directly from the codebase.
- **Comprehensive RBAC**: Introduce Spatie permissions or Laravel Bouncer to map distinct access levels for different polymorphic user types (e.g., only authorized staff can cancel a triaged referral).
- **Webhooks**: Provide an outbound webhook infrastructure to notify external systems (Referring Parties) when a referral status changes.
- **Broader Lifecycle for Referrals**: Rejecting a referral should lead to a sending back of the referral to make edits and then resubmit for processing.
- **Provide user access for Patients**: Patients should be able to login and cancel their own referrals manually. And also to edit their bio information like weight, sex, genotype etc