# OgaFarm — Workspace Instructions

## Product source of truth
Use the OgaFarm Application Flow & Detailed UX Design, Technical Architecture and Visual Architecture handoff, version 60 (7 November 2026), supplied by the user, as the current product-design baseline.

Before implementing a requirement, inspect the repository and identify the actual implementation state. The handoff describes proposed architecture and expected behavior; it does not prove that a feature, API, payment integration, identity verification, inventory system, or real-time tracking is already implemented.

If repository behavior conflicts with the v60 handoff, report the discrepancy. Do not silently change scope or treat proposed design choices as approved technical decisions.

## Product overview
OgaFarm is a multi-role agricultural marketplace with:
- Public responsive web pages and shareable deep links.
- Flutter Android/iOS applications.
- Role-aware dashboards and an operations/admin console.
- Farmers and farm suppliers as paid members; buyer roles and transporters register free, subject to approved role rules.
- Marketplace catalogue, inventory, buyer orders, business RFQs and quotes.
- Group buying, rescue/imperfect produce, logistics and delivery.
- Jobs, consultants, markets, messaging, notifications and AI assistance.
- Invite & Share, referral attribution, Membership Rewards and an auditable reward ledger.
- Subscription billing, payment verification, moderation, disputes and audit operations.

## Important business rules from v60
- Farmer/supplier subscription prices are specified as ₦30,000 / 3 months, ₦54,000 / 6 months, and ₦95,000 / 12 months. Confirm commercial approval before changing them.
- Only eligible paying farmer/supplier referrers can earn rewards.
- A qualifying direct first-time paid farmer/supplier referral can earn 14 calendar days after the 14-day validation period.
- A verified paying account owner can receive at most two lifetime referral rewards (28 days total). Renewal, reactivation or role changes do not reset the cap.
- All supported roles may continue sharing/inviting; reaching the reward cap must not disable sharing.
- Only direct/first-level referrals can earn an upstream reward; no second- or third-level rewards.
- Referral attribution window (30 days proposed), consultant role treatment, entitlement/renewal accounting and other open decisions require product-owner approval.
- Payment success must be verified server-side using signed webhooks, unique provider event IDs and idempotent processing. Never activate paid entitlements from a client-side success screen.
- Referral rewards must be granted transactionally, with concurrency protection and an auditable/immutable ledger; retries must not double-grant.
- Inventory reservations must be atomic to prevent overselling.
- Role and object authorization must be enforced by the backend, not only by hiding UI.
- Preserve original deep-link destinations through sign-in/registration.
- Public pages should allow anonymous browsing of approved content without requiring an app install.
- Never expose private buyer conversations, payment details, identity documents, referral personal data or secrets.

## Engineering rules
1. Inspect the existing repository, package manifests, routes, tests, schema and deployment configuration before proposing implementation.
2. Do not assume a framework, language, database, cloud provider, payment gateway, messaging provider or API naming. Discover it from the repository or ask.
3. Keep changes small, maintainable, secure and consistent with established project conventions.
4. Do not introduce dependencies or rewrite architecture without explaining the need and getting approval when the change is material.
5. Never invent completed functionality, test results, API contracts or infrastructure.
6. Keep secrets out of source control, logs, client bundles and documentation.
7. Add/update tests for behavior changes. Run relevant checks and report commands and actual outcomes.
8. Use migrations for schema changes; preserve data and explain rollback considerations.
9. Consider loading, empty, offline, error, expired, pending, unavailable, moderation and permission-denied states.
10. For mobile/low-bandwidth use, consider accessible controls, responsive layouts, compressed media and graceful network failure.
11. Do not make destructive data changes, production deployments, real payments, external messages or irreversible actions without explicit approval.

## Required working style
- State what you inspected before making significant changes.
- Separate facts found in the repository from assumptions and recommendations.
- For implementation tasks, provide a concise plan, make the changes, run checks and summarize files changed.
- If a requirement is ambiguous or the v60 document marks it as an open decision, stop and ask rather than inventing a policy.
