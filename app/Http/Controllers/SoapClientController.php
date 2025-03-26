<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SoapClientService;
use Illuminate\Http\JsonResponse;

class SoapClientController extends Controller
{
    protected SoapClientService $soapClientService;

    public function __construct(SoapClientService $soapClientService)
    {
        $this->soapClientService = $soapClientService;
    }

    public function getContact($id): JsonResponse
    {
        try {
            $response = $this->soapClientService->getContact($id);
            return response()->json(['status' => 'success', 'data' => $response]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getAddress($id): JsonResponse
    {
        try {
            $response = $this->soapClientService->getAddress($id);
            return response()->json(['status' => 'success', 'data' => $response]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
