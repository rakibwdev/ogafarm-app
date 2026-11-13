# OgaFarm

**OgaFarm** is a proposed agricultural marketplace and farm-to-buyer platform designed to connect farmers, farm suppliers, buyers, businesses, group buyers, transporters, job seekers, consultants, and administrators.

The platform is intended to support public farm and supplier storefronts, produce discovery, procurement and RFQs, group buying, order fulfilment, transport coordination, subscriptions, referrals, jobs, market information, and administrative operations.

> **Project status:** This README is based on the OgaFarm Application Flow & Visual Architecture handoff (v60) and its architecture supplement. The handoff describes a proposed product and implementation design; it does not establish that the features, APIs, integrations, or production infrastructure have already been implemented.
>
> **Product domain in the handoff:** `farmoga.com`

---

## Contents

- [Product goals](#product-goals)
- [User roles](#user-roles)
- [Core modules](#core-modules)
- [Key user journeys](#key-user-journeys)
- [Business rules](#business-rules)
- [Proposed technical architecture](#proposed-technical-architecture)
- [Core data domains](#core-data-domains)
- [Public routes and sharing](#public-routes-and-sharing)
- [Security and reliability requirements](#security-and-reliability-requirements)
- [Quality assurance and acceptance criteria](#quality-assurance-and-acceptance-criteria)
- [Development setup](#development-setup)
- [Repository structure](#repository-structure)
- [Project decisions still requiring approval](#project-decisions-still-requiring-approval)
- [Contributing](#contributing)

## Product goals

OgaFarm aims to:

1. Help farmers and suppliers publish discoverable storefronts and product catalogues.
2. Let individuals, businesses, groups, and diaspora buyers discover produce and procure from suppliers.
3. Support direct purchases, business requests for quotation (RFQs), and group purchases.
4. Coordinate order preparation, transport assignment, delivery proof, and dispute handling.
5. Provide subscription and entitlement management for paid farmer and supplier accounts.
6. Support unlimited sharing and invitation links, with a controlled lifetime referral reward programme.
7. Provide job, consultant, and market-directory features.
8. Give administrators tools for verification, moderation, payment review, disputes, fraud checks, and audit reporting.
9. Prioritise mobile-first usability, accessible interfaces, low-bandwidth access, and browser-accessible public pages.

## User roles

The product handoff describes the following roles and account types:

| Role | Main capabilities |
|---|---|
| Farmer | Manage farm profile and storefront, publish listings, manage availability, orders, RFQs, promotions, transport, reports, reviews, and invitations. |
| Farm supplier | Manage supplier storefront and catalogue, quotes, orders, inventory, subscription, and invitations. |
| Individual buyer | Browse produce, save listings, place orders, message sellers, and track purchases. |
| Business buyer | Submit procurement RFQs, compare quotations, place orders, and coordinate fulfilment. |
| Group buyer / organiser | Create or join group purchases, invite participants, manage group terms, and coordinate payment and pickup details. |
| Diaspora buyer | Arrange purchases for recipients, compare options, organise group orders, and track fulfilment. |
| Transporter | Complete identity and vehicle verification, manage capacity and availability, respond to delivery offers, and handle assigned jobs. |
| Job seeker | Maintain a profile, browse opportunities, apply for jobs, and track applications. |
| Consultant | Present services or a portfolio and manage enquiries or service-related interactions, subject to the final role definition. |
| Administrator | Manage verification, moderation, payments, disputes, markets, referrals, reward ledger, audit logs, and operational reports. |

A user may hold multiple permitted roles. Role permissions and access to records must be enforced by the backend, not only by hiding or showing frontend controls.

## Core modules

### 1. Public website and discovery

- Public home and produce-browsing pages.
- Public farmer and supplier storefronts.
- Public product details and share previews.
- Supplier discovery, jobs, markets, pricing, and partner information.
- Anonymous browsing of approved public content.
- Browser-accessible shared links; visitors should not need to install the mobile app just to view public pages.

### 2. Identity, registration, and verification

- Role-aware registration and onboarding.
- Email and/or phone verification.
- Profile, farm, and business information.
- Verified owner identity where required.
- Role and object-level permissions.
- Token lifecycle and authenticated access.

### 3. Storefronts, catalogue, and inventory

- Farmer and supplier public profiles with unique public slugs.
- Product catalogue and category navigation.
- Product availability and inventory events.
- Listing moderation and rescue listings.
- Shareable storefront and product previews.
- Graceful handling of unavailable or expired listings.

### 4. Procurement, RFQs, orders, and group buying

- Buyer browsing and purchase flow.
- Business RFQ submission and supplier quotations.
- Quote comparison and order creation.
- Group organiser, invitees, group terms, cut-off time, and join flow.
- Individual group payment obligations and pickup points.
- Inventory reservation and order-state management.
- Cancellation, refund, and dispute workflows.

### 5. Logistics and delivery

- Transporter identity and vehicle verification.
- Transporter capacity and availability.
- Delivery quotes and assignment.
- Pickup and delivery status.
- Delivery proof and tracking.
- Delivery issue and dispute handling.

### 6. Billing, subscriptions, and entitlements

The handoff identifies paid subscriptions for farmer and farm-supplier accounts. The listed plan prices are:

| Plan | Price |
|---|---:|
| 3 months | ₦30,000 |
| 6 months | ₦54,000 |
| 12 months | ₦95,000 |

These are product-handoff values and should not be changed without written product-owner approval.

The backend is responsible for verifying payment gateway events and maintaining subscription entitlements. A client-side success screen must never be treated as proof of payment.

### 7. Invite & Share and referral rewards

- Users can share storefronts, products, jobs, group purchases, and invitation links.
- Sharing remains available to every supported role.
- Referral attribution records the direct referrer, invited account, and source.
- Only eligible paying farmer/supplier referrers can qualify for subscription rewards.
- Referral rewards are direct only; there are no second- or third-level upstream rewards.
- Reward eligibility, validation, lifetime limits, reversals, and audit history must be enforced server-side.

### 8. Jobs, consultants, and markets

- Employer job listings and job applications.
- Job seeker profiles and application status.
- Consultant profiles, portfolios, or service listings.
- Market submissions and moderation.
- Public opportunity and market discovery pages.

### 9. Messaging, notifications, and AI assistance

- In-app notifications and messaging.
- Opt-in WhatsApp/SMS and transactional email integrations, where selected.
- Notification retry and delivery-status tracking.
- AI assistant interactions with clear disclosure and access boundaries.
- Human escalation where appropriate.
- Private buyer conversations and farmer data must not be exposed across accounts.

### 10. Administration and reporting

- Account and transporter verification queues.
- Listing and market moderation.
- Payment review and reconciliation.
- Dispute handling.
- Referral fraud review and reward-ledger inspection.
- Audit logs and operational reporting.
- Administrative actions protected by least-privilege permissions.

## Key user journeys

### General onboarding

1. A visitor opens the public website or a shared deep link.
2. The visitor browses public content or selects an account type.
3. The user registers and verifies their contact details.
4. The user completes the required profile or business/farm information.
5. A farmer or supplier selects a subscription plan and completes payment.
6. The backend verifies the signed payment event and activates the relevant entitlement.
7. The user accesses the role-appropriate dashboard.

Free account types follow their applicable onboarding flow without a farmer/supplier subscription requirement.

### Buyer-to-fulfilment flow

1. Discover a public farm, supplier, or product.
2. Authenticate as a buyer or recipient when required.
3. Choose a purchase route: direct purchase or business RFQ.
4. Check stock or request/compare quotations.
5. Reserve the required quantity and confirm order terms and delivery details.
6. Verify payment through a server-side gateway event.
7. Farmer/supplier confirms and prepares the order.
8. Quote, assign, and coordinate transport.
9. Record delivery proof, completion, and review.

Expected exception paths include out-of-stock re-quotation, failed or pending payments, delivery issues, disputes, refunds, and reconciliation.

### Referral reward flow

1. A user shares an invitation link.
2. A visitor opens the public HTTPS link.
3. The platform records direct referral attribution during signup.
4. The backend checks referrer and referred-account eligibility.
5. The referred farmer/supplier completes a first qualifying paid subscription.
6. Payment settlement and identity are verified.
7. A 14-day validation period is observed.
8. If the referral remains eligible, the backend grants 14 calendar days through an auditable reward ledger, subject to the lifetime cap.
9. Notifications are sent after the transaction commits.

The referral cap does not disable invitations or sharing.

## Business rules

The following rules are stated in the product/architecture handoff and should be treated as business-critical:

### Subscription and referral rules

- Farmer and farm-supplier subscription prices are ₦30,000 for 3 months, ₦54,000 for 6 months, and ₦95,000 for 12 months.
- Only eligible paying farmer/supplier referrers can receive subscription rewards.
- A qualifying referral is a direct, first-time paid farmer/supplier referral.
- The referred account must complete its first qualifying paid subscription.
- A 14-day validation period applies before the reward is granted.
- Each qualifying reward adds 14 calendar days.
- A verified paying account owner can receive at most two lifetime referral rewards: 28 days in total.
- Renewals, reactivation, or role changes do not reset the lifetime reward cap.
- All supported roles may continue sharing and inviting, even after the reward cap is reached.
- No second- or third-level referral rewards are allowed.
- Invalid rewards must be reversible through auditable ledger entries.

### Payment and inventory rules

- Verify payment gateway signatures and relevant payment details on the server.
- Enforce unique provider event IDs and idempotent webhook processing.
- Do not activate subscriptions from client-side payment success screens.
- Reconcile payments, refunds, and entitlement changes.
- Reserve inventory atomically to prevent overselling.
- Protect order, group-payment, refund, and transport state transitions with durable records and audit history.

### Access and navigation rules

- Enforce role and object authorization on the server.
- Allow anonymous visitors to browse approved public content.
- Require appropriate authentication for transactions and applications.
- Preserve the original deep-link destination through authentication.
- Do not silently delete listings when a paid membership expires; show the appropriate expired or renewal state.

## Proposed technical architecture

The handoff proposes the following logical architecture. It does **not** select a final programming language, framework, database provider, cloud provider, or payment gateway.

```text
Public Website / PWA       Flutter Mobile Apps       Admin Console
         |                        |                        |
         +------------------------+------------------------+
                                  |
                     API / Authentication Gateway
                         HTTPS, RBAC, rate limits
                                  |
        +-------------------------+-------------------------+
        |                         |                         |
 Marketplace & Orders      Growth & Subscription       Operations
 Catalogue / RFQ / Groups   Referrals / Payments        Jobs / Transport
                            / Rewards                    / Markets
        |                         |                         |
        +-------------------------+-------------------------+
                                  |
             Relational Database / Object Storage
                                  |
                       Async Worker / Queue
                                  |
          External payment / SMS / WhatsApp / maps services
```

### Proposed service boundaries

| Service/domain | Responsibilities |
|---|---|
| Identity & Access | Registration, verification, roles, permissions, and token lifecycle. |
| Storefront & Catalogue | Public farm/supplier pages, products, inventory, share previews, and moderation. |
| Procurement & Orders | Buyer carts, RFQs, quotations, group allocations, reservations, and order states. |
| Logistics | Transporter verification, vehicles, capacity, quotes, assignments, delivery proof, and tracking. |
| Billing & Entitlements | Plans, payment webhooks, paid periods, earned days, renewals, refunds, and audit events. |
| Referrals & Growth | Invitation codes, direct attribution, lifetime reward limits, sharing events, and reward ledger. |
| Jobs & Markets | Employer listings, applications, consultant profiles, market submissions, and moderation. |
| Messaging & AI | Consent-based notifications, assistant interactions, human escalation, and retention controls. |
| Admin & Reporting | Verification, disputes, fraud review, audit logs, and operational reporting. |

### Technology decisions

The implementation team must inspect the actual repository and obtain approval before selecting or changing the technology stack. The architecture supplement gives a relational transactional database such as PostgreSQL as an example, not as a confirmed selection.

Do not assume a framework, database, hosting provider, payment gateway, messaging provider, maps provider, or API naming convention based only on this README.

## Core data domains

The architecture handoff identifies these main data areas:

- **Identity:** users, user roles, verifications.
- **Billing:** subscriptions, payments, entitlement events.
- **Marketplace:** storefronts, products, inventory events.
- **Procurement:** buyers, RFQs, quotes, orders, order lines, group purchases.
- **Logistics:** transporters, vehicles, capacity, delivery assignments, delivery proof.
- **Growth:** referrals, referral attribution, reward ledger.
- **Operations:** notifications, consent, disputes, moderation, audit logs.
- **Jobs and markets:** employers, job listings, applications, consultant profiles, market records.

Important data constraints include unique payment-provider event IDs, unique storefront slugs, unique qualifying referral relationships, durable entitlement records, and auditable inventory and reward events.

## Public routes and sharing

The architecture supplement proposes these route patterns:

| Route | Expected behaviour |
|---|---|
| `/farm/{slug}` | Public farmer profile and active catalogue. |
| `/supplier/{slug}` | Public supplier profile and active catalogue. |
| `/product/{id}` | Current product quantity/price, condition, and purchase or RFQ action. |
| `/group/{id}` | Group terms, organiser, cut-off, and join flow. |
| `/jobs/{id}` | Public opportunity with authenticated application route. |
| `/invite/{code}` | Signup landing page with direct referral attribution. |

These are proposed route patterns; confirm the existing application routing before implementing them. Shared links should preserve their intended destination after sign-in. Referral pages must not reveal private referrer identity.

## Security and reliability requirements

- Enforce server-side role and object-level authorization.
- Apply least-privilege access and use separate administrative accounts.
- Keep secrets out of source control and client applications.
- Verify payment webhooks, defend against replay, and reconcile payment events.
- Store private verification documents in protected storage; use signed access URLs where appropriate.
- Apply self-referral and duplicate checks, rate limits, identity review, and reversible reward-ledger entries.
- Obtain consent for marketing communications and support unsubscribe handling.
- Use structured logs, monitored background queues, alerts, and retry-safe processing.
- Maintain backups and test restoration.
- Use staging environments and test payment integrations before production.
- Disclose AI assistance and restrict AI data access to the authorised user's scope.

## Quality assurance and acceptance criteria

The architecture supplement lists the following minimum acceptance tests:

1. A public WhatsApp storefront link loads the correct farm and stock in a mobile browser without requiring an app installation.
2. A new referred paying farmer qualifies only after verified settlement and the 14-day validation period, producing exactly one 14-day grant.
3. Two qualifying rewards produce 28 lifetime days; a third referral is tracked but does not extend the entitlement.
4. Renewing or changing between 3-, 6-, and 12-month plans never resets the two-reward lifetime cap.
5. Simultaneous payment callbacks and worker retries cannot double-grant rewards or oversell stock.
6. A second- or third-level conversion cannot reward an upstream ancestor.
7. Free business buyers, consultants, and job applicants can be referred and tracked without subscription credit.
8. Group payment, refund, and transport assignment transitions are auditable and access-controlled.
9. An expired shared product link displays an informative unavailable state rather than broken navigation.
10. Database backups can be restored and migrations can be deployed successfully in staging.

Additional testing should cover permissions, validation, accessibility, low-bandwidth performance, notification consent, failure recovery, and cross-role data isolation.

## Development setup

The handoff does not specify the final implementation stack or repository scripts. Therefore, there are no reliable install, environment, or run commands to publish here yet.

Before adding setup commands:

1. Inspect the repository to identify its actual applications, package managers, frameworks, and runtime versions.
2. Identify required services such as the database, cache, queue, object storage, and external integrations.
3. Document environment variable **names and purposes only**. Never put secret values in this README.
4. Add the verified install, migration, seed, development, test, and build commands.
5. Document how to run the application safely with test credentials and sandbox integrations.

### Environment configuration template

Create the appropriate environment file for the selected stack and document required variables in an `.env.example` file. The names below are illustrative categories, not confirmed variable names:

```dotenv
# Application
APP_ENV=
APP_URL=

# Database
DATABASE_URL=

# Authentication
AUTH_SECRET=

# Object storage
STORAGE_ENDPOINT=
STORAGE_BUCKET=

# Payment gateway
PAYMENT_PUBLIC_KEY=
PAYMENT_SECRET_KEY=
PAYMENT_WEBHOOK_SECRET=

# Notifications
SMS_PROVIDER_KEY=
WHATSAPP_PROVIDER_KEY=
EMAIL_PROVIDER_KEY=
```

Do not commit `.env` files, private keys, access tokens, production credentials, or real customer data.

## Repository structure

The final source tree must reflect the actual selected stack. A possible logical organisation is:

```text
ogafarm/
├── apps/
│   ├── public-web/
│   ├── mobile/
│   └── admin/
├── services/
│   ├── identity/
│   ├── marketplace/
│   ├── procurement/
│   ├── logistics/
│   ├── billing/
│   ├── referrals/
│   ├── jobs-markets/
│   └── notifications/
├── packages/
│   ├── shared-types/
│   └── shared-config/
├── infrastructure/
├── docs/
├── tests/
├── .github/
│   ├── agents/
│   └── copilot-instructions.md
├── .env.example
└── README.md
```

This is an illustrative domain-based layout, not a statement about the current repository. Keep the actual structure if the project already uses a different architecture.

## Project decisions still requiring approval

The handoff identifies open decisions that must be resolved by the product owner and engineering team:

- Whether Consultant is a standalone account role or a profile specialisation.
- Whether the proposed 30-day first-valid referral attribution window is approved.
- The precise renewal and earned-day accounting rules.
- What qualifies as an active paying referrer during the validation period.
- Which features are included in the signed US$4,500 / three-phase scope.
- Final technology stack, API contracts, database schema/ERD, sequence diagrams, cloud topology, and deployment plan.
- Payment, messaging, maps, and hosting providers.
- Infrastructure ownership, recovery procedure, and monthly hosting estimates.

Do not silently make product or commercial decisions in code. Record assumptions and request approval where required.

## Contributing

Before implementing a feature:

1. Read the relevant product and architecture requirements.
2. Inspect the existing repository and follow its established conventions.
3. Confirm whether the requirement is approved, proposed, or still an open decision.
4. Define acceptance criteria and affected roles, permissions, and data.
5. Implement backend validation and authorization; do not rely on frontend restrictions.
6. Add or update unit, integration, and end-to-end tests.
7. Consider retries, duplicate events, concurrency, auditability, and failure states.
8. Update the relevant documentation.

### Safe engineering rules

- Do not perform destructive database changes without explicit approval.
- Do not deploy to production or trigger real payments/messages without explicit approval.
- Do not expose private user, payment, identity, or referral information.
- Do not invent API contracts, database fields, or provider integrations.
- Make payment, inventory, entitlement, and referral operations idempotent and concurrency-safe.
- Keep business-critical state transitions auditable.

---

## Source of this README

Prepared from **OgaFarm Application Flow & Detailed UX Design / Application Flow & Visual Architecture, v60**, and its **Technical Architecture & Backend Design supplement, v59**. Where the handoff describes a proposal or open decision, this README preserves that status rather than presenting it as an implemented feature.
