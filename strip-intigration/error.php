<!DOCTYPE html>
<html lang="en">

<head>
    <title>Payment Error</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container text-center mt-5">
        <h1 class="text-danger">Payment Failed</h1>
        <p class="mt-3">
            <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "An unexpected error occurred."; ?>
        </p>
        <a href="payment_form.php" class="btn btn-primary mt-3">Back to Payment Form</a>
    </div>
</body>

</html>