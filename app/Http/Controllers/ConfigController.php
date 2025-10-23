<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\Service;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function index()
    {
        $config = Config::first();
        $servicios = Service::all();
        return view('config.index', compact('config', 'servicios'));
    }

    public function token(Request $request)
    {
        // Obtener parámetros específicos de la URL

        try {
            $successUri = $request->query('success_uri');
            $user = $request->query('user');
            $wialonSdkUrl = $request->query('wialon_sdk_url');
            $accessToken = $request->query('access_token');
            $svcError = $request->query('svc_error');


            $config = Config::first();

            $config->update([
                'user' => $user,
                'token' => $accessToken,
                'base_uri' => $wialonSdkUrl,
            ]);


            return redirect()->route('config');
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
