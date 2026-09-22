# UI Modernization Strategy

## Goal

Modernize the existing CI3 system UI so it looks cleaner, more modern and more user-friendly.

Current priority is **UI/UX only**.

Do not rewrite business logic or migrate the backend framework.

---

## Current Frontend Stack

The system currently uses:

- CodeIgniter 3
- Bootstrap 3
- Ace Admin
- jQuery
- Select2
- Bootstrap Datepicker / Datetimepicker
- Gritter
- Highcharts
- custom CSS / JS

Many existing pages depend on Bootstrap 3 markup, jQuery selectors, modal events and existing DOM IDs.

---

## Main Decision

### Do not migrate to Bootstrap 5 in Phase 1.

Bootstrap 5 migration would increase regression risk because existing pages rely heavily on:

- Bootstrap 3 modal/dropdown behavior
- Ace components
- jQuery
- existing plugins
- old input-group/panel markup
- existing JavaScript selectors

Phase 1 should modernize the visual layer while keeping the existing frontend behavior.

---

## Modernization Approach

Create a shared stylesheet:

```text
css/ui-modern.css
```

Load it after existing application/theme CSS.

Use it to modernize:

- layout
- navbar
- sidebar
- cards
- forms
- buttons
- tables
- filters
- badges
- alerts
- modals
- responsive behavior

Prefer CSS overrides and small safe markup improvements.

Do not modify vendor Bootstrap/Ace files unless absolutely necessary.

---

## Compatibility Rules

Treat these as functional contracts:

- DOM IDs
- form field names
- form actions
- modal IDs
- AJAX URLs
- JavaScript selectors
- `data-*` attributes
- PHP variables
- ACL conditions
- routes
- hidden fields

Do not rename or remove them only for cleaner markup.

New presentation classes may be added freely.

---

## Scope Rules

UI work must NOT become an excuse to:

- rewrite Controllers or Models
- change database logic
- change routes
- rewrite AJAX flow
- replace jQuery
- replace existing plugins
- migrate CodeIgniter
- introduce Vue / React / Tailwind
- introduce a frontend build system
- remove ACL checks

Preserve existing behavior first.

---

## Phase 1

Use these as reference implementations:

1. Shared application shell
   - header
   - navbar
   - sidebar
   - footer

2. Login page

3. Dashboard

4. Customer list page
   - page header
   - filters
   - table
   - actions
   - modal

5. Shared styles
   - forms
   - buttons
   - tables
   - alerts
   - badges
   - modals
   - responsive layout

Do not modernize the entire application in one task.

---

## Large / Complex Pages

Large pages such as:

```text
application/views/customer/customer_detail.php
```

should not be completely rewritten during Phase 1.

Apply shared styling first.

Modernize complex forms section-by-section later.

---

## Responsive Goal

The modernized UI should work reasonably on:

- desktop
- laptop
- tablet
- mobile

Important functionality must remain accessible.

Tables may scroll horizontally when necessary.

---

## Rollout

```text
Phase 1
Foundation + reference pages

Phase 2
Common CRUD/list pages

Phase 3
Large forms and complex workflows

Phase 4
Billing / payment / reporting pages

Phase 5
Remaining UI consistency cleanup

Future
Evaluate Bootstrap 5 migration separately
```

---

## Codex Working Rule

For every UI task:

1. Read this document.
2. Read `UI_DESIGN_SYSTEM.md`.
3. Read the relevant task in `UI_TASKS.md`.
4. Inspect only necessary files.
5. Preserve existing functional selectors and behavior.
6. Make small, reviewable changes.
7. Avoid unrelated cleanup.
8. Report changed files and regression-sensitive areas.

The goal is to progressively modernize the UI without destabilizing the existing system.