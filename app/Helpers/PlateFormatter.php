<?php

namespace App\Helpers;

class PlateFormatter
{
    public static function format($plate)
    {
        $cleanPlate = preg_replace('/\s+/', '', strtoupper($plate));
        
        if (preg_match('/^([А-ЯA-Z])(\d{3})([А-ЯA-Z]{2})(\d{2,3})$/', $cleanPlate, $matches)) {
            return [
                'letter1' => $matches[1],
                'numbers' => $matches[2],
                'letters2' => $matches[3],
                'region' => $matches[4],
                'formatted' => $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4]
            ];
        }
        
        return [
            'letter1' => '',
            'numbers' => $plate,
            'letters2' => '',
            'region' => '',
            'formatted' => $plate
        ];
    }
    
    public static function render($plate)
    {
        $data = self::format($plate);
        
        if ($data['letter1']) {
            return view('components.license-plate', $data)->render();
        }
        
        return '<span class="plate-simple">' . $plate . '</span>';
    }
}