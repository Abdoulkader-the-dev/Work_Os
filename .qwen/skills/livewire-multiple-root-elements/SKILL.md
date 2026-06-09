---
name: livewire-multiple-root-elements
description: Fix MultipleRootElementsDetectedException when Livewire component views contain @section directives or multiple top-level HTML elements
source: auto-skill
extracted_at: '2026-06-09T17:30:00.000Z'
---

## Problem

Livewire throws `MultipleRootElementsDetectedException` when visiting a page that renders a Livewire component:

```
Livewire\Features\SupportMultipleRootElementDetection\MultipleRootElementsDetectedException
Livewire only supports one HTML element per component. Multiple root elements detected for component: [meetings.meeting-editor]
```

## Root Cause

Livewire component Blade views (`resources/views/livewire/**/*.blade.php`) must have **exactly one root HTML element**. The error occurs when the view contains:

1. **`@section(...)` / `@section...@endsection` directives** — These are meant for `@extends` layouts. In a Livewire view, each `@section` produces a separate root-level output node.
2. **Multiple top-level `<div>` or other HTML elements** — e.g., a `<div>` for content plus another `<div>` for a modal, both at the root level.
3. **Plain text or `@php` blocks at the root** alongside HTML elements.

Common pattern that triggers this:

```blade
{{-- livewire/meetings/meeting-editor.blade.php --}}
@section('page-title', 'Réunions')           {{-- root node #1: text --}}

@section('topbar-action')                     {{-- root node #2: HTML --}}
    <button wire:click="save">Save</button>
@endsection

<div style="max-width:820px;...">             {{-- root node #3: HTML --}}
    ...actual content...
</div>
```

Livewire sees 3 root elements → exception.

## Diagnosis Steps

1. **Identify the component** from the error message (e.g., `[meetings.meeting-editor]`).
2. **Open the view**: `resources/views/livewire/{component-path}.blade.php`.
3. **Count root-level nodes** — anything not nested inside another element:
   - `@section(...)` calls (inline form)
   - `@section...@endsection` blocks
   - Top-level HTML elements (`<div>`, `<section>`, etc.)
   - Bare `@php` blocks that output content
4. If there are 2+ root nodes, that's the problem.

## Fix Pattern

### Case 1: @section directives in Livewire views (most common)

**Remove** the `@section` blocks entirely. The page-level Blade view (`pages/*.blade.php`) that wraps the Livewire component should handle page titles and topbar actions:

```blade
{{-- pages/meeting-create.blade.php --}}
<x-app-layout>
@section('page-title', 'Nouveau compte rendu')
<livewire:meetings.meeting-editor />
</x-app-layout>
```

The Livewire view keeps only its single root `<div>`:

```blade
{{-- livewire/meetings/meeting-editor.blade.php --}}
<div style="max-width:820px;margin:0 auto;display:flex;flex-direction:column;gap:32px;">
    ...all content here...
</div>
```

### Case 2: View-switcher / toolbar content that was in @section

If the `@section` contained UI elements (view switcher buttons, action buttons), move them **inside** the main root `<div>` as an integrated toolbar row:

```blade
<div style="display:flex;flex-direction:column;gap:0;">
    <div class="flex-between" style="align-items:center;gap:14px;margin-bottom:16px;flex-wrap:wrap;">
        <div class="flex-center" style="gap:6px;">
            <a class="view-btn active" href="...">Tableau</a>
            <a class="view-btn" href="...">Kanban</a>
        </div>
        <a class="btn-primary" href="...">Nouvelle tâche</a>
    </div>
    ...rest of content...
</div>
```

### Case 3: Multiple top-level HTML elements

Wrap everything in a single container:

```blade
{{-- Before: two root elements --}}
<div>Content A</div>
<div>Content B</div>

{{-- After: single root element --}}
<div>
    <div>Content A</div>
    <div>Content B</div>
</div>
```

## Prevention

- **Never use `@section` in Livewire component views** — they render in isolation, not inside a `@extends` layout.
- **Always wrap Livewire views in a single root element** — typically a `<div>` with the component's layout.
- **Use `@props` instead of `@section`** for passing data to Blade components (not Livewire components).
- After creating or refactoring a Livewire view, verify: `grep -c "^<" view.blade.php` should show exactly 1 top-level opening tag (or use a more precise check).

## Related

- The `web-route-returns-json-instead-of-view` skill covers a related symptom (raw JSON in browser) that can appear alongside this error if the page route hits a JSON-returning controller.
- The `blade-undefined-variable` skill covers missing `@props` on Blade components, which is a different root cause but can appear during the same debugging session.
