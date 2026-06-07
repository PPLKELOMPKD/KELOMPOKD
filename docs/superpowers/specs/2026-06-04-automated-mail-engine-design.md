# Automated Mail Engine Design

## Overview

SIKARA needs an automated mail engine so the backend can send consistent email for account verification, application notifications, and application status updates. The first implementation scope covers three events:

- Account email verification after registration.
- New application notification to the company that owns the internship.
- Application status update notification to the student applicant.

Password reset already exists and stays as part of the same mail ecosystem, but it does not need to be rebuilt.

## Goals

- Use the existing Laravel mail and notification stack, including the configured Resend mailer.
- Keep core user flows working even when email delivery fails.
- Reuse branded Blade email templates under `resources/views/emails`.
- Make future automated email events easy to add without scattering mail error handling across controllers.

## Non-Goals

- Build admin company verification approval or rejection flows in this scope.
- Add a mail delivery dashboard.
- Store every email delivery attempt in a new database table.
- Require queue workers before the feature can work locally.

## Architecture

The implementation will use Laravel Notifications as the primary delivery mechanism because the app already uses `App\\Notifications\\ResetPasswordNotification` for password recovery. A small `AutomatedMailService` will act as the mail engine facade for application-related mail.

Main units:

- `App\\Services\\AutomatedMailService`: validates recipient email, sends notifications, catches provider/template exceptions, and logs failures.
- `App\\Notifications\\VerifyEmailNotification`: branded account verification email using Laravel signed verification URLs.
- `App\\Notifications\\ApplicationSubmittedToCompanyNotification`: email sent to a company when a student applies to one of its internships.
- `App\\Notifications\\ApplicationStatusUpdatedNotification`: email sent to a student when their application status changes.
- Blade templates in `resources/views/emails`: one template per new notification type.

`App\\Models\\User` will implement `Illuminate\\Contracts\\Auth\\MustVerifyEmail` so Laravel's existing registration event can send verification mail. The model will override `sendEmailVerificationNotification()` to use the branded notification.

## Event Flow

### Account Verification

1. A student or company registers.
2. `Registered($user)` is dispatched by `RegisteredUserController`.
3. Laravel detects that `User` implements `MustVerifyEmail`.
4. `User::sendEmailVerificationNotification()` sends `VerifyEmailNotification`.
5. The recipient opens the signed URL and the existing `VerifyEmailController` marks the email as verified.

### New Application To Company

1. A student submits an internship application through `ApplicationController::store`.
2. The application is created and the existing in-app notification is stored.
3. `AutomatedMailService::sendApplicationSubmittedToCompany($application)` resolves the internship owner.
4. The company receives an email containing applicant name, internship title, and a link to `perusahaan.applicants.show` for the submitted application.
5. If email fails, the service logs the error and the application submission still succeeds.

### Application Status Update To Student

1. A company updates an application status through `CompanyApplicantController::updateStatus`.
2. The application status is updated and the existing in-app notification is stored.
3. `AutomatedMailService::sendApplicationStatusUpdated($application, $oldStatus, $newStatus)` sends an email to the student.
4. The email includes internship title, company name, status label, and status-specific guidance.
5. If email fails, the service logs the error and the status update still succeeds.

## Templates

All new templates will follow the visual language of `resources/views/emails/reset-password.blade.php`: SIKARA header, clear title, concise body copy, primary CTA, fallback link when needed, and footer stating the email is automated.

Templates:

- `emails.verify-email`: verification CTA and signed verification link.
- `emails.application-submitted-company`: applicant and internship summary for company users.
- `emails.application-status-updated`: status update for student users.

The templates will avoid relying on external assets so Resend and common email clients can render them reliably.

## Error Handling

`AutomatedMailService` will use these rules:

- If the recipient is missing or the email is invalid, skip sending and log a warning.
- If notification sending throws, catch the exception and log event name, recipient id/email, application id, and exception message.
- Do not throw mail delivery errors back to registration, application submission, or status update flows.

Account verification uses Laravel's notification path. If the provider fails during registration, the user account should still be created. The implementation will avoid adding hard failures around verification email sending.

## Testing

Tests will use Laravel's notification fakes and mail configuration fakes rather than calling Resend.

Required coverage:

- Registration dispatches a branded verification notification for a new user.
- Submitting an application sends `ApplicationSubmittedToCompanyNotification` to the internship owner.
- Updating application status sends `ApplicationStatusUpdatedNotification` to the student applicant.
- Application submission and status update still complete when the mail service cannot deliver.

## Future Extensions

The same service can later add:

- Company verification approved or rejected emails after the admin verification feature exists.
- LMS enrollment or graduation emails.
- Event registration reminders.
- Optional database logging for email delivery attempts.
- Queueing by adding `ShouldQueue` to notification classes once queue workers are part of deployment.
