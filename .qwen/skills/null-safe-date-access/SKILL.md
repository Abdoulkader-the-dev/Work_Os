---
name: null-safe-date-access
description: Fix Laravel Eloquent null date access by using null-safe operator on date-cast fields before calling format()
source: auto-skill
extracted_at: '2026-06-09T07:45:00.000Z'
---

## Pattern: Null-safe date access on Eloquent models

### Problem

When a model has a `date` (or `datetime`) cast but the underlying column is nullable, calling `->format('Y-m-d')` directly on the attribute throws a "Call to a member function format() on null" error when the value is `null` in the database.

This commonly appears in Livewire `mount()` methods when hydrating form fields from an existing model:

```php
// ❌ Crashes when $meeting->date is null
$this->date = $meeting->date->format('Y-m-d');
```

### Fix

Use the null-safe operator (`?->`) with a fallback:

```php
// ✅ Safe — falls back to today's date when null
$this->date = $meeting->date?->format('Y-m-d') ?? now()->format('Y-m-d');
```

### When to apply

- Any Eloquent model attribute with `'date'` or `'datetime'` in `$casts` where the DB column is `nullable`
- Livewire `mount()` methods that hydrate date fields for form editing
- Blade views that display `->format()` on a date attribute without a null check
- API resources that format dates

### How to find occurrences

Search for patterns like:

```bash
grep -rn "->format('Y-m-d')" app/Livewire/ app/Http/ app/Models/
```

Then check if the source attribute is from a nullable date column. If the model has `'date' => 'date'` in `$casts` and the migration has `->date('...')->nullable()`, apply the null-safe fix.

### Related: validator date rules

When validating nullable date fields in Livewire or Form Requests, use `'nullable', 'date'` instead of `'required', 'date'` if the field can legitimately be empty:

```php
'date' => ['required', 'date'],   // must be present
'deadline' => ['nullable', 'date'],  // can be null/omitted
```
