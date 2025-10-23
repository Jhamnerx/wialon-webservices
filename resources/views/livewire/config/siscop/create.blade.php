<x-form.modal.card persistent title="Crear Nuevo Acceso" wire:model.live="showModal" align="center">

    <div class="grid grid-cols-12 gap-6">
        {{-- Tipo --}}
        <div class="col-span-12">
            <x-form.select wire:model.live="tipo" label="Tipo de Acceso" placeholder="Selecciona el tipo" :options="[['name' => 'Serenazgo', 'id' => 'serenazgo'], ['name' => 'Policial', 'id' => 'policial']]"
                option-label="name" option-value="id" />
        </div>

        {{-- Nombre --}}
        <div class="col-span-12">
            <x-form.input wire:model="nombre" label="Nombre" placeholder="Ingresa el nombre" />
        </div>

        {{-- ID Municipalidad o Código Comisaría --}}
        @if ($tipo === 'serenazgo')
            <div class="col-span-12">
                <x-form.input wire:model="idMunicipalidad" label="ID Municipalidad"
                    placeholder="Ingresa el ID de la municipalidad" />
            </div>
        @elseif($tipo === 'policial')
            <div class="col-span-12">
                <x-form.input wire:model="idTransmision" label="ID Transmisión"
                    placeholder="Ingresa el ID de la transmisión" />
            </div>
            <div class="col-span-12">
                <x-form.input wire:model="codigoComisaria" label="Código Comisaría"
                    placeholder="Ingresa el código de la comisaría" />
            </div>
        @endif

        {{-- Ubigeo --}}
        <div class="col-span-12">
            <x-form.input wire:model="ubigeo" label="Ubigeo" placeholder="Ingresa el código de ubigeo (6 dígitos)"
                maxlength="6" />
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex justify-end gap-x-4">
            <div class="flex gap-2">
                <x-form.button flat label="Cancelar" wire:click="closeModal" />
                <x-form.button primary label="Guardar" wire:click.prevent="save" spinner="save" />
            </div>
        </div>
    </x-slot>
</x-form.modal.card>
