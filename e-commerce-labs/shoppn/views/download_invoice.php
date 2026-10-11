<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../classes/InvoicePdfClass.php';

$orderId = filter_var($_SESSION['last_paid_order_id'] ?? null, FILTER_VALIDATE_INT);
if ($orderId === false || $orderId === null || $orderId <= 0) {
    redirect('/e-commerce-labs/shoppn/index.php');
}

$order = (new CartController())->getOrderById($orderId);
if (!$order || $order['order_status'] !== 'paid') {
    redirect('/e-commerce-labs/shoppn/index.php');
}

try {
    $pdf = (new InvoicePdfClass())->generate($order);
} catch (Throwable $exception) {
    error_log('Failed to generate downloadable invoice for order ' . $orderId . ': ' . $exception->getMessage());
    http_response_code(500);
    exit('The invoice PDF could not be generated. Please use the print invoice option.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="invoice-' . (int) $order['invoice_no'] . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
