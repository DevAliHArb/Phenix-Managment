<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineSettings;
class SettingsController extends Controller
{
    public function index(){
        $machineSettings = MachineSettings::firstOrCreate([],[
            'ip' => config('zkteco.ip'), 
            'port' => config('zkteco.port'), 
            'timeout' => config('zkteco.timeout')]);
        return view('settings',compact('machineSettings'));
    }


    public function getMachineSettings(Request $request)
    {
        $settings = MachineSettings::firstOrCreate([], [
            'ip' => config('zkteco.ip'), 
            'port' => config('zkteco.port'), 
            'timeout' => config('zkteco.timeout')]);
        return response()->json($settings);
    }

    public function updateMachineSettings(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'timeout' => 'required|integer|min:1|max:60',
        ]);

        $settings = MachineSettings::firstOrCreate([], ['ip' => config('zkteco.ip'), 'port' => config('zkteco.port'), 'timeout' => config('zkteco.timeout')]);
        $settings->update($request->only('ip', 'port', 'timeout'));

        if($request->ajax())
            return response()->json(['success' => true, 'settings' => $settings]);

        return redirect()->route('settings.index')->with('success', 'Machine Settings updated successfully');
    }

    
}
