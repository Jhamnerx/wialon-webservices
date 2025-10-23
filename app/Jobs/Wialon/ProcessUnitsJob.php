<?php

namespace App\Jobs\Wialon;

use App\Models\Config;
use App\Jobs\SendToSutranJob;
use App\Jobs\SendToSiscopJob;
use App\Jobs\SendToOsinergminJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Services\Fetchers\WialonFetcher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Processors\WialonProcessor;


class ProcessUnitsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $config;

    public function __construct()
    {
        $this->onQueue('web-service');
    }

    public function handle()
    {
        $this->config = Config::first();

        if (!$this->config || !$this->config->token) {
            Log::warning('Token de configuración es null o config no encontrada. Terminando el job.');
            return;
        }

        try {
            $fetcher = new WialonFetcher($this->config);
            $units = $fetcher->fetchUnits();

            if (empty($units)) {
                Log::info('No se obtuvieron unidades de Wialon. Terminando el job.');
                return;
            }

            Log::info('Unidades obtenidas de Wialon: ' . count($units));

            $processor = new WialonProcessor();
            $processedUnits = $processor->processUnits($units);

            // Despachar jobs a colas específicas
            if (!empty($processedUnits['sutran']['units']) && $processedUnits['sutran']['service']) {
                Log::info('Despachando ' . count($processedUnits['sutran']['units']) . ' unidades a SUTRAN');
                SendToSutranJob::dispatch($processedUnits['sutran']['units'], $processedUnits['sutran']['service']);
            }

            if (!empty($processedUnits['osinergmin']['units']) && $processedUnits['osinergmin']['service']) {
                Log::info('Despachando ' . count($processedUnits['osinergmin']['units']) . ' unidades a OSINERGMIN');
                SendToOsinergminJob::dispatch($processedUnits['osinergmin']['units'], $processedUnits['osinergmin']['service']);
            }

            if (!empty($processedUnits['siscop']['units']) && $processedUnits['siscop']['service']) {
                Log::info('Despachando ' . count($processedUnits['siscop']['units']) . ' unidades a SISCOP');
                SendToSiscopJob::dispatch($processedUnits['siscop']['units'], $processedUnits['siscop']['service']);
            }

            Log::info('ProcessUnitsJob completado exitosamente');
        } catch (\Exception $e) {
            dd($e);
            Log::error('Error en ProcessUnitsJob: ' . $e->getMessage());
            throw $e; // Re-lanzar para que el job falle y se pueda reintentar si es necesario
        }
    }
}
