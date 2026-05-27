---
name: Emerald Precision
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#3c4a42'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#6c7a71'
  outline-variant: '#bbcabf'
  surface-tint: '#006c49'
  primary: '#006c49'
  on-primary: '#ffffff'
  primary-container: '#10b981'
  on-primary-container: '#00422b'
  inverse-primary: '#4edea3'
  secondary: '#0058be'
  on-secondary: '#ffffff'
  secondary-container: '#2170e4'
  on-secondary-container: '#fefcff'
  tertiary: '#565e74'
  on-tertiary: '#ffffff'
  tertiary-container: '#9ba2bb'
  on-tertiary-container: '#31394d'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#6ffbbe'
  primary-fixed-dim: '#4edea3'
  on-primary-fixed: '#002113'
  on-primary-fixed-variant: '#005236'
  secondary-fixed: '#d8e2ff'
  secondary-fixed-dim: '#adc6ff'
  on-secondary-fixed: '#001a42'
  on-secondary-fixed-variant: '#004395'
  tertiary-fixed: '#dae2fd'
  tertiary-fixed-dim: '#bec6e0'
  on-tertiary-fixed: '#131b2e'
  on-tertiary-fixed-variant: '#3f465c'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  display-currency:
    fontFamily: Hanken Grotesk
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Hanken Grotesk
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 34px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Hanken Grotesk
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 30px
  headline-md:
    fontFamily: Hanken Grotesk
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-caps:
    fontFamily: Hanken Grotesk
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
  numeric-bold:
    fontFamily: Hanken Grotesk
    fontSize: 18px
    fontWeight: '700'
    lineHeight: 24px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  container-padding: 20px
  gutter: 16px
  card-gap: 12px
  section-margin: 32px
---

## Brand & Style
The design system is engineered for a high-end mobile fintech experience, blending the trustworthiness of traditional finance with the agility of a modern startup. The aesthetic is defined as **Premium Minimalist with Glassmorphic accents**, prioritizing clarity, high-quality whitespace, and sophisticated depth.

The UI should feel "lightweight" yet grounded. It leverages smooth Gaussian blurs and subtle gradients to suggest a multi-layered physical space, evoking an atmosphere of transparency and technological precision. Every interaction should feel intentional, using soft transitions and a disciplined color application to guide the user’s focus toward growth and financial health.

## Colors
The palette is anchored by **Emerald Green**, symbolizing growth, vitality, and financial prosperity. This is used for primary actions and "success" states. **Dark Slate** provides the grounding for typography, ensuring high legibility and a professional weight.

- **Primary (Emerald):** Used for CTA buttons, positive trends, and active navigation states.
- **Secondary (Subtle Blue):** Reserved for secondary utility actions, information callouts, and interactive charting elements.
- **Backgrounds:** A crisp **Off-white** (#F8FAFC) base allows card elements to pop using white fills and soft shadows.
- **Gradients:** Use linear gradients from Emerald (#10B981) to a slightly deeper Teal (#059669) for primary surfaces to add dimension.

## Typography
This design system utilizes **Hanken Grotesk** for all structural and high-impact elements. Its sharp, contemporary geometry is ideal for financial figures and headers. **Inter** is utilized for body copy to ensure maximum readability during long-form data consumption.

Financial figures are the hero of the interface. Use `display-currency` for main balances and `numeric-bold` for transaction lists. Always use tabular numbers (tnum) for alignment in lists. Maintain tight letter-spacing on larger headlines to reinforce the premium, editorial feel.

## Layout & Spacing
The layout follows a **fluid-to-fixed mobile logic**, optimized for a 390px-430px viewport width (standard high-end smartphones). 

- **Grid:** Use a 4-column layout for mobile with a 20px outer margin.
- **Rhythm:** An 8px base unit governs all padding and margins. 
- **Card Layout:** Elements within cards should use 16px internal padding. 
- **Verticality:** Group related items with 12px gaps, and separate distinct sections (e.g., "Accounts" vs "Recent Transactions") with 32px of vertical whitespace to maintain a clean, breathable atmosphere.

## Elevation & Depth
Elevation in this design system is achieved through **Tonal Stacking and Glassmorphism** rather than traditional heavy shadows.

- **Level 0 (Base):** Off-white background (#F8FAFC).
- **Level 1 (Cards):** Pure White (#FFFFFF) with a very soft, diffused shadow: `0px 10px 30px rgba(15, 23, 42, 0.04)`.
- **Level 2 (Glass Overlays):** Use a backdrop-filter blur (20px) and a semi-transparent white stroke (1px, 20% opacity) for floating elements like the bottom navigation and modals.
- **Interactive Depth:** On press, cards should subtly scale (0.98) and increase shadow density to simulate physical tactility.

## Shapes
The shape language is defined by **expansive, friendly radii**. 

Main containers and data cards must use `rounded-2xl` (1.5rem / 24px) to create a soft, high-end tech aesthetic. Smaller interactive components like buttons and input fields use `rounded-lg` (1rem / 16px). This contrast in rounding helps distinguish between structural "housing" and actionable "tools." Iconography should follow a consistent 2px stroke weight with rounded terminals.

## Components

### Bottom Navigation Bar
A glassmorphic "floating" bar positioned 16px from the bottom edge. Use a `backdrop-filter: blur(12px)` with a high-contrast Emerald Green icon for the active state. The bar should have a 1px white border to separate it from the content behind.

### Polished Data Cards
Cards are the primary content vessel. They feature a white background, 24px corner radius, and include internal "micro-headers" using the `label-caps` typography style. Data trends should be visualized with small, sparkline-style SVG charts.

### Floating Action Button (FAB)
The primary "Add Transaction" trigger. A perfect circle with an Emerald-to-Teal gradient. It should have a more pronounced shadow than cards to indicate it is on the highest elevation plane.

### Interactive Charts
Charts use Emerald Green for positive data and a soft Neutral Grey for historical/baseline data. Interaction points (tooltips) should be glassmorphic bubbles that appear above the touch point.

### Inputs & Selectors
Input fields are "ghost-styled": off-white backgrounds with no border until focused, at which point a 1.5px Emerald border appears. Labels are always visible in `label-caps` style above the field.

### Chips
Used for category filtering (e.g., "Food", "Rent"). These use a pill-shape (`rounded-full`) with a light blue-grey background and transition to a solid Emerald fill when active.