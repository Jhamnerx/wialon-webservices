<?php

namespace App\Livewire\Config\Siscop;

use App\Models\Acceso;
use App\Models\Device;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class SyncDevices extends Component
{
    use WithPagination;

    public $showModal = false;
    public $acceso;
    public $search = '';
    public $selectedDevices = [];

    #[On('openModalVincularDispositivos')]
    public function openModal($accesoId)
    {
        // Reiniciar completamente el estado
        $this->reset(['selectedDevices', 'search']);
        $this->resetPage();

        $this->acceso = Acceso::with('devices')->find($accesoId);
        $this->selectedDevices = $this->acceso->devices->pluck('id')->toArray();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['acceso', 'search', 'selectedDevices']);
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function toggleDevice($deviceId)
    {
        $deviceId = (int) $deviceId; // Asegurar que sea un entero

        if (in_array($deviceId, $this->selectedDevices)) {
            $this->selectedDevices = array_values(array_filter($this->selectedDevices, fn($id) => $id != $deviceId));
        } else {
            $this->selectedDevices[] = $deviceId;
        }

        // Forzar re-render
        $this->selectedDevices = array_values(array_unique($this->selectedDevices));
    }

    public function toggleAll()
    {
        $currentPageDeviceIds = $this->getCurrentPageDeviceIds();

        // Si todos los dispositivos de la página actual están seleccionados, deseleccionar todos
        $allSelected = !array_diff($currentPageDeviceIds, $this->selectedDevices);

        if ($allSelected) {
            // Remover todos los dispositivos de la página actual de la selección
            $this->selectedDevices = array_values(array_diff($this->selectedDevices, $currentPageDeviceIds));
        } else {
            // Agregar todos los dispositivos de la página actual a la selección
            $this->selectedDevices = array_values(array_unique(array_merge($this->selectedDevices, $currentPageDeviceIds)));
        }
    }

    private function getCurrentPageDeviceIds()
    {
        return Device::whereHas('deviceServices', function ($query) {
            $query->where('name', 'siscop')->where('active', true);
        })
            ->where(function ($query) {
                $query->where('plate', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%')
                    ->orWhere('id_wialon', 'like', '%' . $this->search . '%');
            })
            ->paginate(10)
            ->pluck('id')
            ->toArray();
    }

    public function save()
    {
        try {
            // Primero, desvincula todos los dispositivos del acceso actual
            Device::where('acceso_id', $this->acceso->id)->update(['acceso_id' => null]);

            // Luego, vincula los dispositivos seleccionados
            if (!empty($this->selectedDevices)) {
                Device::whereIn('id', $this->selectedDevices)->update(['acceso_id' => $this->acceso->id]);
            }

            $this->dispatch('notify-toast', icon: 'success', title: 'ÉXITO', mensaje: 'Dispositivos vinculados correctamente');

            $this->dispatch('actualizarTablaAccesos');

            // Reiniciar el estado después de guardar
            $this->reset(['selectedDevices', 'search', 'acceso']);
            $this->resetPage();
            $this->showModal = false;
        } catch (\Exception $e) {
            $this->dispatch('notify-toast', icon: 'error', title: 'ERROR', mensaje: 'Error al actualizar el acceso: ' . $e->getMessage());
        }
    }

    public function isAllSelected()
    {
        $currentPageDeviceIds = $this->getCurrentPageDeviceIds();
        return !empty($currentPageDeviceIds) && !array_diff($currentPageDeviceIds, $this->selectedDevices);
    }

    public function render()
    {
        $devices = Device::whereHas('deviceServices', function ($query) {
            $query->where('name', 'siscop')->where('active', true);
        })
            ->where(function ($query) {
                $query->where('plate', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%')
                    ->orWhere('id_wialon', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.config.siscop.sync-devices', compact('devices'));
    }
}
