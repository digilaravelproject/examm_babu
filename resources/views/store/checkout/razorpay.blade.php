@extends('layouts.site')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-slate-50 pt-20">
    <div class="bg-white p-10 rounded-3xl shadow-xl text-center max-w-md w-full border border-slate-100">
        <div class="relative w-20 h-20 mx-auto mb-6">
            <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
        </div>

        <h2 class="text-2xl font-bold text-slate-900 mb-2">Processing Payment</h2>
        <p class="text-slate-500 mb-6">Please wait while we initialize the secure payment gateway...</p>

        <p class="text-xs text-slate-400">Do not refresh or close this page.</p>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = {
        "key": "{{ $razorpay_key }}",
        "amount": "{{ $amount }}",
        "currency": "{{ $currency }}",
        "name": "{{ $name }}",
        "description": "{{ $description }}",
        "image": "{{ $image }}",
        "order_id": "{{ $razorpay_order_id }}",
        "handler": function (response){
            // Create a form dynamically to submit the response to Laravel
            var form = document.createElement("form");
            form.setAttribute("method", "POST");
            form.setAttribute("action", "{{ $callback_url }}");

            var csrfToken = document.createElement("input");
            csrfToken.setAttribute("type", "hidden");
            csrfToken.setAttribute("name", "_token");
            csrfToken.setAttribute("value", "{{ csrf_token() }}");
            form.appendChild(csrfToken);

            var paymentId = document.createElement("input");
            paymentId.setAttribute("type", "hidden");
            paymentId.setAttribute("name", "razorpay_payment_id");
            paymentId.setAttribute("value", response.razorpay_payment_id);
            form.appendChild(paymentId);

            var orderId = document.createElement("input");
            orderId.setAttribute("type", "hidden");
            orderId.setAttribute("name", "razorpay_order_id");
            orderId.setAttribute("value", response.razorpay_order_id);
            form.appendChild(orderId);

            var signature = document.createElement("input");
            signature.setAttribute("type", "hidden");
            signature.setAttribute("name", "razorpay_signature");
            signature.setAttribute("value", response.razorpay_signature);
            form.appendChild(signature);

            document.body.appendChild(form);
            form.submit();
        },
        "prefill": {
            "name": "{{ $user->name }}",
            "email": "{{ $user->email }}",
            "contact": "{{ $user->phone ?? '' }}"
        },
        "theme": {
            "color": "#0777be" // Uses your Brand Blue
        },
        "modal": {
            "ondismiss": function(){
                window.location.href = "{{ route('payment_failed') }}";
            }
        }
    };

    var rzp1 = new Razorpay(options);

    // Automatically open on load
    window.onload = function(){
        rzp1.open();
    };
</script>
@endsection
