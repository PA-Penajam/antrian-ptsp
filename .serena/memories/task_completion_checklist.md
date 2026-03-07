# Task Completion Checklist

## Before Finalizing Any Change

1. **Run Pint formatter** on changed files:
   ```bash
   vendor/bin/pint --dirty --format agent
   ```

2. **Run affected tests** (minimum scope):
   ```bash
   php artisan test --compact tests/Feature/RelevantTest.php
   ```
   Or filter:
   ```bash
   php artisan test --compact --filter=relevantTestName
   ```

3. **Check LSP diagnostics** on changed files (via `lsp_diagnostics` tool)

4. **Verify no N+1 queries** if touching Eloquent relationships (eager load)

5. **Verify Form Requests** exist for any new controller validation

6. **Verify factories** exist for any new models

7. **Verify tests** exist or are updated for the change

## For New Features
- [ ] Action class created (if business logic)
- [ ] Form Request created (if validation needed)
- [ ] Model factory created/updated
- [ ] Feature test written and passing
- [ ] Pint formatted
- [ ] LSP diagnostics clean

## For Bug Fixes
- [ ] Minimal fix (no refactoring while fixing)
- [ ] Regression test added
- [ ] Pint formatted
- [ ] Existing tests still pass
