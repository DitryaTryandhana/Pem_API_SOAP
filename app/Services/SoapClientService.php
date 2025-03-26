<?php

namespace App\Services;

use SoapClient;
use SoapFault;
use Illuminate\Support\Facades\Log;

class SoapClientService
{
    protected ?SoapClient $contactClient = null;
    protected ?SoapClient $addressClient = null;

    protected function getContactClient(): SoapClient
    {
        if (!$this->contactClient) {
            $wsdl = env('CONTACT_WSDL_URL', 'http://127.0.0.1:8000/api/soap/contact.wsdl'); // Gunakan env()
            $this->contactClient = new SoapClient($wsdl, [
                'trace' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'exceptions' => true, // Tangani error SOAP sebagai exception
            ]);
        }
        return $this->contactClient;
    }

    protected function getAddressClient(): SoapClient
    {
        if (!$this->addressClient) {
            $wsdl = env('ADDRESS_WSDL_URL', 'http://127.0.0.1:8000/api/soap/address.wsdl'); // Gunakan env()
            $this->addressClient = new SoapClient($wsdl, [
                'trace' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'exceptions' => true, // Tangani error SOAP sebagai exception
            ]);
        }
        return $this->addressClient;
    }

    public function getContact($id)
    {
        try {
            $params = ['id' => $id];
            $response = $this->getContactClient()->__soapCall('GetContact', [$params]);

            return ['status' => 'success', 'data' => $response];
        } catch (SoapFault $e) {
            Log::error("SOAP Error in getContact: " . $e->getMessage());
            return ['status' => 'error', 'message' => "SOAP Error: " . $e->getMessage()];
        } catch (\Exception $e) {
            Log::error("General Error in getContact: " . $e->getMessage());
            return ['status' => 'error', 'message' => "General Error: " . $e->getMessage()];
        }
    }

    public function getAddress($id)
    {
        try {
            $params = ['id' => $id];
            $response = $this->getAddressClient()->__soapCall('GetAddress', [$params]);

            return ['status' => 'success', 'data' => $response];
        } catch (SoapFault $e) {
            Log::error("SOAP Error in getAddress: " . $e->getMessage());
            return ['status' => 'error', 'message' => "SOAP Error: " . $e->getMessage()];
        } catch (\Exception $e) {
            Log::error("General Error in getAddress: " . $e->getMessage());
            return ['status' => 'error', 'message' => "General Error: " . $e->getMessage()];
        }
    }
}
