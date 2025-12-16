---
inclusion: fileMatch
fileMatchPattern: 'app/Livewire/**/*.php'
---

# Livewire v3 rules (scoped)

## Core
- Keep state on the server; validate and authorize in actions.
- Use `$this->dispatch()` for events (avoid legacy `emit()` / `dispatchBrowserEvent()` patterns).
- Prefer the `App\Livewire` namespace (not `App\Http\Livewire`).

## UX
- Use `wire:loading` / `wire:target` for loading feedback.
- Use `wire:key` in loops.

## Testing
- Prefer Pest + `Livewire::test()` for component behavior.
- Use Boost `search-docs` when unsure about Livewire v3 APIs.
