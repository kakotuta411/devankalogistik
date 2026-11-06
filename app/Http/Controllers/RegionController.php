<?php
namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function provinces(Request $request): JsonResponse
    {
        return response()->json(Region::provinces()->get(['code', 'name']));
    }

    public function regencies(Request $request): JsonResponse
    {
        return response()->json(Region::regencies($request->province)->get(['code', 'name']));
    }

    public function districts(Request $request): JsonResponse
    {
        return response()->json(Region::districts($request->regency)->get(['code', 'name']));
    }

    public function villages(Request $request): JsonResponse
    {
        return response()->json(Region::villages($request->district)->get(['code', 'name']));
    }
}
