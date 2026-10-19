<?php

namespace App\Services;

use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    public static function send($target, $message)
    {
        if (!config('services.fonnte.enabled') || empty($target)) {
            return false;
        }

        dispatch(function () use ($target, $message) {
            try {
                Http::withHeaders([
                    'Authorization' => config('services.fonnte.token')
                ])->timeout(10)->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                    'countryCode' => '62',
                ]);
            } catch (\Exception $e) {
                Log::error('Fonnte Error: ' . $e->getMessage());
            }
        })->afterResponse();

        return true;
    }

    public static function notifyPengaju(Pengajuan $pengajuan, $message)
    {
        $noHp = $pengajuan->user->no_hp ?? null;
        if ($noHp) {
            self::send($noHp, $message);
        }
    }

    public static function notifyRole($roleNames, $message)
    {
        $targets = User::getPhonesByRole($roleNames);
        if ($targets) {
            self::send($targets, $message);
        }
    }
}