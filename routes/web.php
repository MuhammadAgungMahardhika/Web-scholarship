<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-python', function () {
    $scriptPath = storage_path('app/ml/add.py');
    $angka1 = 15;
    $angka2 = 10;

    $process = new Process(['python', $scriptPath, $angka1, $angka2]);

    try {
        $process->mustRun();
        $pythonOutput = $process->getOutput();
        $resultData = json_decode($pythonOutput, true);

        if (isset($resultData['error'])) {
            return "Error dari Python: " . $resultData['error'];
        } else {
            return "Hasil penjumlahan dari Python: " . $resultData['result'];
        }
    } catch (ProcessFailedException $exception) {
        return $exception->getMessage();
    }
});
