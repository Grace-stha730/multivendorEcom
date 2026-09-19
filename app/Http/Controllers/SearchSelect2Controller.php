<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Province;
use Illuminate\Http\Request;

class SearchSelect2Controller extends Controller
{
    public function __invoke(Request $request)
    {
        return match ($request->string('module')->toString()) {
            'province', 'get_province' => $this->getProvince($request),
            'district', 'get_district' => $this->getDistrict($request),
            default => response()->json(['results' => []]),
        };
    }

    public function getProvince(Request $request)
    {
        return Province::query()
            ->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('term', '') . '%');
            })
            ->limit(10)
            ->get(['id', 'name as text']);
    }

    private function getDistrict(Request $request)
    {
        return response()->json(['results' => District::query()
            ->where('province_id', $request->input('selected_id'))
            ->when($request->filled('term'), fn($query) => $query->where('name', 'like', '%' . $request->term . '%'))
            ->limit(10)->get(['id', 'name as text'])]);
    }
}
