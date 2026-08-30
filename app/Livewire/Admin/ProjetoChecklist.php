<?php

namespace App\Livewire\Admin;

use App\Models\Projeto;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ProjetoChecklist extends Component
{
    public const string ABA_URBANO = 'urbano';

    public const string ABA_RURAL = 'rural';

    public Projeto $projeto;

    public string $aba = self::ABA_URBANO;

    public function mount(Projeto $projeto): void
    {
        $this->projeto = $projeto;
        $this->authorize('view', $projeto);
    }

    public function setAba(string $aba): void
    {
        if (! in_array($aba, [self::ABA_URBANO, self::ABA_RURAL], true)) {
            return;
        }

        $this->aba = $aba;
    }

    public function render(): View
    {
        return view('livewire.admin.projeto-checklist');
    }
}
