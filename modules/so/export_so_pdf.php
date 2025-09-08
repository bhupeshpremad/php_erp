<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Mpdf\Mpdf;

$poId = isset($_GET['po_id']) ? (int) $_GET['po_id'] : 0;
if ($poId <= 0) {
	exit('Invalid PO ID');
}

$stmt = $conn->prepare("SELECT * FROM po_main WHERE id = ?");
$stmt->execute([$poId]);
$po = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$po) {
	exit('PO not found');
}

$stmt = $conn->prepare("SELECT product_code, product_name, quantity, price, total_amount, product_image FROM po_items WHERE po_id = ? ORDER BY id ASC");
$stmt->execute([$poId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rows = '';
$baseUrl = rtrim(BASE_URL, '/') . '/modules/po/uploads/';
$sn = 1;
foreach ($items as $it) {
	$imgTag = '';
	if (!empty($it['product_image'])) {
		$imgTag = '<img src="' . htmlspecialchars($baseUrl . $it['product_image']) . '" style="height:40px;">';
	}
	$rows .= '<tr>' .
		'<td>' . $sn++ . '</td>' .
		'<td>' . htmlspecialchars($it['product_code'] ?? '') . '</td>' .
		'<td>' . htmlspecialchars($it['product_name'] ?? '') . '<br>' . $imgTag . '</td>' .
		'<td>' . htmlspecialchars($it['quantity'] ?? '') . '</td>' .
		'<td>' . htmlspecialchars($it['price'] ?? '') . '</td>' .
		'<td>' . htmlspecialchars($it['total_amount'] ?? '') . '</td>' .
	'</tr>';
}

$html = '<h2 style="text-align:center;">Sell Order</h2>' .
	'<table width="100%" cellspacing="0" cellpadding="6" border="0" style="margin-bottom:10px;">' .
		'<tr><td><strong>SO Number:</strong> ' . htmlspecialchars($po['sell_order_number'] ?? $po['po_number']) . '</td><td><strong>PO Number:</strong> ' . htmlspecialchars($po['po_number']) . '</td></tr>' .
		'<tr><td><strong>Client:</strong> ' . htmlspecialchars($po['client_name']) . '</td><td><strong>Prepared By:</strong> ' . htmlspecialchars($po['prepared_by']) . '</td></tr>' .
		'<tr><td><strong>Order Date:</strong> ' . htmlspecialchars($po['order_date']) . '</td><td><strong>Delivery Date:</strong> ' . htmlspecialchars($po['delivery_date']) . '</td></tr>' .
	'</table>' .
	'<table width="100%" border="1" cellpadding="6" cellspacing="0">' .
		'<thead><tr><th>#</th><th>Code</th><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>' .
		'<tbody>' . $rows . '</tbody>' .
	'</table>';

$mpdf = new Mpdf(['tempDir' => sys_get_temp_dir()]);
$mpdf->SetTitle('SO_' . ($po['sell_order_number'] ?? $po['po_number']));
$mpdf->WriteHTML($html);
$mpdf->Output('SO_' . ($po['sell_order_number'] ?? $po['po_number']) . '.pdf', 'D');

