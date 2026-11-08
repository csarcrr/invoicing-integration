---
name: reflect
description: "Turn a mistake from this session into the smallest durable fix: a rule, agent prompt, skill, or memory edit, applied only after the user approves. Use when the user corrects Claude, rejects an output, says 'reflect', or at the end of a session where something went wrong, including a builder subagent report that missed the brief."
---

# Reflect

Find what went wrong in this session, why, and the smallest change that stops it from happening again. Propose the change and wait. Never edit anything before the user approves.

## 1. Collect the mistakes

Scan the session for:

- User corrections, rejections, or repeated instructions.
- Outputs that broke a rule in `CLAUDE.md`, `.claude/rules/*.md`, `~/.claude/CLAUDE.md`, or memory.
- `builder` reports with `not met` items, or builder output the main session had to redo.
- Wrong assumptions about a dependency, provider API, or repo convention.

If nothing went wrong, say so in one line and stop.

## 2. Find the root cause

For each mistake, name the cause in one sentence:

- **Missing rule**: no instruction covered it.
- **Unclear rule**: an instruction exists but allows the wrong reading.
- **Conflicting rules**: two instructions disagree, and the wrong one won.
- **Ignored rule**: the instruction was clear and was not followed. Prefer a stronger placement over a new rule.
- **Bad brief**: the main session's brief to `builder` was missing a file, constraint, or `Done when:` item.

## 3. Choose where the fix goes

| Cause | Target |
|---|---|
| Project convention for code, tests, or docs | `.claude/rules/<topic>.md` (edit an existing file before creating one) |
| Builder got it wrong | `.claude/agents/builder.md` |
| Reviewer missed it | `.claude/agents/code-reviewer.md` |
| Brief was incomplete | this skill or the orchestration notes in `CLAUDE.md` |
| User preference or one-off ruling with context | auto-memory file plus its `MEMORY.md` index line |

Before proposing, read the target file and check that no existing rule or memory already covers the fix. Update the existing entry rather than adding a duplicate. Delete entries that turned out wrong.

## 4. Propose

For each fix, show:

```
Mistake: <what happened, one line>
Cause: <category>: <one sentence>
Target: <path>
Change:
<exact lines to add, replace, or remove>
```

Keep each change to the fewest lines that prevent the mistake. Write it in English, imperative mood, with no examples unless the rule is unreadable without one.

## 5. Apply after approval

Apply only the fixes the user approves, exactly as approved. Do not commit.
