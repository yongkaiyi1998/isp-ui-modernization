# UI Design System

## Visual Direction

The system should look like a modern professional admin/business application.

Style direction:

- clean
- light
- practical
- readable
- compact but not cramped
- consistent
- suitable for daily operational use

Avoid:

- heavy gradients
- glassmorphism
- excessive shadows
- oversized cards
- excessive animation
- decorative elements that reduce usability

---

## Color Direction

Use a neutral light interface.

Recommended roles:

- Page background: very light gray
- Surface/card: white
- Primary action: blue
- Success: green
- Warning: amber/orange
- Danger: red
- Text: dark neutral
- Secondary text: medium gray
- Border: soft gray

Use colors consistently by meaning.

Do not use color as the only way to communicate status.

---

## Typography

Use the existing system font stack.

Target:

- normal text: 14–15px
- labels: 13–14px
- page title: 22–26px
- section title: 16–18px

Avoid very small text such as 10px labels unless required by dense data areas.

Use font weight and spacing to create hierarchy instead of excessive font sizes.

---

## Spacing

Use consistent spacing throughout the system.

Preferred spacing scale:

```text
4px
8px
12px
16px
24px
32px
```

Typical rules:

- page padding: 20–24px desktop
- card padding: 16–20px
- form field gap: 12–16px
- section gap: 20–24px
- button gap: 8px

Do not add excessive whitespace to data-heavy pages.

---

## Cards / Panels

Legacy Bootstrap panels may remain structurally, but their appearance should be modernized.

Target style:

- white background
- subtle border
- small border radius
- light shadow only when useful
- clear header/content separation
- consistent padding

Avoid strong colored panel headers unless the color communicates status.

---

## Page Header

Each main page should preferably have:

```text
Page Title                  Primary Action
Optional description        Secondary Actions
```

Example:

```text
Customers                         + Add Customer
Manage customer accounts
```

Primary actions should be easy to find.

Avoid icon-only primary actions where visible text is practical.

---

## Buttons

Use clear hierarchy.

### Primary
For:

- Add
- Save
- Submit
- Confirm
- Proceed

### Secondary
For:

- Edit
- Filter
- View
- Refresh

### Neutral
For:

- Clear
- Cancel
- Back

### Danger
For:

- Delete
- Void
- Disconnect
- destructive actions

Buttons should have:

- consistent height
- reasonable padding
- clear hover state
- visible focus state

Do not create many slightly different button styles.

---

## Forms

Normal form controls should feel more modern than the current legacy controls.

Target:

- height around 38–40px
- readable text
- clear label
- consistent border
- visible focus state
- sufficient spacing
- obvious disabled/read-only state

Textarea height should follow content needs.

Do not rename existing:

- IDs
- names
- selectors
- hidden fields

Legacy `.input-group` and addon structures may remain if changing them risks compatibility.

---

## Filters

Filter areas should use a dedicated card/section.

Preferred layout:

```text
[ Search ] [ Status ] [ Category ] [ Building ] [ Filter ] [ Clear ]
```

On smaller screens, controls should wrap or stack.

Filter sections should visually differ from result tables without using strong background colors.

---

## Tables

Tables should remain information-dense but easy to scan.

Use:

- clear header background
- readable header weight
- comfortable row height
- subtle borders
- row hover state
- consistent action column
- responsive wrapper when necessary

Avoid excessive grid borders.

Status values may use badges where appropriate.

Do not alter server-side filtering, sorting or pagination behavior.

---

## Badges / Status

Use consistent semantic badge styles:

```text
Active / Success     → green
Pending / Warning    → amber
Inactive / Neutral   → gray
Error / Failed       → red
Info                 → blue
```

Keep badge text short and readable.

Do not invent new status meanings.

---

## Alerts

Standardize:

- success
- warning
- error
- information

Alerts should be noticeable without dominating the page.

Existing Gritter / flash-message behavior should remain functional.

---

## Modals

Keep Bootstrap 3 modal behavior.

Modernize appearance only.

Use:

- cleaner header
- clear title
- consistent padding
- readable body spacing
- clear primary/secondary footer actions
- responsive width

Do not convert modal markup to Bootstrap 5 syntax.

---

## Navbar

Modernize the top navigation while preserving existing behavior.

Improve:

- spacing
- alignment
- user dropdown
- logo area
- toggle button
- visual separation from content

Keep the navbar relatively compact.

---

## Sidebar

The sidebar should remain efficient for many menu items.

Improve:

- readable menu spacing
- active item visibility
- submenu hierarchy
- icon alignment
- hover states
- collapsed state

Do not make the sidebar excessively wide.

Do not remove or regroup menu items unless separately requested.

Existing ACL conditions must remain unchanged.

---

## Responsive Rules

Desktop remains the primary working environment, but modernized screens should degrade cleanly.

### Desktop
Use available width efficiently.

### Tablet
Allow filters/forms to wrap.

### Mobile
Stack form controls and page actions where needed.

Tables may use horizontal scrolling when the content cannot reasonably collapse.

Important actions must remain accessible.

---

## Accessibility Baseline

New UI changes should include, where practical:

- visible keyboard focus
- readable contrast
- clear labels
- usable button sizes
- `title` or `aria-label` for icon-only actions
- distinguishable disabled state

Do not rely only on icons or colors for important meaning.

---

## Implementation Rule

Prefer reusable classes in:

```text
css/ui-modern.css
```

instead of adding new page-specific inline styles.

Page-specific CSS is acceptable only when the component is genuinely unique.

Before changing markup, confirm that existing JavaScript does not depend on its IDs, classes or structure.

Consistency is more important than making each page visually unique.