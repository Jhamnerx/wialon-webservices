<?php

namespace App\Livewire\Logs;

use App\Models\Log;
use App\Exports\LogsExport;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;

class Index extends Component
{
    public $stats = [];

    public function mount()
    {
        $this->loadStats();
    }

    public function refreshTable()
    {
        $this->dispatch('update-table-logs');
        $this->loadStats(); // Actualizar estadísticas también
    }

    public function loadStats()
    {
        $today = Carbon::today();

        $this->stats = [
            'success_count' => Log::normal()->status('success')->whereDate('created_at', $today)->count(),
            'error_count' => Log::normal()->status('error')->whereDate('created_at', $today)->count(),
            'total_today' => Log::normal()->whereDate('created_at', $today)->count(),
            'hourly_average' => $this->calculateHourlyAverage()
        ];
    }

    private function calculateHourlyAverage()
    {
        $today = Carbon::today();
        $currentHour = Carbon::now()->hour;

        if ($currentHour === 0) {
            return 0;
        }

        $totalToday = Log::normal()->whereDate('created_at', $today)->count();
        return round($totalToday / max($currentHour, 1), 1);
    }

    public function render()
    {
        return view('livewire.logs.index', [
            'stats' => $this->stats
        ]);
    }

    public function export()
    {
        return Excel::download(new LogsExport, 'logs-' . Carbon::now()->format('Y-m-d-H-i') . '.xlsx');
    }

    public function deleteAllLogs()
    {
        try {
            $count = Log::count();
            Log::truncate();

            $this->dispatch(
                'notify-toast',
                icon: 'success',
                title: 'LOGS ELIMINADOS',
                mensaje: "Se eliminaron {$count} registros de logs correctamente"
            );

            $this->loadStats();
            $this->refreshTable();
        } catch (\Exception $e) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'ERROR',
                mensaje: 'Error al eliminar logs: ' . $e->getMessage()
            );
        }
    }
}
