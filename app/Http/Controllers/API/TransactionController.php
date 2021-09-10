<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TransactionItem;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\CodeCoverage\Report\Xml\Totals;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit',6);
        $status = $request->input('status');

        if ($id){
            $transaction = Transaction::with(['items.product'])->find($id);

            if ($transaction){

                return ResponseFormatter::success(
                    $transaction, 'data berhasil diambil'
                );
            }
            else
            {
                return ResponseFormatter::error(null,'data tranassksi salah', 404);

            }
        }

        $transaction = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);

        if ($status) {
            $transaction->where('status',$status);
        }

        return ResponseFormatter::Success(
            $transaction->paginate($limit),
            'Data List transaksi berhasil diambil'
        );
        
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*id' => 'exists:products,id',
            'total_price' => 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED,SHIPPING,SHIPPED'
        ]);

        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $request->total_price,
            'shipping_price' => $request->shipping_price,
            'status' =>  $request->status,
        ]);

        foreach ($request->items as $product)
        {
            TransactionItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product['id'],
                'transactions_id' => $transaction->id,
                'quantity' => $product['quantity']
            ]);
        }

        return ResponseFormatter::Success(
            $transaction->load('items.product'),
            'Data transaksi berhasil diambil'
        );
    }
}
