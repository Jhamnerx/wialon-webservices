<?php

namespace App\Jobs\Wialon;

use Carbon\Carbon;
use App\Models\Log;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ClearLogs implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $days;

    public function __construct(int $days)
    {
        $this->days = $days;
    }


    public function handle(): void
    {
        $date = Carbon::now()->subDays($this->days);
        Log::where('created_at', '<', $date)->delete();
    }
}
