<?php

namespace App\Livewire\Admin;

use App\Models\Projeto;
use App\Support\ChecklistCatalog;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProjetoChecklist extends Component
{
    public Projeto $projeto;

    public string $aba = ChecklistCatalog::ABA_URBANO;

    public function mount(Projeto $projeto): void
    {
        $this->projeto = $projeto;
        $this->authorize('view', $projeto);
    }

    public function setAba(string $aba): void
    {
        if (! in_array($aba, [ChecklistCatalog::ABA_URBANO, ChecklistCatalog::ABA_RURAL], true)) {
            return;
        }

        $this->aba = $aba;
    }

    #[Computed]
    public function items(): array
    {
        return app(ChecklistCatalog::class)->items($this->aba);
    }

    public function render(): View
    {
        return view('livewire.admin.projeto-checklist');
    }
}
