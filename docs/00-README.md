# START HERE - Read This First

This folder guides you (the AI tool) on how to work on the **School Management API** project (Laravel, API-first).

## Current project state

- Laravel is installed with API support.
- Laravel Breeze and Sanctum are installed and working.
- Do not reinstall them. Do not overwrite the existing auth setup.

## Read the files in this order

1. `AGENTS.md` (project root) - my working rules. Follow them in everything you do.
2. `docs/00-README.md` - this file.
3. `docs/school_api_ai_guide.md` - design review, table changes, and build phases.
4. `docs/school_management_api_erd.pdf` - the original ERD (only if it is in this folder).

Read all of them fully before you write any code.

## Then start with Step 0

Open `docs/school_api_ai_guide.md` and go to **Section 1: START HERE**.

Begin with **Step 0 - Inspect the existing project (read-only)**. Then continue with Step 1 and Step 2.

## How the two rule sets work together

- `AGENTS.md` sets how you work: build understanding first, keep changes small, test, verify, protect the system.
- `school_api_ai_guide.md` sets what to build and in which order.
- If they seem to conflict, follow the stricter one and ask me.

## Important

- Do not create or edit any code file until I approve your plan (end of Step 2).
- Build one phase at a time. After each phase, run the tests, then stop and explain.
- Do not build extra things I did not ask for.
- If you are not sure, ask me.
- Record decisions, rejected alternatives, and phase progress under `docs/changelog/<YYYY-MM>/<DD-topic>/`, as `AGENTS.md` requires. A fresh session must be able to resume from it.
