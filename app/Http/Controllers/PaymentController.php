<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Http\Request;
use App\Models\ReturnProduct;
use App\Enums\FinePaymentStatus;
use Illuminate\Http\JsonResponse;
use App\Enums\ReturnProductStatus;

class PaymentController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $request->order_id,
                'gross_amount' => $request->gross_amount,
            ],
            'customer_details' => [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'snapToken' => $snapToken,
            ], 200);
        } catch(Exception $err) {
            return response()->json([
                'error' => $err->getMessage()
            ], 500);
        }
    }

    public function callback(Request $request): JsonResponse
    {
        $serverKey = config('services.midtrans.server_key');
        $signatureKey = signatureMidtrans(
            $request->order_id,
            $request->status_code,
            $request->gross_amount,
            $serverKey,
        );

        if ($request->signature_key !== $signatureKey){
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        $return_product = ReturnProduct::query()
            ->where('return_product_code', $request->order_id)
            ->first();

        if (!$return_product){
            return response()->json([
                'message' => 'Pengembalian tidak ditemukan',
            ], 404);
        }

        if (!$return_product->fine){
            return response()->json([
                'message' => 'Denda tidak ditemukan'
            ], 404);
        }

        switch($request->transaction_status){
            case 'settlement':
                $return_product->fine->payment_status = FinePaymentStatus::SUCCESS->value;
                $return_product->fine->save();

                $return_product->status = ReturnProductStatus::RETURNED->value;
                $return_product->save();

                return response()->json([
                    'message' => 'Berhasil melakukan pembayaran',
                ]);
            case 'capture':
                $return_product->fine->payment_status = FinePaymentStatus::SUCCESS->value;
                $return_product->fine->save();

                $return_product->status = ReturnProductStatus::RETURNED->value;
                $return_product->save();

                return response()->json([
                    'message' => 'Berhasil melakukan pembayaran',
                ]);
            case 'pending':
                $return_product->fine->payment_status = FinePaymentStatus::PENDING->value;
                $return_product->fine->save();

                return response()->json([
                    'message' => 'Pembayaran Tertunda',
                ]);
            case 'expire':
                $return_product->fine->payment_status = FinePaymentStatus::FAILED->value;
                $return_product->fine->save();

                return response()->json([
                    'message' => 'Pembayaran kadarluasa',
                ]);
            case 'cancel':
                $return_product->fine->payment_status = FinePaymentStatus::FAILED->value;
                $return_product->fine->save();

                return response()->json([
                    'message' => 'Pembayaran dibatalkan',
                ]);
            default:
                return response()->json([
                    'message' => 'Status transaksi tidak diketahui',
                ], 400);
        }
    }

    public function success(): Response
    {
        return inertia('Payments/Success');
    }
}
