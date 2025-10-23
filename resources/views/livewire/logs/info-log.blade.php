<x-form.modal.card blur wire:model.live="showModal" align="center" width="6xl">
    @if ($log)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-5xl p-0 overflow-hidden">
            <!-- Header mejorado -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Detalles del Log</h2>
                            <p class="text-blue-100 text-sm">{{ $log->service_name }} -
                                {{ $log->plate_number ?: 'Sin placa' }}</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="text-white/80 hover:text-white transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <!-- Estado mejorado -->
                <div class="mb-6">
                    @if ($log->status == 'error')
                        <div
                            class="flex items-center p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Error en el envío</h3>
                                <p class="text-sm text-red-700 dark:text-red-300 mt-1">Hubo un problema al enviar la
                                    posición al webservice</p>
                            </div>
                        </div>
                    @elseif ($log->status == 'success')
                        <div
                            class="flex items-center p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Envío exitoso</h3>
                                <p class="text-sm text-green-700 dark:text-green-300 mt-1">La posición se envió
                                    correctamente al webservice</p>
                            </div>
                        </div>
                    @else
                        <div
                            class="flex items-center p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Estado pendiente
                                </h3>
                                <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">El estado del envío está
                                    pendiente de confirmación</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Información del dispositivo y fechas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Información del dispositivo -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                            <svg class="mr-2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Información del Dispositivo
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600 dark:text-gray-400">IMEI:</span>
                                <span
                                    class="text-xs font-mono text-gray-900 dark:text-gray-100">{{ $log->imei }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Placa:</span>
                                <span
                                    class="text-xs font-mono text-gray-900 dark:text-gray-100">{{ $log->plate_number ?: 'Sin placa' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Servicio:</span>
                                <span class="text-xs text-gray-900 dark:text-gray-100">{{ $log->service_name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Información de fechas -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                            <svg class="mr-2 h-4 w-4 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Información de Tiempo
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Fecha posición:</span>
                                <span class="text-xs text-gray-900 dark:text-gray-100">
                                    {{ $log->fecha_hora_posicion ? $log->fecha_hora_posicion->format('d/m/Y H:i:s') : 'Sin fecha' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-600 dark:text-gray-400">Fecha envío:</span>
                                <span class="text-xs text-gray-900 dark:text-gray-100">
                                    {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : 'Sin fecha' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- JSON containers con pestañas -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                    x-data="{ activeTab: 'response' }">
                    <div class="flex border-b border-gray-200 dark:border-gray-700">
                        <button @click="activeTab = 'response'"
                            :class="activeTab === 'response' ?
                                'bg-blue-50 dark:bg-blue-900/30 border-blue-500 text-blue-700 dark:text-blue-300' :
                                'bg-gray-50 dark:bg-gray-700 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                            <div class="flex items-center justify-center">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                Respuesta del WebService
                            </div>
                        </button>
                        <button @click="activeTab = 'request'"
                            :class="activeTab === 'request' ?
                                'bg-blue-50 dark:bg-blue-900/30 border-blue-500 text-blue-700 dark:text-blue-300' :
                                'bg-gray-50 dark:bg-gray-700 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="flex-1 px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                            <div class="flex items-center justify-center">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Datos Enviados
                            </div>
                        </button>
                    </div>

                    <!-- Contenido de pestañas -->
                    <div x-show="activeTab === 'response'" class="p-4">
                        <div class="relative bg-gray-900 dark:bg-gray-950 rounded-lg overflow-hidden">
                            <div class="absolute top-3 right-3 z-10">
                                <button onclick="copyToClipboard('response-json')"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                                    <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Copiar
                                </button>
                            </div>
                            <pre class="text-sm text-green-400 p-4 overflow-auto max-h-96" id="response-json"><code>{{ $log->response ? json_encode(json_decode($log->response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Sin respuesta' }}</code></pre>
                        </div>
                    </div>

                    <div x-show="activeTab === 'request'" class="p-4">
                        <div class="relative bg-gray-900 dark:bg-gray-950 rounded-lg overflow-hidden">
                            <div class="absolute top-3 right-3 z-10">
                                <button onclick="copyToClipboard('sent-json')"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                                    <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Copiar
                                </button>
                            </div>
                            <pre class="text-sm text-blue-400 p-4 overflow-auto max-h-96" id="sent-json"><code>{{ $log->request ? json_encode(json_decode($log->request), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Sin datos de envío' }}</code></pre>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeModal"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-form.modal.card>


@push('scripts')
    <script>
        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).innerText;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                // Usa Clipboard API si está disponible
                navigator.clipboard.writeText(text).then(() => {
                    @this.notifyClient('Texto copiado al portapapeles');
                }).catch((err) => {
                    console.error('Error al copiar:', err);
                    alert('No se pudo copiar el texto al portapapeles');
                });
            } else {
                // Alternativa: copiar manualmente al portapapeles
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed'; // Evitar que se desplace el scroll
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try {
                    document.execCommand('copy');
                    @this.notifyClient('Texto copiado al portapapeles');
                } catch (err) {
                    console.error('Error al copiar:', err);
                    alert('No se pudo copiar el texto al portapapeles');
                }
                document.body.removeChild(textarea);
            }
        }
    </script>
@endpush
