<?php
require_once __DIR__ . '/../core/mailer.php';
require_once __DIR__ . '/InvoicePdfClass.php';

class OrderEmailClass
{
    public function sendOrderConfirmation($order)
    {
        if (!is_array($order) || !filter_var($order['shipping_email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            error_log('Order confirmation email skipped: invalid order recipient.');
            return false;
        }

        $escape = static function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };
        $supportEmail = app_setting('MAIL_SUPPORT_EMAIL');
        if (!is_string($supportEmail) || !filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
            $supportEmail = app_setting('MAIL_FROM');
        }
        $supportLine = is_string($supportEmail) && filter_var($supportEmail, FILTER_VALIDATE_EMAIL)
            ? '<p style="margin:0;color:#687386">Questions? Contact <a href="mailto:' . $escape($supportEmail) . '" style="color:#e85d54">'
                . $escape($supportEmail) . '</a>.</p>'
            : '<p style="margin:0;color:#687386">Questions? Reply to this email for support.</p>';
        $rows = '';
        foreach ($order['items'] as $item) {
            $lineTotal = (int) $item['qty'] * (float) $item['unit_price'];
            $rows .= '<tr><td style="padding:12px;border-bottom:1px solid #e5e9f0">'
                . $escape($item['product_title']) . '</td><td style="padding:12px;text-align:right;border-bottom:1px solid #e5e9f0">'
                . (int) $item['qty'] . '</td><td style="padding:12px;text-align:right;border-bottom:1px solid #e5e9f0">'
                . $escape(format_ghs_price($item['unit_price'])) . '</td><td style="padding:12px;text-align:right;border-bottom:1px solid #e5e9f0">'
                . $escape(format_ghs_price($lineTotal)) . '</td></tr>';
        }

        $body = '<!doctype html><html lang="en"><body style="margin:0;padding:24px;background:#f3f5f8;font-family:Arial,sans-serif;color:#17243b">'
            . '<table role="presentation" style="width:100%;max-width:680px;margin:0 auto;background:#fff;border-collapse:collapse">'
            . '<tr><td style="padding:24px 30px;background:#111c35;color:#fff"><div style="font-size:26px;font-weight:bold;color:#ff756b">Shoppn</div>'
            . '<div style="margin-top:6px;font-size:12px;letter-spacing:1px">ORDER CONFIRMATION</div></td></tr>'
            . '<tr><td style="padding:28px 30px"><h1 style="margin:0 0 12px;font-size:22px">Thank you for your order, '
            . $escape($order['shipping_name']) . '.</h1><p style="margin:0;color:#687386">Your payment was received and your order is confirmed.</p>'
            . '<table role="presentation" style="width:100%;margin:22px 0;border-collapse:collapse;background:#f7f8fa">'
            . '<tr><td style="padding:12px;color:#687386">Invoice</td><td style="padding:12px;text-align:right"><strong>#'
            . (int) $order['invoice_no'] . '</strong></td></tr><tr><td style="padding:12px;color:#687386">Order date</td><td style="padding:12px;text-align:right">'
            . $escape($order['created_at'] ?? $order['order_date'] ?? '') . '</td></tr><tr><td style="padding:12px;color:#687386">Payment reference</td><td style="padding:12px;text-align:right">'
            . $escape($order['payment_reference'] ?? 'Unavailable') . '</td></tr></table>'
            . '<table role="presentation" style="width:100%;border-collapse:collapse"><thead><tr style="background:#eef2f7;text-align:left">'
            . '<th style="padding:12px">Product</th><th style="padding:12px;text-align:right">Qty</th><th style="padding:12px;text-align:right">Unit price</th>'
            . '<th style="padding:12px;text-align:right">Total</th></tr></thead><tbody>' . $rows . '</tbody></table>'
            . '<p style="margin:20px 0;text-align:right;font-size:18px"><strong>Order total: '
            . $escape(format_ghs_price($order['total_amount'])) . '</strong></p>'
            . '<div style="padding:18px;background:#f7f8fa"><strong>Delivery details</strong><p style="margin-bottom:0">'
            . $escape($order['shipping_name']) . '<br>' . $escape($order['shipping_address']) . ', '
            . $escape($order['shipping_city']) . ', ' . $escape($order['shipping_country']) . '<br>'
            . $escape($order['shipping_contact']) . '<br>' . $escape($order['shipping_email']) . '</p></div>'
            . '<div style="margin-top:24px">' . $supportLine . '</div>'
            . '<p style="margin:24px 0 0;color:#687386">Thank you for shopping with Shoppn.</p></td></tr></table>'
            . '</body></html>';

        $subject = 'Order confirmation - invoice ' . (int) $order['invoice_no'];

        try {
            $pdf = (new InvoicePdfClass())->generate($order);
        } catch (Throwable $exception) {
            error_log('Failed to generate invoice PDF for order ' . (int) $order['order_id'] . ': ' . $exception->getMessage());
            return false;
        }

        $attachmentName = 'invoice-' . (int) $order['invoice_no'] . '.pdf';
        if (!send_app_email($order['shipping_email'], $subject, $body, true, [[
            'name' => $attachmentName,
            'content' => $pdf,
            'type' => 'application/pdf',
        ]])) {
            error_log('Failed to send order confirmation email for order ' . (int) $order['order_id'] . '.');
            return false;
        }

        return true;
    }
}
