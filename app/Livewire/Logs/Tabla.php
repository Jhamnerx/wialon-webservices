<?php

namespace App\Livewire\Logs;

use App\Models\Log;
use Livewire\Component;
use App\Enums\WebServices;
use App\Exports\LogsExport;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Exports\WoxLogsExport;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class Tabla extends Component
{
    use WithPagination, WithFileUploads;

    public int $perPage = 10;

    #[Url] public string $sortField = 'id';
    #[Url] public string $sortDirection = 'desc';
    #[Url] public string $search = '';
    #[Url] public ?string $plate_number = null;
    #[Url] public ?string $imei = null;
    #[Url] public ?string $service_name = null;
    #[Url] public ?string $status = null;
    #[Url] public ?string $fecha_hora_enviado = null;

    #[On('update-table-logs')]
    public function updateTableLogs()
    {
        $this->render();
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }


    public function render()
    {
        $logs = Log::query()
            ->normal() // Solo logs normales (no reenvíos)
            ->when($this->search, fn($query) => $query->where(function ($q) {
                $q->where('plate_number', 'like', "%{$this->search}%")
                    ->orWhere('imei', 'like', "%{$this->search}%")
                    ->orWhere('service_name', 'like', "%{$this->search}%");
            }))
            ->when($this->plate_number, fn($query) => $query->buscarPlaca($this->plate_number))
            ->when($this->imei, fn($query) => $query->buscarImei($this->imei))
            ->when($this->service_name, fn($query) => $query->servicio($this->service_name))
            ->when($this->status, fn($query) => $query->status($this->status))
            ->when($this->fecha_hora_enviado, fn($query) => $query->whereDate('created_at', $this->fecha_hora_enviado))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $services = WebServices::cases();

        return view('livewire.logs.tabla', compact('logs', 'services'));
    }

    public function openModalInfo(Log $log): void
    {
        $this->dispatch('open-modal-log', log: $log);
    }
}
