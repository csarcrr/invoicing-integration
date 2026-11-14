# Unify Agent Instructions

Goal: any coding agent (Codex, Cursor, Copilot, Gemini, Windsurf, Zed, Jules, Amp, Junie...) gets the same project rules Claude Code gets, from one source of truth. No tool-specific files (`.cursor/`, `.github/instructions/`, `.windsurf/`, `.gemini/`), no agent or skill ports.

## Inventory

| Source | Lines | Loaded by | Portable |
|---|---|---|---|
| `CLAUDE.md` | 72 | Claude only | Yes: architecture, provider docs, domain decisions, namespaces |
| `AGENTS.md` | 133 | Every other agent | Stale (see Findings) |
| `.claude/rules/` always-on (10 files) | ~60 | Claude only | Yes, except `orchestration.md` |
| `.claude/rules/` path-scoped (5 files) | ~95 | Claude only, lazily by path | Yes |
| `.claude/agents/` (4 files) | — | Claude only | Out of scope |
| `.claude/skills/` (graft, reflect) | — | Claude only | Out of scope |
| `.claude/settings.json`, `helpers/*.cjs` | — | Claude hooks + statusline | No |
| `.claude/agent-memory/`, `.claude/plans/` | — | Claude only | No (`agent-memory` is gitignored) |
| `.mcp.json`, `graft/`, graft skill/hooks | — | Claude | Out of scope: graft is being removed separately |

Always-on rules: `dependencies`, `docs`, `no-verification-commands`, `orchestration`, `provider-rules`, `simplicity-first`, `surgical-changes`, `task-completion`, `tdd`, `think-before-coding`.

Path-scoped rules:

| File | Applies to |
|---|---|
| `php-code.md` | `**/*.php` |
| `solid.md` | `src/**/*.php` |
| `dtos.md` | `src/Data/**` |
| `provider-operations.md` | `src/Provider/**`, `src/Contracts/**`, `src/Actions/**`, `src/Facades/**` |
| `testing.md` | `tests/**` |

## Findings

### 1. Other agents see none of the current rules

Everything in `.claude/rules/` and most of `CLAUDE.md` is invisible to non-Claude agents. They get only `AGENTS.md`, which has none of: simplicity first, surgical changes, TDD, think before coding, provider rules, docs sync, dependency source reading, no verification commands, task completion, provider operation shape, current testing conventions, domain decisions, provider docs location, Vendus v1.1 vs v1.2 note.

### 2. `AGENTS.md` is stale and contradicts the rules

| `AGENTS.md` says | Reality |
|---|---|
| "currently supporting Cegid Vendus" | Moloni is also implemented (`src/Provider/Moloni/`) |
| Laravel 11.x and 12.x | `illuminate/contracts: ^11.0\|\|^12.0\|\|^13.0` |
| Pest `^3.0`, Testbench `^10.3.0\|\|^9.3.0` | Pest `^3.0\|\|^4.0`, Testbench `^11.0\|\|^10.3.0\|\|^9.3.0` |
| "Tests mirror `src/` structure under `tests/Unit/`" | Tests are organised by operation (`.claude/rules/testing.md`) |
| Run `composer test`, `composer analyse`, `composer format` "before committing" | `no-verification-commands.md` forbids the agent running them |
| `composer test-coverage` | Script does not exist in `composer.json` |
| Facade `CsarCrr\InvoicingIntegration\Facades\InvoicingIntegration`, alias `InvoicingIntegration` | No such facade; facades are per resource (`src/Facades/Invoice.php`, `Client.php`, ...) |
| Key/dev dependency lists | Duplicates `composer.json`, drifts |

### 3. `CLAUDE.md` has stale references too

- Contract `CreateClient` does not exist; the convention is `Should<Operation><Resource>` (`src/Contracts/IntegrationProvider/<Resource>/`).
- Namespace table lists `database/factories/` and `workbench/app/`; neither directory exists. Confirm against `composer.json` autoload before keeping them.
- Enums list omits `Tax/` subfolder (`ItemTax`, `TaxExemptionReason` live in `src/Enums/Tax/`) and newer enums (`Unit`, `ItemType`, `DueDateTerm`, `OutputFormat`, `Property`).

### 4. Duplication between `CLAUDE.md` and `AGENTS.md`

Architecture, request flow, key directories, adding a provider, HTTP layer and namespaces are copied in both files and have already drifted (finding 2).

### 5. Rules that live only on one machine

Some rules other agents need are not in the repo at all:

- `~/.claude/CLAUDE.md` (user-global): no comments (type-only docblocks allowed), PSR-12, `match` over `switch`, `readonly`, enums over constants, `===`, early returns, English everywhere, PT-PT for `pt` files, imperative commit messages.
- Claude auto-memory (`~/.claude/projects/.../memory/`): short one-sentence commit messages, never commit unless asked, no checkboxes in PR descriptions, no caching suggestions, read source before proposing a refactor, report an already-existing feature instead of changing it, Moloni is unreleased so Moloni-only changes are not breaking.

Memory facts already covered by repo rules (no action): no `const` properties, `is_null()`, tests by operation, unit tests never fake HTTP, extend provider base class, agnostic tests, unsupported fields dropped not thrown, one tax per item, Vendus openapi v1.2 vs v1.1.

### 6. Claude-only content that must stay out of `AGENTS.md`

- `orchestration.md` (delegation to the `builder` subagent, `reflect` skill).
- Hooks, statusline, agent memory, plans.

### 7. Existing Claude files that reference paths this plan moves

- `.claude/agents/builder.md:24` lists `.claude/rules/*.md` files to read.
- `.claude/skills/reflect/SKILL.md:15,35,38` names `CLAUDE.md` and `.claude/rules/<topic>.md` as where rules go.
- `.claude/agents/{code-reviewer,docs-writer,pr-description-writer}.md` say "conventions in CLAUDE.md".

## Decision: source of truth

`AGENTS.md` holds every portable rule. `CLAUDE.md` imports it and adds only Claude-only content.

Path-scoped rules: keep lazy loading for Claude without copies and without a sync script.

- Move the 5 path-scoped files to a neutral folder: `.agents/rules/<name>.md`, keeping their `paths:` frontmatter.
- Replace each `.claude/rules/<name>.md` with a symlink to `../../.agents/rules/<name>.md`. Claude Code keeps loading them by path.
- `AGENTS.md` gets an index table: "before editing files matching X, read `.agents/rules/Y.md`". Other agents follow it on demand.

Fallback if symlinks in `.claude/rules/` turn out unsupported (verify in step 1): inline the 5 files as "When editing ..." sections in `AGENTS.md` and delete them from `.claude/rules/`. Cost: ~95 extra lines always loaded for Claude.

Always-on rules move inline into `AGENTS.md`; their `.claude/rules/` files are deleted. No sync script: with one file per rule there is nothing to sync.

## Target layout

```
AGENTS.md                      canonical, every agent
CLAUDE.md                      @AGENTS.md + Claude-only section
.agents/rules/
  php-code.md
  solid.md
  dtos.md
  provider-operations.md
  testing.md
.claude/rules/
  orchestration.md             Claude-only, unchanged
  php-code.md -> ../../.agents/rules/php-code.md
  solid.md -> ../../.agents/rules/solid.md
  dtos.md -> ../../.agents/rules/dtos.md
  provider-operations.md -> ../../.agents/rules/provider-operations.md
  testing.md -> ../../.agents/rules/testing.md
```

## `AGENTS.md` outline

1. Project: one paragraph (package name, purpose, providers: Cegid Vendus, Moloni; Moloni unreleased).
2. Stack: PHP 8.2+, Laravel 11–13, Spatie Laravel Data, Pest, PHPStan level 7, Pint. Versions point at `composer.json`, not copied.
3. Commands: list `composer test`, `composer analyse`, `composer format`, `composer complete` for reference, followed by the no-verification rule: the agent never runs them; the user does.
4. Architecture: request flow, key directories, adding a provider, HTTP layer, namespaces, service provider (from `CLAUDE.md`, stale items fixed per finding 3).
5. Provider API docs: `providers-docs/` table and Vendus v1.1 vs v1.2 note.
6. Domain decisions: one tax per item, Moloni EXEMPT mapping, tax-only update keeps gross.
7. Working rules (from always-on files): think before coding, simplicity first, surgical changes, TDD, provider rules are the provider's job, dependencies, docs sync, task completion.
8. Code style (promoted per decision A): no comments (type-only docblocks allowed), PSR-12, strict types, `match` over `switch`, `readonly`, enums over constants, `===`, early returns, English everywhere, PT-PT for `pt` files.
9. Git and PRs (promoted per decision A): one-sentence imperative commit messages (what + why, no body), never commit unless asked, no checkboxes in PR descriptions.
10. File-scoped rules index: table of glob → `.agents/rules/<name>.md`.
11. Documentation: keep the existing "never give legal advice in docs" rule.

## Plan

1. Verify symlink support: check Claude Code docs that `.claude/rules/` follows symlinks. Pick the main design or the fallback.
2. Verify facts before writing: `composer.json` autoload namespaces, contract naming, enum list, facade list.
3. Create `.agents/rules/` with the 5 path-scoped files (moved via `git mv`, content unchanged).
4. Replace the 5 files in `.claude/rules/` with relative symlinks.
5. Rewrite `AGENTS.md` per the outline. Drop the stale content listed in finding 2.
6. Rewrite `CLAUDE.md` to:
   ```
   @AGENTS.md

   ## Claude Code
   - Orchestration rules: `.claude/rules/orchestration.md`
   ```
   Everything else moves to `AGENTS.md`.
7. Delete the 9 portable always-on files from `.claude/rules/` (all except `orchestration.md`).
8. Update Claude files that point at moved paths (finding 7):
   - `builder.md:24`: read `AGENTS.md` and the matching `.agents/rules/*.md`.
   - `reflect/SKILL.md`: portable conventions go to `AGENTS.md` or `.agents/rules/<topic>.md`; Claude-only ones to `.claude/rules/`.
   - "CLAUDE.md" mentions in the other three agents: change to `AGENTS.md`.
9. Memory cleanup: delete the promoted memories (`feedback_short_commit_messages.md`, `feedback_no_commits_without_request.md`, `feedback_pr_description_no_checkboxes.md`, `feedback_no_comments_beats_style_match.md`) and the memories already covered by repo rules (finding 5), then update `MEMORY.md`. `project_moloni_unreleased.md` moves into `AGENTS.md` section 1 and is deleted too.
10. Hand back for review. The user checks that a non-Claude agent (e.g. Codex) picks up `AGENTS.md` and follows the index to `.agents/rules/`.

Docs under `docs/` need no change: this touches agent instructions only.

## Resolved decisions

A. Promote finding 5 rules into `AGENTS.md`: yes. Code style and git/PR rules go into `AGENTS.md` sections 8 and 9. Personal workflow memories (no caching suggestions, verify before proposing, report existing feature) stay in Claude memory.

B. Graft: not mentioned in `AGENTS.md`. Graft is being removed from the repo in a separate change (`graft/`, `.claude/skills/graft/`, `.claude/helpers/graft-*.cjs`, graft hooks and statusline in `.claude/settings.json`, `.mcp.json`). This plan does not touch those files.

C. Path-scoped rules folder: `.agents/rules/`.
