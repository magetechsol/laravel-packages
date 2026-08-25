<?php

declare(strict_types=1);

namespace MageTech\DevTools\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MageTech\DevTools\DevTools;

class DashboardController extends Controller
{
    public function __construct(
        protected DevTools $devtools,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = $this->devtools->getAllData();
        $health = $this->devtools->getHealthStatus();
        $overallHealth = $this->devtools->getOverallHealth();
        $refreshInterval = config('mts-devtools.refresh_interval', 30);

        return view('devtools::dashboard', [
            'data' => $data,
            'health' => $health,
            'overallHealth' => $overallHealth,
            'refreshInterval' => $refreshInterval,
        ]);
    }
}
