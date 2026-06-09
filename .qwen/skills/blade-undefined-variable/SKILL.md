---
name: blade-undefined-variable
description: Debug and fix "Undefined variable $value" (or similar) errors in compiled Blade views, typically from Livewire components with missing required props
source: auto-skill
extracted_at: '2026-06-09T16:12:00.000Z'
---

## Pattern: Undefined variable in compiled Blade views from missing component props

### Problem

A Livewire or Blade component declares a required prop (e.g., `@props(['value', 'label'])`) and the consuming view omits it. The error appears in a **compiled** cache file, not the source:

```
ErrorException: Undefined variable $value
at storage/framework/views/1580b06a304f1fe815bbed5e898b2899.php:33
```

The stack trace makes it look like a framework bug, but the root cause is a **missing required prop on a Blade component invocation**.

### Diagnosis steps

1. **Note the error**: `$value` (or `$label`, `$subvalue`, etc.) is undefined in the compiled view.
2. **Extract the variable name** from the error message.
3. **Find components that use that variable name** without a null fallback:
   ```bash
   grep -rn "\$value" resources/views/components/*.php
   ```
4. **Check which components declare it as a required prop** (`@props(['value'])`) — those are the suspects.
5. **Find all invocations** of the suspected component and verify every call site passes the prop:
   ```bash
   grep -rn "x-kpi-card\|x-section-header\|x-empty-state" resources/views/
   ```
6. **Fix the missing prop** at the call site (not the component).

### Real example from this project

The dashboard had:
```blade
{{-- app/Livewire\Dashboard.php passes $tasksThisWeek, $completionRate, etc. --}}
<x-kpi-card
    label="Créées cette semaine"
    subvalue="Tâches avec deadline"
    {{-- missing: value="{{ $tasksThisWeek }}" --}}
/>
```

While `kpi-card.blade.php` declared `@props(['label', 'value', 'subvalue' => null, 'delta' => null])` and rendered `{{ $value }}` without a null coalescing fallback.

Fix: `value="{{ $tasksThisWeek }}"` — which the card clearly intended to show based on its label.

### Prevention

When writing Blade components, add a null-safe fallback for variables that _might_ be omitted:

```blade
{{ $value ?? '' }}     {{-- instead of bare {{ $value }} --}}
{{ $label ?? '—' }}    {{-- explicit fallback for labels --}}
```

This turns a crash into a graceful empty display during rapid prototyping.

### Related: compact() variable name mismatch

When a controller uses `compact('meeting')`, the variable in the view is `$meeting` (the model), not `$meetingId`.

```
ErrorException: Undefined variable $meetingId
at resources/views/pages/meeting-show.blade.php:2
```

**Cause**: The blade references `$meetingId` but the controller passed `compact('meeting')`.

**Fix**: Either change the blade to use the.compacted variable:
```blade
{{-- Controller: return view('pages.meeting-show', compact('meeting')); --}}
<livewire:meetings.meeting-editor :meetingId="$meeting->id" />
```

Or change the controller to pass `meetingId` explicitly:
```php
return view('pages.meeting-show', ['meetingId' => $meeting->id]);
```

Blade's `Undefined variable` with an object suffix like `Id`, `_id`, or `_at` is almost always a compact mismatch — the developer assumed a different variable name than what the controller actually passed.

### Related: orphaned @endif / @endforeach

When refactoring Blade views, orphaned closing directives (`@endif`, `@endforeach`, `</div>`, `</button>`) left over from removed blocks cause parse errors:

```
ParseError: syntax error, unexpected token "endforeach", expecting end of file
```

Diagnose by counting opening/closing pairs:
```bash
grep -c "@if\|@foreach\|@forelse" file.blade.php
grep -c "@endif\|@endforeach\|@endforelse" file.blade.php
```
If counts don't match, find and remove the orphaned closers. Also look for `</button>` or `</div>` that don't have matching openers.
