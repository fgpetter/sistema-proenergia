---
name: php-laravel-standards
description: Padrões de desenvolvimento PHP e Laravel específicos para o projeto Sistema Proenergia. Define o uso de Actions sobre Services, Slim Controllers, e uso obrigatório de Enums em migrations e modelos.
---

# PHP & Laravel Project Standards

Esta skill garante que o código PHP e Laravel siga as convenções arquiteturais do projeto.

## Diretrizes Gerais

- **Concatenar Comandos**: Nunca encadeie comandos de criação (`make:model -m`) com `&&` ou `;` para evitar timestamps idênticos.
- **Docker Sail**: Sempre utilize `vendor/bin/sail` para comandos (artisan, npm, composer).
- **Match Operator**: Prefira `match` sobre `switch`.

## Arquitetura Laravel

- **Actions vs Services**: Priorize **Actions**. Use Services apenas se a lógica de negócio exigir múltiplos métodos relacionados.
- **Slim Controllers**: Controllers devem ser "magros". Se um Controller tiver apenas um método retornando uma view, use `Route::view()`.
- **Eloquent Observers**: Registre via PHP Attributes no Model: `#[ObservedBy([UserObserver::class])]`.
- **Thin Controller, Fat Model**: Use escopos no Model (ex: `User::active()->get()`).
- **Helpers**: Use helpers como `auth()->id()` e `redirect()->route()` em vez de Facades.

## Uso de Enums

- Localizados em `app/Enums`.
- Use Enums em Migrations como valor padrão e faça o cast no Model.
- Evite strings "hardcoded"; use constantes de Enum em Blade e Testes.

## Frontend & Blade

- Use `@session()` para flash messages.
- Use `@selected()` e `@checked()` em vez de atributos HTML manuais.
- Consulte `resource/views/components/ui` antes de criar novos componentes.
