# Automated Mail Engine Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add automated email for account verification, new internship applications, and application status updates.

**Architecture:** Use Laravel Notifications for each email type and a small `AutomatedMailService` for application-related sends. Keep mail failures non-blocking by catching notification exceptions and logging them.

**Tech Stack:** Laravel, Notifications, Blade email views, PHPUnit feature tests.

---

### Task 1: Verification Email Notification

**Files:**
- Modify: `app/Models/User.php`
- Create: `app/Notifications/VerifyEmailNotification.php`
- Create: `resources/views/emails/verify-email.blade.php`
- Test: `tests/Feature/Auth/RegistrationTest.php`

- [ ] Add a failing registration test that expects `VerifyEmailNotification`.
- [ ] Run the targeted registration test and verify it fails because the notification is missing or not sent.
- [ ] Implement `MustVerifyEmail`, `sendEmailVerificationNotification()`, the notification class, and the Blade view.
- [ ] Re-run the targeted registration test and verify it passes.

### Task 2: Application Mail Notifications

**Files:**
- Create: `app/Services/AutomatedMailService.php`
- Create: `app/Notifications/ApplicationSubmittedToCompanyNotification.php`
- Create: `app/Notifications/ApplicationStatusUpdatedNotification.php`
- Create: `resources/views/emails/application-submitted-company.blade.php`
- Create: `resources/views/emails/application-status-updated.blade.php`
- Modify: `app/Http/Controllers/ApplicationController.php`
- Modify: `app/Http/Controllers/CompanyApplicantController.php`
- Test: `tests/Feature/Sikara/AutomatedMailEngineTest.php`

- [ ] Add failing tests for company email on application submit and student email on status update.
- [ ] Run the targeted automated mail test and verify it fails because notifications are missing.
- [ ] Implement notification classes and templates.
- [ ] Implement `AutomatedMailService` with non-blocking send behavior.
- [ ] Inject and call the service from application submission and status update controllers.
- [ ] Re-run targeted tests and verify they pass.

### Task 3: Failure Safety

**Files:**
- Modify: `tests/Feature/Sikara/AutomatedMailEngineTest.php`
- Modify: `app/Services/AutomatedMailService.php`

- [ ] Add a failing test that binds a throwing mail service and verifies the application flow still succeeds.
- [ ] Run the targeted failure-safety test and verify it fails before handling is in place.
- [ ] Add exception handling and warning logs in `AutomatedMailService`.
- [ ] Re-run the targeted tests and full relevant feature test set.
