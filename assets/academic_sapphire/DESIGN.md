---
name: Academic Sapphire
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#43474f'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#747780'
  outline-variant: '#c3c6d0'
  surface-tint: '#3f5f8e'
  primary: '#002851'
  on-primary: '#ffffff'
  primary-container: '#1b3e6b'
  on-primary-container: '#8aaadd'
  inverse-primary: '#a8c8fd'
  secondary: '#1960a3'
  on-secondary: '#ffffff'
  secondary-container: '#7db6ff'
  on-secondary-container: '#00477f'
  tertiary: '#6d5e00'
  on-tertiary: '#ffffff'
  tertiary-container: '#c4aa01'
  on-tertiary-container: '#4a3f00'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d5e3ff'
  primary-fixed-dim: '#a8c8fd'
  on-primary-fixed: '#001b3b'
  on-primary-fixed-variant: '#264775'
  secondary-fixed: '#d3e4ff'
  secondary-fixed-dim: '#a2c9ff'
  on-secondary-fixed: '#001c38'
  on-secondary-fixed-variant: '#004881'
  tertiary-fixed: '#ffe24c'
  tertiary-fixed-dim: '#e2c62d'
  on-tertiary-fixed: '#211b00'
  on-tertiary-fixed-variant: '#524600'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 34px
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 22px
    fontWeight: '700'
    lineHeight: 28px
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Inter
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 22px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.25rem
  margin: 1.5rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style
The design system establishes a trustworthy, orderly, and encouraging academic administration and portal experience. Tailored for tutors, branch administrators, students, and parents, the interface communicates structured clarity, reliable academic tracking, and approachable institutional efficiency.

The design movement combines **Corporate Modern** with **Crisp Educational SaaS**. It utilizes high-legibility typographic hierarchies, structured white data cards set upon an ultra-light cool canvas, and a signature deep navy navigation column offset by vibrant royal blue active states and warm pastel metric indicators. The interface feels intentionally organized, clean, and free of visual clutter—directing focus toward academic performance, schedules, and operations.

## Colors
The color architecture relies on a crisp high-contrast relationship between deep structural navies and luminous academic highlights:

- **Primary (`#1b3e6b`):** Deep academic navy blue. Anchors the primary persistent navigation sidebar, mastheads, primary CTA buttons (`+ Tambah Siswa`, `Cetak`), and modal headers.
- **Secondary (`#2b6cb0`):** Royal active blue. Applied to active navigation state pills, interactive hover rings, link colors, and selected timeline date blocks.
- **Tertiary (`#fde047`):** Warm gold pastel. Powers key administrative summary tiles (e.g., Total Kelas) and brand marks.
- **Canvas & Neutral Surfaces:**
  - Background Canvas: `#f0f4f8` to `#f8fafc` (ultra-soft ice blue/slate wash).
  - Surface Card: `#ffffff` crisp white with subtle boundary borders.
  - Border Lines: `#e2e8f0` for table dividers and `#cbd5e1` for form inputs.
- **Status & Metric Accent Badges:**
  - Success / Active (`Lunas`, `Aktif`): Background `#d1fae5`, Text `#059669`.
  - Warning / Pending (`Belum Lunas`): Background `#fef3c7`, Text `#d97706`.
  - Danger / Deletion: Background `#fee2e2`, Text `#dc2626`.
- **Metric Tile Palette:**
  - Royal Blue Tile: `#2b6cb0` with `#ffffff` text.
  - Light Sky Tile: `#93c5fd` with `#1e3a8a` text.
  - Muted Gold Tile: `#fde047` with `#78350f` text.
  - Ice Neutral Tile: `#ffffff` or `#f1f5f9` with `#334155` text and dark blue icons.

## Typography
The system employs a dual-typeface strategy. **Plus Jakarta Sans** delivers warmth, modern authority, and crisp geometric legibility to major page headers, stats values, and module banners. **Inter** handles high-density administrative tabular data, complex form fields, breadcrumb links, and metadata.

Tabular figures (`tnum`) should be enabled across tables, monetary IDR figures, phone numbers, and dashboard counter blocks. Breadcrumbs (`Dashboard > Data Pengajar`) use `label-md` in muted slate (`#64748b`) with the active node colored in `#1b3e6b`.

## Layout & Spacing
The layout follows a responsive two-column application frame:
- **Left Navigation Drawer:** Fixed `240px` (or `260px`) persistent sidebar on desktop displays, retracting into an off-canvas drawer controlled by a hamburger toggle on screens below `1024px`.
- **Main Viewport Canvas:** Background filled with `#f0f4f8` to `#f8fafc`. Content nests within an expansive, unified card container or individual rounded panels with standard `margin: 1.5rem` on desktop and `1rem` on mobile.
- **Table Density & Rhythm:** Data rows maintain a strict `48px` to `56px` height with `0.75rem` vertical cell padding, allowing for rapid scanning without cramped layouts.
- **Form Controls:** Search inputs and filters run in unified flex rows with gap tokens set to `space-sm` (`0.5rem`) and `space-md` (`1rem`).

## Elevation & Depth
This design system avoids heavy shadows, favoring clean structural boundaries and soft perimeter tints:

- **Level 0 (Flat / Canvas):** Applied to the main application background `#f0f4f8`.
- **Level 1 (Card & Containers):** Pure white `#ffffff` surfaces bordered with `1px solid #e2e8f0` and an ultra-subtle diffuse drop shadow: `0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02)`.
- **Level 2 (Active Navigation & Dropdowns):** Subtle float `0 4px 6px -1px rgba(0, 0, 0, 0.07)` applied to open datepickers, profile menus, and active calendar date tiles.
- **Sidebar Integration:** The sidebar (`#1b3e6b`) rests flat against the viewport edge with no hard divider shadow, creating separation purely through deep navy tonal value contrast.

## Shapes
The visual identity is defined by friendly, modern, yet strictly professional contours:
- **Main Containers & Content Cards:** Use `rounded-xl` (`16px` / `1rem` border radius) to soften large dashboard modules and table frames.
- **Inputs, Buttons, and Selects:** Form controls feature `rounded-md` (`8px` / `0.5rem`) to ensure crisp alignment alongside table edges.
- **Status Chips & Pills:** Employ full pill geometry (`rounded-full` / `9999px`) for `Aktif`, `Lunas`, and `Belum Lunas` badges.
- **Calendar Day Tiles:** Use `rounded-lg` (`10px` to `12px`) rectangular blocks for weekly schedule pickers.

## Components

### Primary & Action Buttons
- **Primary Action (`+ Tambah`, `Cetak`):** Solid `#1b3e6b` background, `#ffffff` typography, `8px` radius, `0.5rem 1.25rem` padding, subtle hover shift to `#234d82`.
- **Secondary / Outline (`Export PDF`):** Border `1px solid #1b3e6b`, transparent background, `#1b3e6b` text.
- **Table Action Icons:** Ghost circular or square icon buttons (`28px x 28px`) with subtle slate hover tinting. Edit icon styled in royal blue (`#2563eb`), delete/trash icon in soft red (`#dc2626`), and inspect icon in muted teal/slate.

### Navigation Sidebar
- **Brand Block:** Centered or left-aligned white book icon with gold accent, followed by "Bimbel Tunas Cendekia" title.
- **Nav Links:** Transparent background, muted slate-white text (`#94a3b8`), `8px` radius.
- **Nav Active State:** Prominent royal blue pill (`#2b6cb0` or `#3182ce`) with bold white text and pure white icon highlight.

### Data Tables
- **Header:** Light gray/blue wash (`#f8fafc`) with bold `12px` slate-grey uppercase/titlecase labels (`#475569`).
- **Rows:** Alternating soft zebra or clean bottom border lines (`#f1f5f9`).
- **Pagination:** Centered or right-aligned segmented pill buttons with `#2b6cb0` active page highlight.

### Form Inputs & Filters
- **Search Field:** White background, light gray border (`#cbd5e1`), inset left search magnifying icon (`#94a3b8`), placeholder in `#94a3b8`.
- **Dropdown Filters:** Matching `38px` height with clean chevron indicator.

### Status Chips
- Height `24px`, horizontal padding `10px`, font size `12px`, font weight `500`, pill-shaped (`9999px`).
- `Aktif` / `Lunas`: `#d1fae5` background with `#059669` text.
- `Belum Lunas`: `#fef3c7` background with `#d97706` text.

### KPI Summary Cards
- Uniform heights with top-aligned icon, center-left headline value (`24px-28px bold`), and uppercase or clear subtitle label. Available in solid Royal Blue, Pastel Sky, Pastel Gold, and White.