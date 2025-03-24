<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function getWiup()
    {
        $data = File::get(storage_path('app/WIUP.json'));
        return response()->json(json_decode($data));
    }

    public function getBatasLaut()
    {
        $data = File::get(storage_path('app/Batas Laut.json'));
        return response()->json(json_decode($data));
    }

    public function getWilayahPertambangan()
    {
        $data = File::get(storage_path('app/Wilayah Pertambangan.json'));
        return response()->json(json_decode($data));
    }

    public function leafletMap()
    {
        $wilayah = json_decode(Storage::get('Wilayah_Pertambangan.json'), true);
        $wiup = json_decode(Storage::get('WIUP.json'), true);
        $batas_laut = json_decode(Storage::get('Batas_Laut.json'), true);

        return view('maps.leaflet', compact('wilayah', 'wiup', 'batas_laut'));
    }

    public function mapboxMap()
    {
        return view('maps.mapbox');
    }
}
