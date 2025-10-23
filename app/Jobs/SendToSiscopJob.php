<?php

namespace App\Jobs;

use App\Models\Service;
use App\Services\Senders\SiscopSender;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Formatters\SiscopFormatter;
use App\Services\Transformers\UnitTransformer;

class SendToSiscopJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $units;
    protected $service;

    public function __construct(array $units, Service $service)
    {
        $this->units = $units;
        $this->service = $service;
        $this->onQueue('siscop-queue');
    }

    public function handle()
    {
        $transformer = new UnitTransformer();
        $formatter = new SiscopFormatter($transformer);
        $tramas = $formatter->format($this->units, $this->service);

        // Verificar si hay tramas válidas para enviar
        if (empty($tramas)) {
            \Illuminate\Support\Facades\Log::info('SISCOP: No hay tramas válidas para enviar - todos los dispositivos fueron filtrados');
            return;
        }

        // Enviar al sender
        $sender = new SiscopSender($this->service);
        $sender->send($tramas);
    }
}
