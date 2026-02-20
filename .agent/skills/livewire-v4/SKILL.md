---
name: livewire-alpinejs-expert
description: Especialista em Livewire (Class-based) e Alpine.js. Foco em performance, estados de carregamento, e comunicação entre componentes no projeto Proenergia. (Volt é proibido neste projeto).
---

# Livewire & Alpine.js Specialist

Esta skill define como construir interfaces reativas de alta performance seguindo as regras do projeto.

## Livewire (Class-based apenas)

- **NÃO use Livewire Volt**. Use sempre componentes baseados em classes PHP.
- **Raiz Única**: Componentes devem ter apenas um elemento raiz no Blade.
- **wire:model**: É adiado (deferred) por padrão. Use `.live` apenas se necessário e sempre com `.debounce`.
- **wire:key**: Obrigatório em loops (`wire:key="item-{{ $item->id }}"`).
- **Propriedades Computadas**: Use `#[Computed]` para operações pesadas e acesse via `$this->propriedade`.

## Alpine.js

- Use Alpine para interações puramente de UI que não exigem banco de dados (ex: toggles, dropdowns).
- **$wire**: Use `$wire` dentro do Alpine para manipular o estado do Livewire ou chamar métodos assincronamente.
- **x-modelable**: Use para componentes Alpine que precisam funcionar com `wire:model`.

## Performance & Segurança

- **Lazy Loading**: Use `lazy` em componentes pesados.
- **Islands vs Nested**: Use Islands para isolamento de performance sem complexidade de ciclo de vida.
- **Locking**: Use `#[Locked]` para propriedades que não devem ser alteradas pelo cliente.
- **Validação**: Sempre execute `$this->validate()` antes de persistir dados.

## Testes

- Teste componentes com `Livewire::test(Component::class)`.
- Verifique erros com `->assertHasErrors(['field'])`.
