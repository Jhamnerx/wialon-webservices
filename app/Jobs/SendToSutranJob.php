<?php

namespace App\Jobs;

use App\Models\Service;
use App\Services\Senders\SutranSender;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Formatters\SutranFormatter;
use App\Services\Transformers\UnitTransformer;

class SendToSutranJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $units;
    protected $service;

    public function __construct(array $units, Service $service)
    {
        $this->units = $units;
        $this->service = $service;
        $this->onQueue('sutran-queue');
    }

    public function handle()
    {
        $transformer = new UnitTransformer();
        $formatter = new SutranFormatter($transformer);
        $tramas = $formatter->format($this->units, $this->service);
        $this->chunckTramas($tramas);
    }

    public function chunckTramas($tramas)
    {
        $url_sutran = config('app.env') == 'local' ? 'https://ws03.sutran.ehg.pe/api/v1.0/transmisiones' : 'https://ws03.sutran.gob.pe/api/v1.0/transmisiones';
        $tramas_por_grupo = array_chunk($tramas, 150);

        foreach ($tramas_por_grupo as $grupo) {
            $sender = new SutranSender($this->service);
            $sender->send($grupo, $url_sutran);
        }
    }
}
