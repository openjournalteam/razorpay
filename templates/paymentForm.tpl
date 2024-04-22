<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redirecting...</title>
  <script>
    function load() {
      document.razorform.submit();
    }
  </script>
</head>

<body onload="load()">
  <h1>Redirecting...</h1>

  <form method="POST" action="https://api.razorpay.com/v1/checkout/embedded" name="razorform" id="razorform">
    <input type="hidden" name="key_id" value="{$key_id}">
    <input type="hidden" name="amount" value="{$amount}">
    <input type="hidden" name="order_id" value="{$order_id}">
    <input type="hidden" name="name" value="{$name}">
    <input type="hidden" name="callback_url" value="{$callback_url}">
    <input type="hidden" name="cancel_url" value="{$cancel_url}">
  </form>
</body>

</html>