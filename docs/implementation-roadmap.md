# OgaFarm Implementation Roadmap

**Status:** Proposed delivery plan; not an approval of open product or vendor decisions

**Inputs reviewed:** `README.md`, `docs/technical-architecture.md`, `.github/copilot-instructions.md`, and the current repository
**Product baseline:** OgaFarm v60 (7 November 2026), as referenced by the project instructions

## 1. Goal and affected roles

Deliver OgaFarm as tested, secure end-to-end journeys in the existing Laravel application, then integrate future Flutter clients against stable server-side operations. Preserve the v60 business rules and prices. Treat architecture proposals as proposals and existing schema/model scaffolding as incomplete until a working flow and its tests exist.

Affected roles: farmers, farm suppliers, individual/business/group/diaspora buyers, transporters, job seekers, consultants, and administrators/operations staff. Public visitors are also in scope for anonymous discovery and share links.

## 2. Review findings

### Architecture coverage against v60

| v60 requirement area | Coverage in technical architecture | Implementation implication |
|---|---|---|
| Public approved browsing, shareable URLs, browser access without app install, and preserving destination through authentication | Covered as a target; no corresponding detail/product routes exist today. Invite attribution exists, but it does not preserve a general destination. | Build public pages and deep-link behavior as tested web journeys. Define approved/unavailable/expired presentation without silently deleting content. |
| Registration, verification, role-aware access, backend authorization, and admin least privilege | Covered, including sessions, proposed mobile tokens, policies, and Filament access. Current registration supports only five roles; authorization middleware is unused on routes; no policies were found. | Fix role persistence and explicitly authorize each protected operation and Filament access before adding more role options. |
| Server-verified payment, idempotency, entitlement events, reconciliation, and no client-side activation | Well covered as design requirements. Existing subscription/payment tables and plan view are not a checkout or webhook implementation. | Keep payment activation blocked on provider selection and approved renewal/refund semantics. |
| Referral attribution, direct-only rewards, 14-day hold, two lifetime grants, shareability, auditability, retry/concurrency safety | Rules are substantially covered, with open policy gates called out. Existing service is not connected to payment events or a scheduler. | Resolve the attribution and entitlement/reversal decisions before production grants. Preserve historical grant counts on reversal unless the owner explicitly approves a different policy. |
| Catalogue, procurement, groups, atomic inventory, logistics, jobs, markets, messaging, disputes, and operations | Domains and likely boundaries are covered. Detailed state transitions, cancellation/refund allocation, stock-reservation expiry, and group-funding rules are not settled. | Implement as vertical slices with owner-approved transitions; do not treat migrations as complete workflows. |
| Mobile, API, provider integrations, and deployment | The architecture appropriately proposes `/api/v1`, Sanctum, a shared Laravel domain layer, and provider adapters without claiming they exist or selecting vendors. | API contract, token lifecycle, provider choices, production database, hosting, and operating model remain gates. No Flutter application or API route file is present. |
| Accessibility, low bandwidth, notifications/consent, and AI privacy | Mentioned, but not translated into concrete feature acceptance criteria and controls. AI provider, allowed data, retention, and escalation remain unspecified. | Add explicit non-functional and privacy tests before those features enter release scope. |

### Repository facts and discrepancies

- The repository is a Laravel application with Blade, Vite, Filament, domain-oriented migrations/models, web authentication, role dashboards, an invite route, subscription-plan display, and one referral service. The actual configured default database is SQLite; no production database is selected.
- Current web routes cover the landing page, invite, registration/login/logout, email verification, dashboard, and subscription plans. There are no public storefront/product detail routes, API controllers/routes, Flutter client, payment webhook handler, completed order workflow, scheduler-based reward evaluator, or provider integration.
- There are nine domain migrations for identity, billing, referrals, catalogue, procurement, group orders, logistics, jobs/markets, and messaging/disputes/audit. This is data scaffolding, not proof those journeys work.
- The test suite contains only the example feature and unit tests (the architecture reports two passing example tests). It does not validate domain workflows or concurrency.
- `User::roles()` declares a many-to-many relation against the string value of a role enum rather than a role model; `hasRole()` separately queries role records. This is a concrete role-model correctness risk to resolve and test.
- `EnsureRole` is registered but unused by current routes. Filament has authentication middleware, but no explicit application-level admin eligibility method or domain policies were found.
- `Product::reserveStock()` performs a guarded transaction/row lock but is not called by an order workflow and has no reservation expiry/release lifecycle. Production locking behavior cannot be inferred from SQLite.
- `Storefront::scopePublicVisible()` filters publication and moderation, but does not enforce its comment's “live subscription” condition. The desired presentation/availability of seller content after subscription expiry needs an explicit rule before encoding it.
- The referral service hard-codes the proposed 30-day attribution window. Its reversal mutates the grant row and decrements `lifetime_grants_used`, which could restore a lifetime slot; that conflicts with the v60 lifetime-cap/no-reset intent unless the owner decides otherwise. It also lacks a verified-payment caller, scheduled evaluator, and domain tests.
- Plan prices are represented by a `float` enum method. Do not change the v60 values (₦30,000 / 3 months, ₦54,000 / 6 months, ₦95,000 / 12 months); payment work should use server-owned, fixed-precision money handling.
- `README.md` describes an API/authentication gateway and service boxes, while the technical architecture recommends a single Laravel modular monolith. Treat the latter as the current implementation recommendation; update the older illustrative diagram so it cannot be mistaken for a selected microservice architecture.
- `README.md` says the handoff does not select a framework, although Laravel is the current implementation. Its setup and environment template are illustrative and must not be copied as confirmed provider configuration.
- v60 source material is not included in the repository as a separate document; the README refers to a v59 supplement. Screen-level and detailed flow acceptance should be checked against the actual supplied v60 handoff when available.

## 3. Delivery phases

1. **Phase 0 — Baseline and decisions:** establish behavioral tests; resolve role, commercial, data, provider, and release gates without changing policy by assumption.
2. **Phase 1 — Identity, authorization, and public web foundation:** repair role behavior; protect admin and object access; complete anonymous public discovery and safe deep-link return.
3. **Phase 2 — Catalogue and transactional commerce:** seller publishing, inventory lifecycle, procurement/RFQ, orders, groups, and their failure paths. Production DB/locking and commercial state rules gate transactional implementation.
4. **Phase 3 — Billing and referral rewards:** integrate only after provider and policy approval; implement verified, replay-safe payment processing and transactional rewards.
5. **Phase 4 — Operations and role journeys:** logistics, jobs/markets, messaging/notifications, AI assistance, and moderation/disputes, each with explicit access and privacy boundaries.
6. **Phase 5 — Mobile integration and release:** stable API contract, Flutter clients, end-to-end regression, staging rehearsal, operations readiness, and release approval.

Phases are dependency-oriented, not dates or a commitment to a specific release scope.

## 4. Prioritized backlog

Priority meanings: **P0** = start/decision gate; **P1** = foundational user journey; **P2** = core marketplace; **P3** = monetization and referrals; **P4** = extended roles and release. A “no owner approval” task still requires normal code review and must preserve approved v60 rules.

### Phase 0 — Baseline and decisions

#### BASE-01 — Establish workflow test baseline (P0, no owner approval)

- **Objective:** Replace confidence based on example tests with focused tests of current authentication, role, invite, and plan-display behavior.
- **Required changes:** Add tests in the existing PHPUnit structure; record confirmed behaviors and failing expectations without widening registration roles or altering policy. Keep fixtures isolated and use the repository's existing conventions.
- **Dependencies:** None.
- **Responsible custom agent:** OgaFarm QA Engineer.
- **Acceptance criteria:** Tests clearly distinguish implemented routes from unimplemented flows; baseline failures are reported rather than silently encoded as intended product behavior; no production policy or prices change.
- **Required tests:** Feature coverage for anonymous landing/invite, registration validation and permitted current roles, login/logout/email verification access, role-aware dashboard selection, and subscription-plan page access. Add focused unit coverage only where a current domain helper is exercised.

#### GOV-01 — Approve product and technical decision register (P0, project-owner approval required)

- **Objective:** Close or explicitly defer decisions that would otherwise force developers to invent policy or infrastructure.
- **Required changes:** Record owners and approved answers for: launch role/registration matrix and multi-role conversion; consultant treatment; 30-day referral attribution; active-paying-referrer and account-owner/identity rules; renewal, earned-day, refund and reversal accounting; order/group cutoff, reservation timeout, partial fulfilment, fees, cancellations/refunds/disputes; whether the v60 prices remain commercially approved; launch/release scope; production database and hosting; payment, messaging, maps, email/SMS, object storage and AI providers; native token lifecycle/API contract; retention, consent, backup and recovery expectations.
- **Dependencies:** None; decisions can be made in parallel. Payment/referral implementation and dependent release tasks wait for applicable answers.
- **Responsible custom agent:** OgaFarm Project Manager.
- **Acceptance criteria:** Each item is marked approved, deferred with an owner, or explicitly out of scope; no proposed value is represented as approved; existing v60 prices and rules remain unchanged until written approval.
- **Required tests:** Not applicable to a decision task. Review the decision register for traceability and confirm all downstream blockers point to it.

### Phase 1 — Identity, authorization, public web

#### ID-01 — Correct role relationship and dashboard behavior (P1, no owner approval)

- **Objective:** Make the existing role records reliable for authorization and role-aware navigation.
- **Required changes:** Replace the invalid enum-string Eloquent relation with a type-safe relationship consistent with the `user_roles` schema; centralize or consistently reuse active-role checks; cover multi-role dashboard precedence without changing which roles public registration permits.
- **Dependencies:** BASE-01 recommended; no policy decision required for the currently supported roles.
- **Responsible custom agent:** OgaFarm Backend Developer.
- **Acceptance criteria:** Role relations return active role records correctly; inactive roles cannot authorize access; role assignment remains unique; current registration options remain unchanged until GOV-01.
- **Required tests:** Model/feature tests for assigned, inactive, multiple, and missing roles; dashboard selection for each currently supported role; reject an invalid/inactive role on protected operations.

#### SEC-01 — Enforce admin and object-level access (P1, no owner approval)

- **Objective:** Ensure successful authentication alone never grants admin or another user's data access.
- **Required changes:** Add explicit Filament panel eligibility using the existing server-side admin role, policies or equivalent domain authorization for implemented records, and route-level enforcement for protected operations. Do not expose admin self-registration or rely on hidden UI.
- **Dependencies:** ID-01.
- **Responsible custom agent:** OgaFarm Security & DevOps Engineer.
- **Acceptance criteria:** Non-admin authenticated users are denied the admin panel; unauthenticated users follow the established authentication flow; owners/participants can access only their authorized records; denial paths do not leak record data.
- **Required tests:** Feature tests for anonymous, ordinary authenticated, and admin users at panel access; owner-versus-non-owner checks for each implemented protected resource; regression test that public registration cannot assign admin.

#### WEB-01 — Deliver approved public storefront and product pages (P1, no owner approval)

- **Objective:** Let visitors discover approved farms, suppliers, and products in a mobile browser without app installation.
- **Required changes:** Add server-rendered public list/detail routes and views using existing storefront/product data; filter unpublished/unapproved content; show out-of-stock, unavailable, or expired product states without deleting historical content; provide canonical shareable URLs and safe metadata. Do not invent a rule that subscription expiry deletes or hides all seller content.
- **Dependencies:** BASE-01. Any decision about seller visibility after subscription expiry remains subject to GOV-01; implement only approved-content and availability behavior until clarified.
- **Responsible custom agent:** OgaFarm Web Frontend Developer.
- **Acceptance criteria:** Anonymous visitors can open approved public pages; unapproved/private records are not rendered; unavailable products show an informative state; links work without an app; private buyer, payment, identity, and referral data are absent.
- **Required tests:** HTTP feature tests for approved, unpublished, held/unapproved, unavailable, and missing records; assert sensitive fields are not present; browser/manual responsive and accessibility checks for mobile navigation and low-bandwidth-friendly content.

#### WEB-02 — Preserve safe deep-link destination through authentication (P1, no owner approval)

- **Objective:** Return a visitor to the intended internal page after sign-in or registration while keeping invite attribution separate.
- **Required changes:** Preserve a validated local destination (or opaque server-side state) through guest authentication and registration; reject arbitrary external redirect targets; keep the invite code session behavior independent from the destination.
- **Dependencies:** BASE-01; WEB-01 for public storefront/product destinations.
- **Responsible custom agent:** OgaFarm Backend Developer.
- **Acceptance criteria:** Valid internal destinations survive login/registration; missing/expired destinations fall back safely; external and malformed destinations cannot cause open redirects; invite attribution still records through existing flow without exposing referrer identity.
- **Required tests:** Feature tests for login and registration return paths, encoded/malformed/external URL rejection, no destination, and invite attribution plus destination preservation.

### Phase 2 — Catalogue and commerce

#### CAT-01 — Complete seller catalogue and moderation workflow (P2, no owner approval)

- **Objective:** Allow authorized farmers/suppliers to manage their storefront and product lifecycle.
- **Required changes:** Add seller-owned create/update/publish operations, validation, moderation state transitions, availability/condition handling including rescue produce, and audit records for moderation. Reuse existing schema where suitable; use migrations only for approved schema gaps.
- **Dependencies:** ID-01, SEC-01, WEB-01.
- **Responsible custom agent:** OgaFarm Marketplace Domain Engineer.
- **Acceptance criteria:** Sellers can mutate only their own records; public visibility follows approved/published state; unavailable or moderated listings have a clear state; no change to subscription prices or expiry policy is inferred.
- **Required tests:** Feature tests for seller ownership, invalid input, moderation/publishing transitions, public visibility, rescue labels, and expired/unavailable product display; audit assertions for moderation decisions.

#### DATA-01 — Select production database and validate transactional guarantees (P0/P2 gate, project-owner approval required)

- **Objective:** Confirm the database and deployment environment can support required transactions, uniqueness, locking, backup, and restore behavior.
- **Required changes:** Select the production relational database and environment with the owner; document migration/rollback and backup/restore procedure; validate uniqueness and row-lock behavior under concurrent inventory/payment/reward attempts. Do not assume the configured SQLite database is production.
- **Dependencies:** GOV-01 technical decisions.
- **Responsible custom agent:** OgaFarm Security & DevOps Engineer.
- **Acceptance criteria:** Provider and environment are explicitly approved; concurrency-sensitive behavior is demonstrated on the selected engine; staging migration and restore rehearsal succeeds; no production migration/deployment occurs without separate approval.
- **Required tests:** Engine-specific integration tests for concurrent stock reservation and idempotent unique-event insertion; migration up/down or forward-compatibility checks as appropriate; documented staging backup/restore rehearsal.

#### INV-01 — Implement inventory reservation lifecycle (P2, approval-gated)

- **Objective:** Prevent overselling across order creation, cancellation, timeout, and payment failure.
- **Required changes:** Persist reservation identity, quantity, status and expiry; reserve atomically with order intent; make release and sale transitions idempotent; record inventory events; add an expiry/recovery process. Do not treat the current `reserveStock()` helper as an end-to-end solution.
- **Dependencies:** CAT-01, DATA-01, GOV-01 order timeout/cancellation decisions.
- **Responsible custom agent:** OgaFarm Marketplace Domain Engineer.
- **Acceptance criteria:** Competing reservations cannot reduce stock below zero; each reservation is released or finalized once; retries do not duplicate events; historic order snapshots remain stable.
- **Required tests:** Selected-database concurrent reservation test; insufficient-stock test; repeated reserve/release/finalize tests; cancellation, payment-failure and expiry transition tests.

#### ORD-01 — Deliver procurement, orders, and group buying vertical slice (P2, approval-gated)

- **Objective:** Support buyer purchase and business RFQ/quote workflows with authorized state transitions.
- **Required changes:** Implement RFQ submission, supplier quotations, quote acceptance, order snapshots, participant-scoped reads, group membership/contribution/cutoff and order cancellation/refund requests. Keep payment state separate from order state; calculate totals server-side and connect inventory only through INV-01.
- **Dependencies:** CAT-01, INV-01, DATA-01, GOV-01 commercial/order/group decisions; verified payment authority from BILL-01 before accepting paid confirmation.
- **Responsible custom agent:** OgaFarm Marketplace Domain Engineer.
- **Acceptance criteria:** Only authorized buyers/suppliers can see or mutate their transactions; valid transitions are auditable; client-supplied prices or payment-success claims are ignored; cancellation/refund behavior follows approved policy.
- **Required tests:** Feature tests for RFQ/quote ownership, order price snapshots, legal/illegal transitions, group cutoff and participant isolation; idempotency and concurrent transition tests on selected database.

### Phase 3 — Billing and referrals

#### BILL-01 — Implement server-verified subscriptions and entitlements (P3, approval-gated)

- **Objective:** Activate paid farmer/supplier entitlements only from an authentic, verified server-side payment event.
- **Required changes:** After gateway approval, add a provider adapter, pending checkout state, signed webhook verification, unique provider-event processing, amount/currency/reference checks, entitlement event history, refunds/reconciliation and operational retry handling. Use fixed-precision/server-owned plan values; preserve ₦30,000 / 3 months, ₦54,000 / 6 months, and ₦95,000 / 12 months unless the owner approves changes.
- **Dependencies:** GOV-01 provider, commercial and renewal/refund decisions; DATA-01. No client-side success screen may activate an entitlement.
- **Responsible custom agent:** OgaFarm Billing & Referrals Engineer.
- **Acceptance criteria:** Invalid signatures, mismatched amounts/references, duplicates, replay, and client-only success cannot activate a plan; verified events apply once; retries and reconciliation are auditable; prices and periods match approved values.
- **Required tests:** Signed/invalid webhook tests, duplicate/replay and out-of-order event tests, amount/currency mismatch, failed/pending/refunded settlement, entitlement period/renewal tests, and concurrent duplicate event processing on the selected database. Use provider sandbox fixtures only after selection; no real payments.

#### REF-01 — Connect referral qualification and immutable reward history (P3, approval-gated)

- **Objective:** Grant exactly one 14-calendar-day reward for each eligible direct qualifying referral, respecting the verified owner's lifetime cap.
- **Required changes:** Connect verified subscription events to qualification hold and a scheduled evaluator; enforce eligibility and direct-only rules transactionally; preserve shareability at cap; make grants and reversals auditable and retry-safe. First resolve the existing hard-coded attribution window and the reversal behavior that currently decrements lifetime grant count. Do not assume reversal restores a slot.
- **Dependencies:** ID-01, SEC-01, BILL-01, DATA-01, GOV-01 attribution/identity/active-referrer/renewal/reversal decisions.
- **Responsible custom agent:** OgaFarm Billing & Referrals Engineer.
- **Acceptance criteria:** No reward without verified qualifying first payment and elapsed 14-day hold; maximum two lifetime grants per verified owner; no upstream reward; share/invite remains available at cap; retry, concurrency, and reversal preserve an auditable history without an unapproved cap reset.
- **Required tests:** Qualification boundary/hold tests, direct-only and ineligible-role tests, cap/continued-sharing tests, concurrent grants, duplicate worker/payment retries, reversal audit/history and entitlement accounting tests, renewal/role-change no-reset tests.

### Phase 4 — Operations and extended journeys

#### OPS-01 — Complete logistics, verification, and delivery operations (P4, approval-gated)

- **Objective:** Deliver participant-safe transport assignment and delivery completion for valid orders.
- **Required changes:** Implement transporter/vehicle verification review, capacity/availability, transport quote/assignment, delivery states and proof access; connect only to approved order transitions; store identity and proof documents privately with authorized access.
- **Dependencies:** SEC-01, ORD-01, DATA-01, GOV-01 verification, proof-retention and delivery/dispute rules; object-storage selection before provider-specific storage integration.
- **Responsible custom agent:** OgaFarm Marketplace Domain Engineer.
- **Acceptance criteria:** Only verified/eligible transporters can receive approved assignments; buyer, seller, transporter, and admin see only authorized details; proof is not publicly accessible; transitions and admin decisions are auditable.
- **Required tests:** Role/participant isolation, unverified transporter denial, assignment race/state-transition tests, private-file authorization, and delivery issue/dispute paths.

#### OPS-02 — Deliver jobs, markets, messaging, and AI with privacy controls (P4, approval-gated)

- **Objective:** Complete opportunity and communications journeys without exposing private data or implying unselected providers.
- **Required changes:** Implement job posting/application and market submission/moderation; clarify consultant behavior using GOV-01; add conversation membership and notification consent/preferences; define AI disclosure, authorized context, escalation, retention and provider adapter only after approval.
- **Dependencies:** SEC-01, GOV-01 role/consultant/consent/retention/provider decisions; selected infrastructure for queued notifications.
- **Responsible custom agent:** OgaFarm Backend Developer.
- **Acceptance criteria:** Applicants and conversation participants can access only their permitted records; moderated public content is distinguished from pending content; opt-out is respected; AI is clearly disclosed and cannot retrieve another user's private records; no provider is assumed.
- **Required tests:** Posting/application authorization, moderation visibility, conversation cross-account denial, notification consent/opt-out and retry idempotency; AI scope/disclosure tests if AI is approved and implemented.

### Phase 5 — API, mobile, and release

#### API-01 — Publish and implement versioned API contract (P4, approval-gated)

- **Objective:** Expose stable server-side operations for future Flutter clients without duplicating domain policy.
- **Required changes:** After API/auth decisions, register API routes, define request/response/error/pagination/versioning conventions, request validation, API resources, rate limits, idempotency, correlation IDs, and a revocable native token lifecycle. Reuse the same domain operations as web.
- **Dependencies:** GOV-01 API/token/rate-limit decisions, SEC-01, and the relevant completed domain tasks.
- **Responsible custom agent:** OgaFarm Solution Architect.
- **Acceptance criteria:** Contract is versioned and documented; every endpoint authorizes role and object access; sensitive model fields are excluded; token issue/revoke/expiry behavior matches approval; web and API return equivalent domain outcomes.
- **Required tests:** Contract/feature tests for auth lifecycle, validation/error envelope, pagination, authorization, field exposure, rate limiting, idempotent commands, and API compatibility.

#### MOB-01 — Integrate Flutter clients against approved API journeys (P4, approval-gated)

- **Objective:** Deliver role-appropriate Android/iOS journeys using the tested server contract.
- **Required changes:** Create or extend the Flutter application only after API-01; implement sign-in/token lifecycle and agreed public discovery, buyer/seller, or operations journeys; handle loading, empty, offline, expired, pending, unavailable, error, and permission-denied states accessibly.
- **Dependencies:** API-01 and selected launch scope from GOV-01; domain endpoints and privacy controls must be complete first.
- **Responsible custom agent:** OgaFarm Flutter Developer.
- **Acceptance criteria:** The app relies on server authorization and verified state; retries do not duplicate commands; public share links still work in browsers without the app; low-bandwidth use and session revocation are handled.
- **Required tests:** Flutter unit/widget tests for the agreed journeys and failure states; API integration tests; device/browser deep-link checks on supported platforms.

#### REL-01 — Complete release readiness and operational rehearsal (P4, project-owner release approval required)

- **Objective:** Demonstrate recoverability, observability, security, and end-to-end acceptance before a production release.
- **Required changes:** Configure only approved hosting, database, storage, queue and provider environments; document deployment/migration/rollback, secrets handling, monitoring/alerts, backup/restore, incident ownership and support; run cross-role staging/UAT. No production deployment or real payments/messages are authorized by this roadmap.
- **Dependencies:** DATA-01, BILL-01 and applicable provider tasks, API-01/MOB-01 if in approved scope, completed security and privacy tests, GOV-01 release-scope decision.
- **Responsible custom agent:** OgaFarm Security & DevOps Engineer.
- **Acceptance criteria:** Staging acceptance passes; migration and backup restore are rehearsed; secrets and sensitive payloads are absent from code/logs/client bundles; operational owners and rollback procedures are recorded; project owner explicitly approves production release separately.
- **Required tests:** Full agreed regression suite, cross-role end-to-end tests, staging migration/rollback rehearsal, backup restore, queue retry/failure checks, security/privacy checks, and accessibility/low-bandwidth checks.

#### DOC-01 — Reconcile project docs with approved implementation (P4, no owner approval for factual edits)

- **Objective:** Keep README and architecture documentation consistent with confirmed repository behavior and approved decisions.
- **Required changes:** Replace stale “stack not selected” wording with verified facts; distinguish the current modular monolith from illustrative service diagrams; update setup commands and environment-variable names only after verifying them; mark routes, providers, and features as implemented only after acceptance.
- **Dependencies:** Relevant implementation tasks and approvals; can begin factual corrections independently.
- **Responsible custom agent:** OgaFarm Project Manager.
- **Acceptance criteria:** Documentation makes current implementation, proposed target, and unresolved choices visually distinct; no credentials, vendor assumption, invented command, or unapproved business rule is introduced.
- **Required tests:** Documentation review against route list, manifests, migrations and test output; run documentation/link checks if configured.

## 5. First five tasks safe to start

These can begin without changing business rules, prices, providers, or infrastructure selections:

1. **BASE-01 — Establish workflow test baseline.**
2. **ID-01 — Correct role relationship and dashboard behavior.** Keep the current registration role list unchanged.
3. **SEC-01 — Enforce admin and object-level access.** Depends on ID-01 for reliable role checks.
4. **WEB-01 — Deliver approved public storefront and product pages.** Do not decide subscription-expiry visibility beyond approved/unavailable states.
5. **WEB-02 — Preserve safe deep-link destination through authentication.** Keep invite attribution separate and reject external redirects.

Tasks 2–5 may be prepared in parallel, but changes that depend on role correctness or public routes should merge only after their stated dependency is satisfied. BASE-01 should establish the regression baseline before broad behavior changes.

## 6. Project-owner approval gates

### Product/commercial approval required before affected implementation

- Launch role matrix, account conversion/multiple-role rules, and consultant treatment.
- Proposed 30-day referral attribution window, eligible active referrer, verified owner identity, and duplicate/merged-account handling.
- Renewal, earned reward days, expiry, refund/reversal and lifetime-cap accounting. The cap must not be reset by renewal, reactivation or role changes under v60.
- Group/order cutoffs, stock reservation timeout, partial fulfillment, fees, cancellation, refund allocation and disputes.
- Confirmation of commercial approval for the listed v60 subscription prices; this roadmap does not change them.
- Public content treatment when a seller subscription expires.
- Messaging consent/retention, AI data scope/disclosure/escalation, verification and proof-document retention.
- Signed feature/release scope and final production release approval.

### Technical/operational approval required; no provider is presumed

- Production database and hosting/cloud, deployment topology, region, operational ownership and budget.
- Payment gateway and its signature, event, refund, and reconciliation contract.
- Email/SMS/WhatsApp, maps, object storage, queue operations, error monitoring, and AI providers.
- API contract, native token expiry/revocation/recovery, rate limits, and compatibility policy.
- Backup/restore objectives, data retention/deletion, and production incident ownership.

Until approved, teams may perform repository inspection, tests, provider-neutral interfaces/design, and non-production experiments that do not encode or trigger the unresolved choice. No real payment, production deployment, external message, or destructive migration is included.
