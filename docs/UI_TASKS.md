# UI Modernization Tasks

Use together with:

- `UI_MODERNIZATION.md`
- `UI_DESIGN_SYSTEM.md`

Codex should implement **only the requested task**, not the whole roadmap at once.

---

## Phase 1 — Foundation

### Task 1 — Shared Modern UI Layer
- [x] Create `css/ui-modern.css`
- [x] Load it after existing application/theme CSS
- [x] Load it for login pages where applicable
- [x] Add shared styles for:
  - page layout
  - typography
  - cards/panels
  - forms
  - buttons
  - tables
  - badges
  - alerts
  - modals
- [x] Add basic responsive rules
- [x] Do not modify vendor Bootstrap/Ace files

**Acceptance**
- Existing pages still function
- No route/JS/business logic changes
- New styles can be reused by later pages

---

### Task 2 — Application Shell
Target:

```text
application/views/templates/header.php
application/views/templates/menu.php
application/views/templates/footer.php
```

- [x] Modernize navbar appearance
- [x] Modernize sidebar appearance
- [x] Improve spacing and content area
- [x] Improve active/hover menu states
- [x] Improve user dropdown
- [x] Improve responsive navigation

**Preserve**
- ACL conditions
- menu links
- submenu behavior
- sidebar collapse
- Account Settings
- Logout

**Do not**
- rewrite menu architecture
- regroup/remove menu items
- change routes

---

### Task 3 — Login Page
Target:

```text
application/views/templates/login_header.php
application/views/pages/login.php
application/views/templates/login_footer.php
```

- [x] Create modern centered login card
- [x] Improve logo/title area
- [x] Modernize username/password fields
- [x] Improve captcha layout
- [x] Improve error display
- [x] Improve login button
- [x] Improve mobile layout

**Preserve**
- authentication flow
- field names
- captcha
- Developer Mode
- Customer Portal link
- autofocus behavior

---

### Task 4 — Dashboard
Target:

```text
application/views/home/index.php
css/home.css
```

- [ ] Align Dashboard with shared design system
- [ ] Standardize cards
- [ ] Improve page spacing
- [ ] Improve action visibility
- [ ] Reduce duplicated page-specific styles where safe
- [ ] Check responsive behavior

**Preserve**
- PHP/data logic
- ACL checks
- chart IDs
- chart behavior
- refresh/actions/links

---

### Task 5 — Customer List
Target:

```text
application/views/customer/index.php
application/views/customer/panel_header.php
js/itelco/customer.js
```

Use Customer as the reference design for future CRUD/list pages.

- [ ] Add clear page header
- [ ] Make Add Customer a clear primary action
- [ ] Convert filter area into modern filter card
- [ ] Improve field spacing/alignment
- [ ] Modernize result table container
- [ ] Improve action buttons
- [ ] Improve responsive layout
- [ ] Modernize existing modal appearance

**Preserve existing selectors**, especially:

```text
#customer
#page_item_no
#txt_search
#sel_category
#sel_status
#sel_building
#select-installation
#sel_installation
#order_by
#order_type
#btFilter
#btClear
.table_rows_area
```

**Regression check**
- filtering
- clear filter
- installation conditional filter
- AJAX refresh
- sorting
- pagination
- modal open/close
- Proceed/Cancel

---

### Task 6 — Shared Component Cleanup
After Tasks 1–5:

- [ ] Review duplicated UI styles
- [ ] Move reusable rules into `ui-modern.css`
- [ ] Standardize button variants
- [ ] Standardize form controls
- [ ] Standardize tables
- [ ] Standardize badges/status
- [ ] Standardize alerts
- [ ] Standardize modal styling
- [ ] Remove only clearly obsolete styles introduced/replaced during Phase 1

Do not perform unrelated legacy CSS cleanup.

---

## Phase 2 — Common CRUD Pages

After Phase 1 is reviewed and approved:

- [ ] Apply established list/filter/table pattern to simple modules
- [ ] Apply established form patterns to simple create/edit pages
- [ ] Reuse existing shared UI classes
- [ ] Avoid creating new design patterns unless required

Potential modules should be handled one at a time.

---

## Phase 3 — Complex Forms

Modernize large workflow pages in smaller sections.

Examples:

- [ ] Customer Detail
- [ ] Registration
- [ ] Change Package
- [ ] Service Ticket
- [ ] Sales Order

Do not rewrite entire large files in one task.

---

## Phase 4 — Billing / Payment / Reports

- [ ] Billing
- [ ] Payment
- [ ] Manual Billing
- [ ] Adjustments
- [ ] E-Invoice
- [ ] Reports

Keep dense financial/data screens practical and information-focused.

---

## Phase 5 — Final Consistency Review

- [ ] Find remaining legacy-looking pages
- [ ] Fix inconsistent spacing/components
- [ ] Review responsive issues
- [ ] Review accessibility basics
- [ ] Review duplicated presentation CSS
- [ ] Confirm main workflows remain functional

---

## Future — Bootstrap 5 Evaluation

Do not start during current UI modernization.

Later evaluate:

- Bootstrap component usage
- Ace dependency
- jQuery dependency
- modal/dropdown compatibility
- plugin compatibility
- migration effort and regression risk

Treat this as a separate project.

---

## Codex Task Rule

For each Codex session:

1. Work on only one unchecked task or clearly defined sub-task.
2. Read only relevant files.
3. Preserve business logic and functional selectors.
4. Avoid unrelated refactoring.
5. Report:
   - files changed
   - what was improved
   - regression-sensitive areas
   - manual checks required
6. Do not commit unless explicitly requested.
