# MyAkad Project Direction

## Objective

MyAkad is a wedding invitation platform where customers choose or purchase one or more templates, select which template/invitation they are currently editing, complete invitation content, preview it accurately, then publish the selected invitation.

The product must make the active template explicit at every customer step. A customer should never wonder which template will be published.

## Core Customer Flow

1. Browse templates.
2. Preview a template.
3. Checkout and pay for the selected template/package.
4. Land on the customer dashboard with purchased templates visible.
5. Select the active template/invitation to manage.
6. Fill invitation content for the selected template.
7. Upload media and gallery assets.
8. Customize sections and ornaments for the selected template.
9. Manage guests and sharing links for the selected invitation.
10. Preview the selected invitation.
11. Publish or unpublish the selected invitation.

## Current Corrections Needed

- Add a purchased-template selector on the customer dashboard.
- Store and use an explicit active invitation per user.
- Ensure paid template order items create an invitation per purchased template, not only the first invitation.
- Make dashboard, editor, customize, settings, gallery, and guests operate on the active invitation.
- Normalize RSVP attendance values across public RSVP, dashboard stats, and guest stats.
- Keep publish validation in one service.
- Align auth and customer dashboard UI with the app design system.
- Replace ad hoc checkout `fetch`/`alert` flows with Inertia-friendly form/error state.
- Keep template data contract complete so saved database fields are available to template files.
- Repair tests so they match current controller props and run against a working test database.

## Engineering Principles

- Follow Laravel and Inertia conventions already used in the app.
- Prefer Form Requests for write validation.
- Prefer named routes and Wayfinder-ready route structures.
- Keep controllers thin; move customer-flow decisions into services.
- Add tests for each important user flow before or alongside fixes.
- Do not add new dependencies without approval.

## Near-Term Implementation Tasks

- [x] Add `active_invitation_id` to users.
- [x] Add `User::activeInvitation()` and helper methods for active invitation resolution.
- [x] Add a customer invitation/template service.
- [x] Add a dashboard route/action to select active invitation.
- [x] Update `OrderService` to create invitations for every paid template item.
- [x] Update dashboard props to include purchased/owned invitations and active invitation.
- [x] Update editor/customize/settings/gallery/guest controllers to use the active invitation.
- [x] Update dashboard Vue to show a purchased template selector before editing.
- [x] Normalize attendance values to `yes`, `no`, and `pending`.
- [ ] Apply migrations in the runtime database.
- [ ] Update and run focused feature tests.
