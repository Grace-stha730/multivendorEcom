<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\EsewaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EsewaPaymentController extends Controller
{
    /** Shows a tiny page that auto-posts the signed form to eSewa. Only the order's owner may open it. */
    public function pay(int $order, EsewaService $esewa)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('user.login')->with('error', 'Please log in to pay for your order.');
        }

        $order = Order::where('user_id', $user->id)->findOrFail($order);

        if ($order->payment_method !== 'E-Sewa' || $order->payment_status === 'Paid' || $order->order_status === 'Cancelled') {
            return redirect()->route('user.order')->with('error', 'This order does not need an eSewa payment.');
        }

        return view('payments.esewa-redirect', $esewa->formFor($order) + ['order' => $order]);
    }

    public function success(Request $request, EsewaService $esewa)
    {
        $order = $esewa->completeFromCallback($request->query('data'));

        return $order
            ? redirect()->route('user.order')->with('success', "Payment received for order {$order->order_number}.")
            : redirect()->route('user.order')->with('error', 'We could not verify your eSewa payment. If money was deducted, contact support with your order number.');
    }

    public function failure()
    {
        return redirect()->route('user.order')->with('error', 'The eSewa payment was cancelled or failed. You can try again from your orders.');
    }
}
