<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class OrderManager extends Controller
{
    function showCheckout()
    {
        return view('checkout');
    }
    function checkoutPost(Request $request)
    {
        $request->validate([
            'address' => 'required',
            'pincode' => 'required',
            'phone' => 'required',
        ]);

        $cartItems = DB::table('cart')
            ->join('products', 'cart.product_id',
                '=', 'products.id')
            ->select('cart.product_id',
                DB::raw('count(*) as quantity'),
                'products.price',
                'products.title'

            )
            ->where('cart.user_id', auth()->user()->id)
            ->groupBy(
                'cart.product_id',
                'products.price',
                'products.title')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect(route('cart.show'))->with('error', 'Cart is empty');
        }

        $productIds = [];
        $quantities = [];
        $totalPrice = 0;
        $lineItems = [];

        foreach ($cartItems as $cartItem) {
            $productIds[] = $cartItem->product_id;
            $quantities[] = $cartItem->quantity;
            $totalPrice += $cartItem->price * $cartItem->quantity;
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $cartItem->title,
                    ],
                    'unit_amount' => $cartItem->price * 100,
                ],
                'quantity' => $cartItem->quantity,
            ];

        }


        $order = new Orders();
        $order->user_id = auth()->user()->id; // Get the authenticated user's ID
        $order->address = $request->address;
        $order->pincode = $request->pincode;
        $order->phone = $request->phone;
        $order->product_id = json_encode($productIds); // You need to assign the product ID here
        $order->total_price = $totalPrice; // Assign the total price
        $order->quantity = json_encode($quantities); // Assign the quantity
        $order->save();
        if ($order->save()) {
            DB::table('cart')->where("user_id", auth()->user()->id)->delete();
            $stripe = new StripeClient(config('app.STRIPE_KEY'));

            $checkoutSession = $stripe->checkout->sessions->create([
                'success_url' => route('payment.success',
                    ['order_id' => $order->id]),
                'cancel_url' => route('payment.error'),
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'customer_email' => auth()->user()->email,
                'metadata' => [
                    'order_id' => $order->id
                ]
            ]);

            return redirect($checkoutSession->url);

        }
        return redirect(route('cart.show'))->with('error', 'Error occurred while processing your order');
    }

    function paymentError()
    {
        return "error";
    }
    function paymentSuccess($order_id)
    {
        return "success" . $order_id;
    }

    // Webhook handler function in OrderManager Controller
    public function webhookStripe(Request $request)
    {
        // Retrieve the Stripe webhook secret key from the config
        $endpointSecret = config('app.STRIPE_WEBHOOK_SECRET');

        // Get the raw POST data
        $payload = $request->getContent();

        // Get the signature from the Stripe-Signature header
        $sigHeader = $request->header('Stripe-Signature');

        try {
            // Construct the Stripe event using the payload and the signature
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (UnexpectedValueException $e) {
            // If there's an error with the payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            // If the signature verification fails
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Process the event for checkout session completed
        if ($event->type == 'checkout.session.completed') {
            // The session object contains information about the payment
            $session = $event->data->object;
            $orderId = $session->metadata->order_id;
            $paymentId = $session->payment_intent;

            // Find the order using the order ID
            $order = Orders::find($orderId);

            if ($order) {
                // Update the order's payment ID and status to 'completed'
                $order->payment_id = $paymentId;
                $order->status = 'payment_completed';
                $order->save();
            }
        }

        // Return a success response
        return response()->json(['status' => 'success']);
    }

    function orderHistory(){

        $orders = Orders::where("user_id", auth()->user()->id)->orderBy('id', "DESC")
            ->paginate(5);

        $orders->getCollection()->transform(function ($order) {
            $productIds = json_decode($order->product_id, associative: true);
            $quantities = json_decode($order->quantity, associative: true);

            $products = Products::whereIn('id', $productIds)->get();

            $order->product_details = $products->map(function ($product) use ($quantities, $productIds) {
                $index = array_search($product->id, $productIds);
                return [
                    'name' => $product->title,
                    'quantity' => $quantities[$index] ?? 0,
                    'price' => $product->price,
                    'slug' => $product->slug,
                    'image' => $product->image,
                ];
            });

            return $order;
        });
        return view("history", compact("orders"));

    }


}
