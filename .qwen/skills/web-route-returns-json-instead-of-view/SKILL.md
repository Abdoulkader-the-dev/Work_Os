---
name: web-route-returns-json-instead-of-view
description: Fix page rendering raw JSON when controller methods return response()->json() but routes are registered in web.php instead of api.php
source: auto-skill
extracted_at: '2026-06-09T17:12:00.000Z'
---

## Problem

Visiting a page in the browser renders raw JSON instead of the expected HTML/Blade view.

## Root Cause

Controller methods (especially `index`, `show`) return `response()->json(...)` but the corresponding routes are registered in `routes/web.php`. Web routes must return Blade views (or redirects), not JSON responses. JSON responses belong in `routes/api.php`.

This commonly happens when:
- An API controller is reused for web routes without adapting the response types.
- A resource controller's `index`/`show` was written for JSON API consumption.

## Diagnosis Steps

1. Check the route file — confirm the route is in `web.php` (not `api.php`).
2. Open the controller method — if it returns `response()->json(...)`, that's the problem.
3. Check for missing web-only routes like `create` and `edit` (which return forms) — they return `view(...)`, not JSON.

## Fix Pattern

For each affected method, change from:

```php
public function index()
{
    // ... query logic ...
    return response()->json(['meetings' => $meetings], 200);
}
```

To:

```php
public function index()
{
    return view('pages.meetings');
}
```

The data-fetching logic moves into the Livewire component or view composer — the controller method just returns the view.

## Additional Checks

- **`create()` and `edit()` methods**: These must exist as separate `GET` routes returning Blade views. They cannot be handled by the same method as `store()`/`update()`.
- **Authorization**: Keep `$this->authorize(...)` calls in `show()`, `edit()`, `update()`, `destroy()` — just change the return type.
- **API variants**: If JSON endpoints are still needed, add them to `routes/api.php` with the `api` middleware group, or use `Route::apiResource()`.

## Example Route Block

```php
Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
Route::get('/meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
Route::patch('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
Route::delete('/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');
```
