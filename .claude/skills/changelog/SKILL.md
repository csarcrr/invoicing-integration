---
name: changelog
description: "Maintain CHANGELOG.md in Keep a Changelog 1.1.0 format. `/changelog` adds the user-facing changes committed since the changelog was last updated to [Unreleased]; `/changelog release <version>` turns [Unreleased] into a dated version. Use when the user asks to update the changelog, add release notes, or prepare a release."
argument-hint: "[release <version>]"
---

# Changelog

`CHANGELOG.md` at the repo root is the only changelog. It follows [Keep a Changelog 1.1.0](https://keepachangelog.com/en/1.1.0/): newest version first, `[Unreleased]` on top, and compare links at the bottom.

Never commit, tag or push. The user commits when they ask and creates tags on GitHub. Stop after editing `CHANGELOG.md` and show the diff.

## `/changelog`: update [Unreleased]

1. Find the starting point: the most recent of the latest tag (`git describe --tags --abbrev=0`) and the last commit that touched `CHANGELOG.md` (`git log -1 --format=%H -- CHANGELOG.md`).
2. List the commits since then: `git log --reverse --format='%h %s' <start>..HEAD`.
3. Classify each commit by its prefix:
   - `feat:` goes to **Added**, or to **Changed** when it changes an existing operation.
   - `fix:` goes to **Fixed**.
   - `refactor!:`, `feat!:`, `fix!:` or a `BREAKING CHANGE` footer goes to **Changed** or **Removed**, with a `**Breaking:**` prefix.
   - A removed public class, enum, method or DTO field goes to **Removed**.
   - Skip `test:`, `chore:`, `wip`, `refactor:` without `!`, and `docs:` commits that touch only internal files (`.claude/`, `TODO*.md`, `AGENTS.md`, `CLAUDE.md`).
4. Read the diff of every commit you keep (`git show --stat <hash>`, then the changed `src/` files) before writing its line. Write for a developer who uses the package: name the facade method, DTO field or config key, and the providers it affects. Never copy the commit message.
5. One line per change, in the style of the existing entries. Within each section, newest change first. Section order: Added, Changed, Deprecated, Removed, Fixed, Security. Omit empty sections.
6. Skip a change that [Unreleased] already describes. Merge a fix into its feature's line when both are unreleased; a fix to something not yet released is not a Fixed entry.
7. A change to how Vendus or Moloni behaves is breaking when existing code must change; mark it `**Breaking:**`.
8. Show the diff of `CHANGELOG.md` and list the commits you skipped, with the reason for each.

## `/changelog release <version>`: cut a version

1. Refuse when [Unreleased] is empty, or when `<version>` is not greater than the latest version in the file.
2. With no `<version>`, propose one from [Unreleased] and ask before writing it:
   - Any `**Breaking:**` entry or a Removed section means a minor bump while the major version is `0`, otherwise a major bump.
   - Any Added entry means a minor bump.
   - Only Fixed entries mean a patch bump.
3. Rename `## [Unreleased]` to `## [<version>] - <YYYY-MM-DD>` (today's date) and add an empty `## [Unreleased]` above it.
4. Update the links at the bottom:
   - `[Unreleased]: https://github.com/csarcrr/invoicing-integration/compare/v<version>...HEAD`
   - `[<version>]: https://github.com/csarcrr/invoicing-integration/compare/v<previous>...v<version>`, where `<previous>` is the previous version in the file, or the latest tag when the file has none.
5. Show the diff and remind the user to create the `v<version>` tag on GitHub.
