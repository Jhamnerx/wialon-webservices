<?php

namespace App\Livewire\Config\Siscop;

use App\Models\Acceso;
use Livewire\Component;
use Livewire\Attributes\On;

class Create extends Component
{
    public $showModal = false;
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

    #[On('openModalCreateAcceso')]
    public function openModal()
    {
        $this->reset(['tipo', 'nombre', 'idMunicipalidad', 'idTransmision', 'codigoComisaria', 'ubigeo']);
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['tipo', 'nombre', 'idMunicipalidad', 'idTransmision', 'codigoComisaria', 'ubigeo']);
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();

        try {
            Acceso::create([
                'tipo' => $this->tipo,
                'nombre' => $this->nombre,
                'idMunicipalidad' => $this->tipo === 'serenazgo' ? $this->idMunicipalidad : null,
                'idTransmision' => $this->tipo === 'policial' ? $this->idTransmision : null,
                'codigoComisaria' => $this->tipo === 'policial' ? $this->codigoComisaria : null,
                'ubigeo' => $this->ubigeo,
            ]);

            $this->dispatch('notify-toast', icon: 'success', title: 'ÉXITO', mensaje: 'Acceso creado correctamente');
            $this->dispatch('actualizarTablaAccesos');

            $this->closeModal();
        } catch (\Exception $e) {

            $this->dispatch('notify-toast', icon: 'error', title: 'ERROR', mensaje: 'Error al crear el acceso: ' . $e->getMessage());
        }
    }

    public function updatedTipo()
    {
        // Limpiar campos cuando cambia el tipo
        $this->idMunicipalidad = '';
        $this->idTransmision = '';
        $this->codigoComisaria = '';
        $this->resetErrorBag(['idMunicipalidad', 'idTransmision', 'codigoComisaria']);
    }

    public function render()
    {
        return view('livewire.config.siscop.create');
    }
}
