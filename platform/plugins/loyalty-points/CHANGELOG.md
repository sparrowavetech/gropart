# Changelog

## 1.0.16 - 2026-07-02

- fix: the redeem-points boxes on the landing page checkout no longer turn unreadable on hover on dark themes; the box now keeps a solid light background on hover instead of dropping to a near-transparent tint that let the dark page show through
- fix: the checkout/funnel "Look up" button hover is now legible on every theme; it was filling with the light accent color under white text (invisible on light themes) and gave no visible change on dark themes, so it now keeps its accent-colored label with a neutral hover wash
- chore: bumped the checkout asset version so browsers refetch the updated styles

## 1.0.15 - 2026-07-01

- fix: the product-info box border color set in Loyalty settings now applies on the Default, Minimal, Compact and Banner styles (it was being overridden by Bootstrap's `.border` utility)
- fix: the checkout/funnel "Look up" button is now readable on light themes; it was showing white text on the light accent color, so it now uses an outline style (accent-colored text and border) that stays legible on both light and dark themes
- chore: bumped the checkout asset version so browsers refetch the updated styles

## 1.0.14 - 2026-06-30

- fix: product-info box background/text color settings now correctly override Bootstrap `.bg-success`, `.bg-primary`, `div`, `.text-primary`, `.text-success`, and `.text-info` elements on all box styles (Default, Minimal, Compact, Card, Banner)
- fix: "Look up" button in checkout loyalty suggestion now uses theme `--color-primary` variable (falls back to `--bs-primary`) so it respects the active Nest theme color instead of always showing Bootstrap blue
