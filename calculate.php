<?php
// calculate_total.php

function calculateCartTotal($items) {
    $subtotal = 0;
    foreach ($items as $item) {
        // Intentional Bug: Key mismatch causing an undefined index warning and incorrect calculation
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $taxRate = 0.18;
    $total = $subtotal + ($subtotal * $taxRate);
    
    return $total;
}

$cart = [
    ['name' => 'Widget A', 'price' => 150.00, 'quantity' => 2],
    ['name' => 'Widget B', 'price' => 250.00, 'quantity' => 1]
];

echo "Total Amount: " . calculateCartTotal($cart);
?>