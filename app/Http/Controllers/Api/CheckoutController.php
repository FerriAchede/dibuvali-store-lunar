<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Lunar\Facades\CartSession;
use App\Http\Controllers\Controller;
use App\Modifiers\CustomShippingModifier;
use Illuminate\Support\Facades\Log;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\Payments;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\OrderInvoiceMail;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        Log::info('Iniciando proceso de checkout', [
            'request_data' => $request->all(),
        ]);

        $validated = $request->validate([
            'payment_driver' => 'required|string',
            'billing_address' => 'required|array',
            'billing_address.first_name' => 'required|string',
            'billing_address.last_name' => 'required|string',
            'billing_address.email' => 'required|email',
            'billing_address.phone' => 'required|string',
            'billing_address.address_1' => 'required|string',
            'billing_address.city' => 'required|string',
            'billing_address.province' => 'required|string',
            'billing_address.postcode' => 'required|string',
            'billing_address.country' => 'required|string',
            'terms_accepted' => 'required|accepted',
        ]);

        $cart = CartSession::current();

        if (!$cart) {
            return response()->json([
                'message' => 'El carrito no está listo para crear un pedido.',
            ], 400);
        }

        // Guardar dirección de facturación
        $country = \Lunar\Models\Country::where('iso2', $validated['billing_address']['country'])->first();
        if (!$country) {
            return response()->json(['message' => 'País no válido.'], 400);
        }

        $address = new \Lunar\Models\Address([
            'first_name' => $validated['billing_address']['first_name'],
            'last_name' => $validated['billing_address']['last_name'],
            'line_one' => $validated['billing_address']['address_1'],
            'city' => $validated['billing_address']['city'],
            'state' => $validated['billing_address']['province'],
            'postcode' => $validated['billing_address']['postcode'],
            'country_id' => $country->id,
            'contact_email' => $validated['billing_address']['email'],
            'contact_phone' => $validated['billing_address']['phone'],
        ]);
        $address->save();

        // Asociar dirección de facturación y envío
        $cart->setBillingAddress($address);
        $cart->setShippingAddress($address); // si es la misma dirección

        app(CustomShippingModifier::class)->handle($cart, function ($cart) {
            return $cart;
        });


        $shippingOptions = ShippingManifest::getOptions($cart);

        $selectedOption = $shippingOptions->firstWhere('identifier', 'fixed-rate');

        if (! $selectedOption) {
            return response()->json(['message' => 'Opción de envío no encontrada.'], 400);
        }

        // Pasa el objeto ShippingOption, no solo el identificador
        $cart->setShippingOption($selectedOption);

        $cart->calculate();
        $cart->save();


        // Procesar pago
        try {
            $driver = Payments::driver($validated['payment_driver']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Método de pago no soportado.',
                'error' => $e->getMessage(),
            ], 400);
        }

        $driver->cart($cart);
        $response = $driver->authorize();

        if (!$response->success) {
            return response()->json([
                'message' => 'No se pudo autorizar el pago.',
                'details' => $response->message ?? 'Error desconocido.',
            ], 400);
        }

        // Crear pedido y marcar fecha
        $order = Order::where('cart_id', $cart->id)->first();

        if (!$order) {
            $order = $cart->createOrder();
        }

        $order->update([
            'placed_at' => now(),
        ]);

        // Registrar transacción
        $order->transactions()->create([
            'success' => true,
            //'refund' => false,
            'type' => 'capture',
            'driver' => $validated['payment_driver'],
            'amount' => $order->total,
            'reference' => $response->reference ?? $validated['payment_driver'],
            'status' => 'paid',
            'notes' => $response->message ?? 'Pago procesado.',
            'card_type' => $validated['payment_driver'],
            'last_four' => $response->last_four ?? null,
        ]);


        // Borrar sesión del carrito
        CartSession::forget();

        $pdf = Pdf::loadView('emails.orders.invoice', [
            'order' => $order
        ])->output();
        
        Mail::to($validated['billing_address']['email'])->send(new OrderInvoiceMail($order, $pdf));
        
        Log::info('Pedido realizado con éxito', [
            'order' => $order,
        ]);
        return response()->json([
            'message' => 'Pedido realizado con éxito.',
            'order_id' => $order->id,
            'reference' => $order->reference,
        ]);
    }
}
