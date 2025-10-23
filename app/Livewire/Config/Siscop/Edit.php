<?php

namespace App\Livewire\Config\Siscop;

use App\Models\Acceso;
use Livewire\Component;
use Livewire\Attributes\On;

class Edit extends Component
{
    public $showModal = false;
    public $accesoId;
    public $tipo = '';
    public $nombre = '';
    public $idMunicipalidad = '';
    public $idTransmision = '';
    public $codigoComisaria = '';
    public $ubigeo = '';

    protected $rules = [
        'tipo' => 'required|in:serenazgo,policial',
        'nombre' => 'required|string|max:255',
        'idMunicipalidad' => 'required_if:tipo,serenazgo|nullable|string|max:255',
        'idTransmision' => 'required_if:tipo,policial|nullable|string|max:255',
        'codigoComisaria' => 'required_if:tipo,policial|nullable|string|max:255',
        'ubigeo' => 'required|string|size:6',
    ];

    protected $messages = [
        'tipo.required' => 'El tipo es requerido',
        'tipo.in' => 'El tipo debe ser serenazgo o policial',
        'nombre.required' => 'El nombre es requerido',
        'idMunicipalidad.required_if' => 'El ID de serenazgo es requerido',
        'idTransmision.required_if' => 'El ID de transmisión es requerido',
        'codigoComisaria.required_if' => 'El código de comisaría es requerido',
        'ubigeo.required' => 'El ubigeo es requerido',
        'ubigeo.size' => 'El ubigeo debe tener exactamente 6 dígitos',
    ];

    #[On('openModalEditarAcceso')]
    public function openModal($accesoId)
    {
        $acceso = Acceso::find($accesoId);

        if ($acceso) {
            $this->accesoId = $acceso->id;
            $this->tipo = $acceso->tipo;
            $this->nombre = $acceso->nombre;
            $this->idMunicipalidad = $acceso->idMunicipalidad ?? '';
            $this->idTransmision = $acceso->idTransmision ?? '';
            $this->codigoComisaria = $acceso->codigoComisaria ?? '';
            $this->ubigeo = $acceso->ubigeo;

            $this->resetErrorBag();
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['accesoId', 'tipo', 'nombre', 'idMunicipalidad', 'idTransmision', 'codigoComisaria', 'ubigeo']);
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();

        try {
            $acceso = Acceso::find($this->accesoId);

            $acceso->update([
                'tipo' => $this->tipo,
                'nombre' => $this->nombre,
                'idMunicipalidad' => $this->tipo === 'serenazgo' ? $this->idMunicipalidad : null,
                'idTransmision' => $this->tipo === 'policial' ? $this->idTransmision : null,
                'codigoComisaria' => $this->tipo === 'policial' ? $this->codigoComisaria : null,
                'ubigeo' => $this->ubigeo,
            ]);

            $this->dispatch('notify-toast', icon: 'success', title: 'ÉXITO', mensaje: 'Acceso actualizado correctamente');
            $this->dispatch('actualizarTablaAccesos');


            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('notify-toast', icon: 'error', title: 'ERROR', mensaje: 'Error al actualizar el acceso: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $acceso = Acceso::find($this->accesoId);

            // Verificar si tiene dispositivos vinculados
            if ($acceso->devices()->count() > 0) {
                $this->dispatch('notify-toast', icon: 'warning', title: 'ADVERTENCIA', mensaje: 'No se puede eliminar el acceso porque tiene dispositivos vinculados');
                return;
            }

            $acceso->delete();

            $this->dispatch('notify-toast', icon: 'error', title: 'ELIMINADO', mensaje: 'Acceso eliminado correctamente');
            $this->dispatch('actualizarTablaAccesos');

            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('notify-toast', icon: 'error', title: 'ERROR', mensaje: 'Error al eliminar el acceso: ' . $e->getMessage());
        }
    }

    public function updatedTipo()
    {
        // Limpiar campos cuando cambia el tipo
        $this->idMunicipalidad = '';
        $this->idTransmision = '';
        $this->codigoComisaria = '';
        $this->resetErrorBag(['idMunicipalidad', 'idTransmision', 'codigoComisaria', 'ubigeo']);
    }

    public function render()
    {
        return view('livewire.config.siscop.edit');
    }
}
