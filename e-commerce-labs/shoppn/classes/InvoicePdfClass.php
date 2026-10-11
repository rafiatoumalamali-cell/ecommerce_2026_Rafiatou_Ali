<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdfClass
{
    public function generate($order)
    {
        if (!is_array($order) || empty($order['items']) || !is_array($order['items'])) {
            throw new InvalidArgumentException('Invoice data is incomplete.');
        }

        $escape = static function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };
        $rows = '';
        foreach ($order['items'] as $item) {
            $quantity = (int) ($item['qty'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $rows .= '<tr><td>' . $escape($item['product_title'] ?? '')
                . '</td><td class="number">' . $quantity . '</td><td class="number">'
                . $escape(format_ghs_price($unitPrice)) . '</td><td class="number">'
                . $escape(format_ghs_price($quantity * $unitPrice)) . '</td></tr>';
        }

        $supportEmail = app_setting('MAIL_SUPPORT_EMAIL');
        if (!is_string($supportEmail) || !filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
            $supportEmail = app_setting('MAIL_FROM');
        }
        $supportLine = is_string($supportEmail) && filter_var($supportEmail, FILTER_VALIDATE_EMAIL)
            ? 'For help, contact ' . $escape($supportEmail) . '.'
            : 'For help, contact Shoppn support.';

        $html = '<!doctype html><html lang="en"><head><meta charset="UTF-8"><style>'
            . 'body{font-family:DejaVu Sans,sans-serif;color:#17243b;font-size:12px}'
            . '.brand{background:#111c35;color:#fff;padding:22px 26px}'
            . '.brand h1{margin:0;color:#ff6b61;font-size:24px}.brand p{margin:5px 0 0;color:#fff}'
            . '.meta{width:100%;margin:22px 0;border-collapse:collapse}.meta td{padding:5px 8px;vertical-align:top}'
            . '.meta .label{color:#667085;width:125px}.items{width:100%;border-collapse:collapse;margin-top:14px}'
            . '.items th{background:#eef2f7;text-align:left}.items th,.items td{border:1px solid #d9e0ea;padding:9px}'
            . '.number{text-align:right}.total{text-align:right;font-size:16px;margin-top:18px;color:#111c35}'
            . '.footer{border-top:1px solid #d9e0ea;margin-top:28px;padding-top:12px;color:#667085}'
            . '</style></head><body>'
            . '<header class="brand"><h1>Shoppn</h1><p>PAID INVOICE</p></header>'
            . '<table class="meta"><tr><td class="label">Invoice number</td><td><strong>#'
            . (int) $order['invoice_no'] . '</strong></td><td class="label">Order date</td><td>'
            . $escape($order['created_at'] ?? $order['order_date'] ?? '') . '</td></tr>'
            . '<tr><td class="label">Payment reference</td><td colspan="3">'
            . $escape($order['payment_reference'] ?? 'Unavailable') . '</td></tr>'
            . '<tr><td class="label">Customer</td><td colspan="3">' . $escape($order['shipping_name'] ?? '')
            . '<br>' . $escape($order['shipping_email'] ?? '') . '<br>'
            . $escape($order['shipping_contact'] ?? '') . '</td></tr>'
            . '<tr><td class="label">Delivery address</td><td colspan="3">'
            . $escape($order['shipping_address'] ?? '') . ', ' . $escape($order['shipping_city'] ?? '')
            . ', ' . $escape($order['shipping_country'] ?? '') . '</td></tr></table>'
            . '<table class="items"><thead><tr><th>Product</th><th class="number">Qty</th>'
            . '<th class="number">Unit price</th><th class="number">Total</th></tr></thead><tbody>'
            . $rows . '</tbody></table><p class="total"><strong>Order total: '
            . $escape(format_ghs_price($order['total_amount'] ?? 0)) . '</strong></p>'
            . '<footer class="footer"><p>Payment status: Paid</p><p>' . $supportLine
            . '</p><p>Thank you for shopping with Shoppn.</p></footer></body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
