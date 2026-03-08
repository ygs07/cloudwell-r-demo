# AI Usage

## AI Tools Used
- **Antigravity (Gemini)**: Used as a pair programmer for codebase generation, refactoring, and automated test implementation.

## Where AI Was Helpful
- **Architectural Scaffolding**: Efficiently generated models, migrations, and controllers for the core referral system.
- **Testing Suite**: Created a robust set of feature tests covering referral creation, async triage, and complex cancellation rules.
- **Documentation**: Assisted in drafting the `README.md` and providing detailed sample API responses (Success/Failure) for main endpoints.
- **Rapid Prototyping**: Quickly implemented complex logic such as the `EnsureIdempotency` middleware and polymorphic audit logging.

## Where AI Got It Wrong or Incomplete
- **Enum Handling**: Initially used raw integer values for patient health data (`blood_group`, `genotype`) instead of utilizing Laravel 11's backed enums and casting.
- **Granular Logic**: Missed some specific clinical validation rules until prompted to refine them with enums.

## Manual Verification & Changes
- **Type Safety**: Manually implemented Laravel's `casts()` and `tryFrom()` logic for `BloodGroup` and `Genotype` enums in the `Patient` model and `ReferralController`.
- **API Response Formatting**: Updated `PatientResource` and `ReferralResource` to ensure enums are returned via their `label()` methods rather than raw values.
- **Higher-Level Design**: Manually authored `ARCHITECTURE.md` to document the specific rationale behind polymorphic models and async triage design.
- **Sanctum Configuration**: Refined the `AppServiceProvider` and authentication routes to ensure seamless token issuance for different user types.
