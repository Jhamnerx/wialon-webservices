<?php

namespace App\Livewire\Logs;

use App\Models\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

class Reenvio extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $filterServicio = '';

    public function render()
    {
        $logs = Log::query()
            ->reenvio() // Solo logs de reenvío
            ->when($this->search, function ($query) {
                $query->where('plate_number', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterServicio, function ($query) {
                $query->where('service_name', $this->filterServicio);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Estadísticas
        $stats = [
            'total' => Log::reenvio()->count(),
            'exitosos' => Log::reenvio()->status('success')->count(),
            'errores' => Log::reenvio()->status('error')->count(),
            'hoy' => Log::reenvio()->hoy()->count(),
        ];

        return view('livewire.logs.reenvio', compact('logs', 'stats'));
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterServicio()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterServicio = '';
        $this->resetPage();
    }
}
