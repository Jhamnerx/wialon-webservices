<?php

namespace App\Livewire\Config\Siscop;

use App\Models\Acceso;
use App\Models\Device;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;


class ShowDevices extends Component
{
    use WithPagination;

    public $showModal = false;
    public $acceso;
    public $search = '';

    #[On('openModalVerDispositivos')]
    public function openModal($accesoId)
    {
        $this->reset();
        $this->acceso = Acceso::with('devices')->find($accesoId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['acceso', 'search']);
        $this->resetPage();
    }

    public function desvincularDispositivo($deviceId)
    {
        try {
            $device = Device::find($deviceId);

            if ($device && $device->acceso_id == $this->acceso->id) {
                $device->update(['acceso_id' => null]);

                $this->dispatch(
                    'notify-toast',
                    icon: 'success',
                    title: 'ÉXITO',
                    mensaje: 'Dispositivo desvinculado correctamente'
                );

                // Actualizar la información del acceso
                $this->acceso = Acceso::with('devices')->find($this->acceso->id);
                $this->dispatch('actualizarTablaAccesos');
            }
        } catch (\Exception $e) {
            $this->dispatch(
                'notify-toast',
                icon: 'error',
                title: 'ERROR',
                mensaje: 'Error al desvincular dispositivo: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        $devices = collect();

        if ($this->acceso) {
            $query = Device::with('imagen')->where('acceso_id', $this->acceso->id);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('plate', 'like', '%' . $this->search . '%')
                        ->orWhere('name', 'like', '%' . $this->search . '%')
                        ->orWhere('id_wialon', 'like', '%' . $this->search . '%');
                });
            }

            $devices = $query->paginate(10);
        }

        return view('livewire.config.siscop.show-devices', compact('devices'));
    }
}
