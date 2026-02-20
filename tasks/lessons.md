# Lessons & Patterns

Este arquivo registra padrões, correções e lições aprendidas durante o desenvolvimento do Sistema Proenergia.

## Padrões Arquiteturais

### Laravel & PHP

- **Action Pattern**: Sempre preferir Actions (`app/Actions`) para lógica de negócio em vez de Services, a menos que a complexidade exija múltiplos métodos relacionados.
- **Slim Controllers**: Controllers devem ser mínimos. Se apenas retornam uma view, use `Route::view()`.
- **DRY (Don't Repeat Yourself)**: Antes de criar um método em um Controller, Action ou Livewire, verifique se o mesmo processo já não é executado em outra classe para evitar duplicidade.
- **Remoção de Código Morto**: Sempre que uma alteração ou refatoração for implementada, o código antigo deve ser removido imediatamente.
- **Análise Prévia**: Antes de criar qualquer nova classe ou view, analise o padrão já estabelecido no projeto para manter a consistência.
- **Enum First**: Usar Enums para colunas de status, tipos de contrato e papéis de usuário. Sempre realizar o cast no Model e usar o valor do Enum em Migrations.
- **Sail Environment**: Todos os comandos (artisan, composer, npm) devem ser executados via `vendor/bin/sail`.

### Livewire & UI

- **Proibição de Volt**: **NUNCA** utilizar Livewire Volt. Todos os componentes devem ser baseados em classes PHP (`Component`).
- **Raiz Única**: Garantir que componentes Blade tenham apenas um elemento pai.
- **wire:key**: Sempre incluir em loops para evitar erros de renderização e estado.
- **Alpine.js**: Utilizar para estados puramente de UI (dropdowns, toggles) para evitar round-trips desnecessários ao servidor.
- **Componentes Genéricos**:
    - Sempre verifique em `resources/views/components` se um componente (botão, card, input, etc.) já existe antes de criar um novo.
    - Ao criar um componente genérico reutilizável, salve-o em `resources/views/components`.

## Erros Evitados

- **Timestamps Duplicados**: Nunca encadear comandos de criação de arquivos (ex: `make:model -m`) para garantir que migrations tenham timestamps distintos e sequenciais.

## Histórico de Correções

- [2026-02-20] Inicialização do sistema de lições baseada nas regras do Cursor e workflow default.
