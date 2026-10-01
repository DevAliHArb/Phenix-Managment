@extends('layouts.app')

@section('content')
<div  style="width:100% ;min-height:100%">
    
        
            @if(session('success'))
                <div class="d-flex justify-content-center align-items-center">
                    <div class="alert mb-0 alert-success">{{ session('success') }}</div>
                </div>
            @endif
        
    <div >
        <h3>Machine Settings</h3>
        <form class="border bg-white rounded p-2" action="{{ route('settings.updateMachineSettings') }}" method="post">
            @csrf
            @method('PUT')
            <label class="form-label" for="ip">Ip</label>
            <input type="text" class="form-control mb-2" name="ip" id="ip" value="{{ $machineSettings->ip??null}}" required> 
            <label class="form-label" for="port">Port</label>
            <input type="text" class="form-control mb-2" name="port" id="port" value="{{  $machineSettings->port??null  }}" required>
            <label class="form-label" for="timeout">Connection Timeout (seconds)</label>
            <input type="number" class="form-control mb-2" name="timeout" id="timeout" min="1" max="60" value="{{  $machineSettings->timeout ?? config('zkteco.timeout')  }}" required>
            <div class="d-flex justify-content-end align-items-center">
                <button type="submit" class="btn btn-primary mt-2 mb-2">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection