<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Address;

class SoapController extends Controller
{
    public function wsdl()
    {
        $wsdl = view('wsdl.contact')->render();

        return response($wsdl)->header('Content-Type', 'text/xml');
    }

    public function getContact(Request $request)
    {
        $xml = file_get_contents('php://input'); // Membaca request SOAP
        $data = simplexml_load_string($xml); // Parsing XML ke Object

        if (!$data) {
            return response('<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Body>
                        <soapenv:Fault>
                            <faultcode>SOAP-ENV:Client</faultcode>
                            <faultstring>Invalid XML format</faultstring>
                        </soapenv:Fault>
                    </soapenv:Body>
                </soapenv:Envelope>', 400)->header('Content-Type', 'text/xml');
        }

        $id = (int) $data->xpath('//id')[0] ?? 0; // Ambil nilai ID dari XML

        if (!$id || !is_numeric($id)) {
            return response('<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Body>
                        <soapenv:Fault>
                            <faultcode>SOAP-ENV:Client</faultcode>
                            <faultstring>Invalid ID</faultstring>
                        </soapenv:Fault>
                    </soapenv:Body>
                </soapenv:Envelope>', 400)->header('Content-Type', 'text/xml');
        }

        $contact = Contact::find($id);

        if (!$contact) {
            return response('<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Body>
                        <soapenv:Fault>
                            <faultcode>SOAP-ENV:Client</faultcode>
                            <faultstring>Contact not found</faultstring>
                        </soapenv:Fault>
                    </soapenv:Body>
                </soapenv:Envelope>', 404)->header('Content-Type', 'text/xml');
        }

        $responseXml = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <GetContactResponse xmlns="http://contact.com/wsdl">
                    <name>' . htmlspecialchars($contact->name) . '</name>
                    <email>' . htmlspecialchars($contact->email) . '</email>
                    <phone>' . htmlspecialchars($contact->phone) . '</phone>
                </GetContactResponse>
            </soapenv:Body>
        </soapenv:Envelope>';

        return response($responseXml)->header('Content-Type', 'text/xml');
    }

    public function callSoapService()
    {
        $wsdl = url('/api/soap.wsdl');

        try {
            $client = new \SoapClient($wsdl, [
                'trace' => 1,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'exceptions' => true
            ]);

            $params = ['id' => 1];
            $response = $client->__soapCall('GetContact', [$params]);

            return response()->json($response);
        } catch (\SoapFault $e) {
            return response('<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Body>
                        <soapenv:Fault>
                            <faultcode>SOAP-ENV:Server</faultcode>
                            <faultstring>' . $e->getMessage() . '</faultstring>
                        </soapenv:Fault>
                    </soapenv:Body>
                </soapenv:Envelope>', 500)->header('Content-Type', 'text/xml');
        }
    }

    public function contactService()
    {
        $path = public_path('wsdl/contact.wsdl');
        
        if (!file_exists($path)) {
            return response()->json(['error' => 'WSDL file not found'], 404);
        }
    
        return response()->file($path, [
            'Content-Type' => 'text/xml',
        ]);
    }

    public function AddressService()
    {
        $path = public_path('wsdl/address.wsdl');
        
        if (!file_exists($path)) {
            return response()->json(['error' => 'WSDL file not found'], 404);
        }
    
        return response()->file($path, [
            'Content-Type' => 'text/xml',
        ]);
    }
    
    public function handleSoapRequest(Request $request)
    {
        $xml = file_get_contents('php://input'); // Baca request SOAP
        $data = simplexml_load_string($xml); // Parsing XML
    
        if (!$data) {
            return response('<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Body>
                        <soapenv:Fault>
                            <faultcode>SOAP-ENV:Client</faultcode>
                            <faultstring>Invalid XML format</faultstring>
                        </soapenv:Fault>
                    </soapenv:Body>
                </soapenv:Envelope>', 400)->header('Content-Type', 'text/xml');
        }
    
        // Cek apakah ini request untuk Contact atau Address
        $action = $request->path(); // Dapatkan endpoint yang dipanggil
        $id = (int) ($data->xpath('//id')[0] ?? 0); // Ambil ID dari XML
    
        if (!$id || !is_numeric($id)) {
            return response('<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Body>
                        <soapenv:Fault>
                            <faultcode>SOAP-ENV:Client</faultcode>
                            <faultstring>Invalid ID</faultstring>
                        </soapenv:Fault>
                    </soapenv:Body>
                </soapenv:Envelope>', 400)->header('Content-Type', 'text/xml');
        }
    
        // Ambil data berdasarkan action yang dipanggil
        if (str_contains($action, 'contact')) {
            $dataModel = Contact::find($id);
            $responseXml = $this->generateContactResponse($dataModel);
        } elseif (str_contains($action, 'address')) {
            $dataModel = Address::find($id);
            $responseXml = $this->generateAddressResponse($dataModel);
        } else {
            return response('<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Body>
                        <soapenv:Fault>
                            <faultcode>SOAP-ENV:Client</faultcode>
                            <faultstring>Invalid service</faultstring>
                        </soapenv:Fault>
                    </soapenv:Body>
                </soapenv:Envelope>', 400)->header('Content-Type', 'text/xml');
        }
    
        return response($responseXml)->header('Content-Type', 'text/xml');
    }
    
    /**
     * Generate SOAP Response untuk Contact
     */
    private function generateContactResponse($contact)
    {
        if (!$contact) {
            return '<?xml version="1.0" encoding="UTF-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                <soapenv:Body>
                    <soapenv:Fault>
                        <faultcode>SOAP-ENV:Client</faultcode>
                        <faultstring>Contact not found</faultstring>
                    </soapenv:Fault>
                </soapenv:Body>
            </soapenv:Envelope>';
        }
    
        return '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <GetContactResponse xmlns="http://contact.com/wsdl">
                    <name>' . htmlspecialchars($contact->name) . '</name>
                    <email>' . htmlspecialchars($contact->email) . '</email>
                    <phone>' . htmlspecialchars($contact->phone) . '</phone>
                </GetContactResponse>
            </soapenv:Body>
        </soapenv:Envelope>';
    }
    
    /**
     * Generate SOAP Response untuk Address
     */
    private function generateAddressResponse($address)
    {
        if (!$address) {
            return '<?xml version="1.0" encoding="UTF-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                <soapenv:Body>
                    <soapenv:Fault>
                        <faultcode>SOAP-ENV:Client</faultcode>
                        <faultstring>Address not found</faultstring>
                    </soapenv:Fault>
                </soapenv:Body>
            </soapenv:Envelope>';
        }
    
        return '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <GetAddressResponse xmlns="http://address.com/wsdl">
                    <street>' . htmlspecialchars($address->street) . '</street>
                    <city>' . htmlspecialchars($address->city) . '</city>
                    <postal_code>' . htmlspecialchars($address->postal_code) . '</postal_code>
                </GetAddressResponse>
            </soapenv:Body>
        </soapenv:Envelope>';
    }

}
