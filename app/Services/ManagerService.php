<?php
namespace App\Services;

use App\Repositories\ManagerRepository;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;
use App\Models\Shipment;
use App\Models\QrCode;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class ManagerService
{
    use ApiResponseTrait;
    protected $managerRepository;

    public function __construct(ManagerRepository $managerRepository)
    {
        $this->managerRepository = $managerRepository;
    }

    private function generateCustomerCode()
    {
        $letters = strtoupper(substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2));
        $numbers = rand(100, 999);
        $suffix = rand(10, 99);
        return "$letters-$numbers-$suffix";
    }

    private function generateVerifiedCode()
    {
        return rand(100000, 999999);
    }

    public function addCustomer(array $data)
    {
        $customerCode = $this->generateCustomerCode();
        $verifiedCode = $this->generateVerifiedCode();

    $profilePhotoPath = null;
    if (isset($data['profile_photo_path'])) {
        $filename = Str::uuid() . '.' . $data['profile_photo_path']->getClientOriginalExtension();
        $data['profile_photo_path']->move(public_path('/uploads/profile_photos'), $filename);
        $profilePhotoPath = '/uploads/profile_photos/' . $filename;
    }

    $identityPhotoPath = null;
    if (isset($data['identity_photo_path'])) {
        $filename = Str::uuid() . '.' . $data['identity_photo_path']->getClientOriginalExtension();
        $data['identity_photo_path']->move(public_path('/uploads/identity_photos'), $filename);
        $identityPhotoPath = '/uploads/identity_photos/' . $filename;
    }




        $userData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address'=>$data['address'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'profile_photo_path' => $profilePhotoPath,
            'identity_photo_path' => $identityPhotoPath,
            'status' => 1,
            'customer_code' => $customerCode,
            'verified_code' => $verifiedCode,
            'role' => $data['is_company'] ? 'company_client' : 'customer',
        ];

        $data = $this->managerRepository->createCustomer($userData);
        return $this->successResponse($data,'user Successfuly created',200);


    }


        public function getAllCustomers()
    {
        $data = $this->managerRepository->getAllCustomers();
        return $this->successResponse($data,' Successfuly ',200);
    }


    public function deleteCustomer($id)
    {
        $customer = $this->managerRepository->findCustomer($id);

        if (!$customer) {
            return $this->errorResponse('customar not found', 404);
        }

        if (!in_array($customer->role, ['customer', 'company_client'])) {
            return $this->errorResponse('you are not Authorized', 403);
        }

        $this->managerRepository->deleteCustomer($customer);
        return $this->successResponse(null,'user Successfuly removed',200);
    }




    public function get_approved_Shipments()
    {

        $shipments = $this->managerRepository->get_approved_Shipments();


        $shipments->load('customer:id,first_name,last_name');


        $data = $shipments->map(function ($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'type' => $shipment->type,
                'customer_id' => $shipment->customer_id,
                'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
                'status' => $shipment->status,
                'declared_parcels_count' => $shipment->declared_parcels_count,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function get_unapproved_Shipments()
    {

        $shipments = $this->managerRepository->get_unapproved_Shipments();


        $shipments->load('customer:id,first_name,last_name');


        $data = $shipments->map(function ($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'type' => $shipment->type,
                'customer_id' => $shipment->customer_id,
                'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
                'status' => $shipment->status,
                'declared_parcels_count' => $shipment->declared_parcels_count,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function getCustomerShipments($customerCode)
    {

        $shipments = $this->managerRepository->getCustomerShipments($customerCode);


        $shipments->load('customer:id,first_name,last_name');


        $data = $shipments->map(function ($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'type' => $shipment->type,
                'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
                'status' => $shipment->status,
                'declared_parcels_count' => $shipment->declared_parcels_count,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function rejectShipment(array $data)
    {
        $data = $this->managerRepository->rejectShipment(
            $data['shipment_id'],
            $data['cancellation_reason']
        );

        return $this->successResponse($data,' Successfuly ',200);
    }


    public function getRejectedShipments()
    {
        $shipments = $this->managerRepository->getRejectedShipments();


        $shipments->load('customer:id,first_name,last_name');


        $data = $shipments->map(function ($shipment) {
            return [
                'tracking_number' => $shipment->tracking_number,
                'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
                'type' => $shipment->type,
                'declared_parcels_count' => $shipment->declared_parcels_count,
                'status' => $shipment->status,
                'cancellation_reason' => $shipment->cancellation_reason,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function getShipmentById($shipmentId)
    {
        $shipment = $this->managerRepository->getShipmentById($shipmentId);


        $shipment->load('customer:id,first_name,last_name');


        $data = [
            'id' => $shipment->id,
            'tracking_number' => $shipment->tracking_number,
            'type' => $shipment->type,
            'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
            'supplier_name' => $shipment->supplier_name,
            'supplier_number' => $shipment->supplier_number,
            'status' => $shipment->status,
            'declared_parcels_count' => $shipment->declared_parcels_count,
            'actual_parcels_count' => $shipment->actual_parcels_count,
            'notes' => $shipment->notes,
            'created_at' => $shipment->created_at
        ];

        return $this->successResponse($data,' Successfuly ',200);
    }

    private function generateQRCode($shipmentId, $invoiceId)
    {
        $qrData = json_encode([
            'shipment_id' => $shipmentId
        ]);

        $filename = Str::uuid() . '.svg';
        $qrPath = '/uploads/qr_codes/' . $filename;

        if (!file_exists(public_path('/uploads/qr_codes'))) {
            mkdir(public_path('/uploads/qr_codes'), 0777, true);
        }

        QrCodeGenerator::size(300)
            ->errorCorrection('H')
            ->generate($qrData, public_path($qrPath));

        $qrCodeData = [
            'shipment_id' => $shipmentId,
            'invoice_id' => $invoiceId,
            'qr_code_path' => $qrPath,
            'qr_code_data' => $qrData
        ];

        return $this->managerRepository->createQRCode($qrCodeData);
    }



    private function generateInvoiceNumber()
    {

        $lastInvoice = $this->managerRepository->getLastInvoice();

        if (!$lastInvoice) {
            $number = 1;
        } else {
            $lastNumber = (int) substr($lastInvoice->invoice_number, 4);
            $number = $lastNumber + 1;
        }


        return 'INV-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function createInvoice(array $data)
    {

        $invoiceNumber = $this->generateInvoiceNumber();


        $totalAmount = $data['amount'];
        if ($data['includes_tax'] && isset($data['tax_amount'])) {
            $totalAmount += $data['tax_amount'];
        }


        $invoiceData = [
            'customer_id' => $data['customer_id'],
            'shipment_id' => $data['shipment_id'],
            'invoice_number' => $invoiceNumber,
            'amount' => $data['amount'],
            'includes_tax' => $data['includes_tax'],
            'tax_amount' => $data['includes_tax'] ? $data['tax_amount'] : null,
            'total_amount' => $totalAmount,
            'payable_at' => $data['payable_at'],
            'status' => 'not_paid'
        ];


        $invoice = $this->managerRepository->createInvoice($invoiceData);

        $this->managerRepository->updateShipmentStatus($data['shipment_id'], 'in_the_way');

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function getInvoiceDetails($invoice_id)
    {
        $invoice = $this->managerRepository->getInvoiceWithDetails($invoice_id);

        if (!$invoice) {
            return $this->errorResponse('Invoice not found', 404);
        }

        // Check if QR code exists and generate if not
        if (!$invoice->qrcode) {
            $qrCode = $this->generateQRCode($invoice->shipment_id, $invoice->id);
            $qrCodePath = $qrCode->qr_code_path;
        } else {
            $qrCodePath = $invoice->qrcode->qr_code_path;
        }

        $data = [
            'invoice_details' => [
                'id' => $invoice->id,
                'payable_at' => date('d/m/Y', strtotime($invoice->payable_at)),
                'invoice_number' => $invoice->invoice_number,
                'amount' => $invoice->amount,
                'name' => $invoice->customer->first_name . ' ' . $invoice->customer->last_name,
                'phone' => $invoice->customer->phone,
                'customer_code' => $invoice->customer->customer_code,
                'declared_parcels_count' => $invoice->shipment->declared_parcels_count,
                'supplier_name' => $invoice->shipment->supplier_name,
                'qr_code' => $qrCodePath,
            ],


            'tax_amount' => $invoice->tax_amount,
            'total_amount' => $invoice->total_amount,
            'includes_tax' => $invoice->includes_tax,
            'status' => $invoice->status,
            'created_at' => date('d/m/Y', strtotime($invoice->created_at)),
        ];

        return $this->successResponse($data, 'Successfully retrieved invoice details', 200);
    }


}



