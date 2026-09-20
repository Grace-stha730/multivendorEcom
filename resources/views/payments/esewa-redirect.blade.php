<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redirecting to eSewa</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f3f4f6; display: grid; place-items: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 30px rgb(0 0 0 / .08); text-align: center; max-width: 24rem; }
        button { background: #60bb46; color: #fff; border: 0; padding: .7rem 1.4rem; border-radius: .5rem; font-weight: 600; cursor: pointer; }
        small { color: #6b7280; }
    </style>
</head>
<body>
    <div class="card">
        <h1 style="margin-top:0">Redirecting to eSewa…</h1>
        <p>Order <strong>{{ $order->order_number }}</strong> · Rs. {{ number_format((float) $order->price, 2) }}</p>
        <form id="esewa-form" action="{{ $url }}" method="POST">
            @foreach ($fields as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <noscript><button type="submit">Continue to eSewa</button></noscript>
        </form>
        <small>Sandbox mode: no real money is charged.</small>
    </div>
    <script>document.getElementById('esewa-form').submit();</script>
</body>
</html>
