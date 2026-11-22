# Claude Code ToolBox — MCP & Skills awareness

_Generated: 2026-11-22T05:06:05.047Z_

## How to use this report

- **Saved copy:** This file is **`.claude/cloude-code-toolbox-mcp-skills-awareness.md`** — refreshed whenever the toolbox runs an MCP & Skills scan (including on workspace open when auto-scan is enabled). It is meant for **Claude Code workspace context** together with `CLAUDE.md` (which gets a shorter replaceable summary when auto-merge is on).
- **MCP:** Lists **configured** servers from Claude Code config (`~/.claude.json` for user scope, `.mcp.json` for project scope). Use `/mcp` in the Claude Code panel to connect servers for your session.
- **Skills:** **On-disk** folders with `SKILL.md`. Claude Code does not auto-load them; attach `SKILL.md` or paths in chat when useful.
- **Task routing:** When the user’s request matches a server’s purpose (e.g. Confluence → Confluence/Atlassian MCP), prefer that **server id** from the tables below.

---

## MCP — workspace

Workspace `mcp.json` _(folder: SRMS)_

- **d:\SRMS\.mcp.json** — _File exists — servers defined_

| Server id | Kind | Detail |
|-----------|------|--------|
| laravel-boost | stdio | php artisan boost:mcp |

## MCP — user profile

- **C:\Users\STVNPRDGY\.claude.json** — _File exists — no servers defined_

_No active user-scoped servers in mcp.json._

## Skills (local `SKILL.md` folders)

### Project-scoped

- **deploying-to-cloud** — `d:\SRMS\.github\skills\deploying-to-cloud`
  - Deploys and manages Laravel applications on Laravel Cloud using the `cloud` CLI. Use when the user wants to deploy an app, ship to Cloud, create or manage applications, environments, databases, caches, object storage, qu

- **infer-conventions** — `d:\SRMS\.github\skills\infer-conventions`
  - Use this skill to analyze how a Laravel application is actually written and record its conventions as shared rules. Trigger when the user wants to detect, infer, document, or standardize project conventions or coding sty

- **laravel-best-practices** — `d:\SRMS\.github\skills\laravel-best-practices`
  - Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and 

- **tailwindcss-development** — `d:\SRMS\.github\skills\tailwindcss-development`
  - Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixe

- **testing-best-practices** — `d:\SRMS\.github\skills\testing-best-practices`
  - Laravel test design and review. Use when selecting coverage, naming or structuring tests, choosing assertions or test data, isolating dependencies, testing HTTP or security boundaries, improving suite performance, or rev

- **deploying-to-cloud** — `d:\SRMS\.claude\skills\deploying-to-cloud`
  - Deploys and manages Laravel applications on Laravel Cloud using the `cloud` CLI. Use when the user wants to deploy an app, ship to Cloud, create or manage applications, environments, databases, caches, object storage, qu

- **infer-conventions** — `d:\SRMS\.claude\skills\infer-conventions`
  - Use this skill to analyze how a Laravel application is actually written and record its conventions as shared rules. Trigger when the user wants to detect, infer, document, or standardize project conventions or coding sty

- **laravel-best-practices** — `d:\SRMS\.claude\skills\laravel-best-practices`
  - Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and 

- **tailwindcss-development** — `d:\SRMS\.claude\skills\tailwindcss-development`
  - Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixe

- **testing-best-practices** — `d:\SRMS\.claude\skills\testing-best-practices`
  - Laravel test design and review. Use when selecting coverage, naming or structuring tests, choosing assertions or test data, isolating dependencies, testing HTTP or security boundaries, improving suite performance, or rev

- **bang-motion** — `d:\SRMS\.agents\skills\bang-motion`
  - Membangun motion graphic sinematik di browser (promo video, opener, intro, bumper, kinetic typography, explainer/video penjelasan bergambar 16:9 atau 9:16) memakai HTML + CSS + GSAP (+ Three.js bila perlu), dengan hasil 

- **chrome-extensions** — `d:\SRMS\.agents\skills\chrome-extensions`
  - >

- **deploying-to-cloud** — `d:\SRMS\.agents\skills\deploying-to-cloud`
  - Deploys and manages Laravel applications on Laravel Cloud using the `cloud` CLI. Use when the user wants to deploy an app, ship to Cloud, create or manage applications, environments, databases, caches, object storage, qu

- **infer-conventions** — `d:\SRMS\.agents\skills\infer-conventions`
  - Use this skill to analyze how a Laravel application is actually written and record its conventions as shared rules. Trigger when the user wants to detect, infer, document, or standardize project conventions or coding sty

- **laravel-best-practices** — `d:\SRMS\.agents\skills\laravel-best-practices`
  - Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and 

- **million-dollar-web-design** — `d:\SRMS\.agents\skills\million-dollar-web-design`
  - Design and engineer $1M-caliber websites and web applications with elite art direction, Linear/Stripe/Apple-grade polish, stratified elevation tokens, spring micro-interactions, high-converting SaaS landing architecture,

- **modern-frontend-craft** — `d:\SRMS\.agents\skills\modern-frontend-craft`
  - Implement robust, production-grade frontend code with complete states (skeleton loading, empty states, inline error recovery), WCAG 2.1 AA accessibility, keyboard navigation, zero layout shift (CLS), and clean component 

- **modern-web-guidance** — `d:\SRMS\.agents\skills\modern-web-guidance`
  - |

- **tailwindcss-development** — `d:\SRMS\.agents\skills\tailwindcss-development`
  - Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixe

- **testing-best-practices** — `d:\SRMS\.agents\skills\testing-best-practices`
  - Laravel test design and review. Use when selecting coverage, naming or structuring tests, choosing assertions or test data, isolating dependencies, testing HTTP or security boundaries, improving suite performance, or rev

- **ui-design-brain** — `d:\SRMS\.agents\skills\ui-design-brain`
  - Generate production-grade UI using real component patterns and best practices from 60+ documented interface components. Use when the user asks to build web interfaces, pages, dashboards, forms, navigation, or any UI — en

- **ui-ux-pro-max** — `d:\SRMS\.agents\skills\ui-ux-pro-max`
  - UI/UX design intelligence for web, mobile, and desktop. This skill should be used when designing, building, reviewing, or fixing interfaces, including pages, components, design systems, accessibility, interaction, respon

- **deploying-to-cloud** — `d:\SRMS\.cursor\skills\deploying-to-cloud`
  - Deploys and manages Laravel applications on Laravel Cloud using the `cloud` CLI. Use when the user wants to deploy an app, ship to Cloud, create or manage applications, environments, databases, caches, object storage, qu

- **infer-conventions** — `d:\SRMS\.cursor\skills\infer-conventions`
  - Use this skill to analyze how a Laravel application is actually written and record its conventions as shared rules. Trigger when the user wants to detect, infer, document, or standardize project conventions or coding sty

- **laravel-best-practices** — `d:\SRMS\.cursor\skills\laravel-best-practices`
  - Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and 

- **tailwindcss-development** — `d:\SRMS\.cursor\skills\tailwindcss-development`
  - Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixe

- **testing-best-practices** — `d:\SRMS\.cursor\skills\testing-best-practices`
  - Laravel test design and review. Use when selecting coverage, naming or structuring tests, choosing assertions or test data, isolating dependencies, testing HTTP or security boundaries, improving suite performance, or rev

### User-scoped

- **bang-motion** — `C:\Users\STVNPRDGY\.agents\skills\bang-motion`
  - Membangun motion graphic sinematik di browser (promo video, opener, intro, bumper, kinetic typography, explainer/video penjelasan bergambar 16:9 atau 9:16) memakai HTML + CSS + GSAP (+ Three.js bila perlu), dengan hasil 

- **chrome-extensions** — `C:\Users\STVNPRDGY\.agents\skills\chrome-extensions`
  - >

- **modern-web-guidance** — `C:\Users\STVNPRDGY\.agents\skills\modern-web-guidance`
  - |

- **million-dollar-web-design** — `C:\Users\STVNPRDGY\.cursor\skills\million-dollar-web-design`
  - Design and engineer $1M-caliber websites and web applications with elite art direction, Linear/Stripe/Apple-grade polish, stratified elevation tokens, spring micro-interactions, high-converting SaaS landing architecture,

- **modern-frontend-craft** — `C:\Users\STVNPRDGY\.cursor\skills\modern-frontend-craft`
  - Implement robust, production-grade frontend code with complete states (skeleton loading, empty states, inline error recovery), WCAG 2.1 AA accessibility, keyboard navigation, zero layout shift (CLS), and clean component 

- **ui-design-brain** — `C:\Users\STVNPRDGY\.cursor\skills\ui-design-brain`
  - Generate production-grade UI using real component patterns and best practices from 60+ documented interface components. Use when the user asks to build web interfaces, pages, dashboards, forms, navigation, or any UI — en

---

## Suggested next steps

- **MCP:** Use this extension’s hub **MCP** tab, or `claude mcp list` in the terminal. In Claude Code, use `/mcp` to connect servers for the session.
- **Edit config:** Open `~/.claude.json` (user MCP) or `<workspace>/.mcp.json` (project MCP) via the extension commands.
- **Refresh this report:** run **Intelligence — scan MCP & Skills awareness** again after changing MCP config or adding skills.

_Report from Claude Code ToolBox extension._
