<?php
// ============================================
// API - Country/Currency Switcher
// ============================================
session_start();
header('Content-Type: application/json');

$countryCode = $_GET['country'] ?? $_POST['country'] ?? 'AU';
$currencyCode = $_GET['currency'] ?? $_POST['currency'] ?? 'AUD';

$countryMap = [
    'AU' => 'AUD', 'US' => 'USD', 'GB' => 'GBP', 'IN' => 'INR', 
    'AE' => 'AED', 'CA' => 'CAD', 'SG' => 'SGD', 'JP' => 'JPY', 
    'NZ' => 'NZD', 'DE' => 'EUR', 'FR' => 'EUR'
];

$_SESSION['country_code'] = $countryCode;
$_SESSION['currency_code'] = $countryCode;

echo json_encode([
    'success' => true,
    'country' => $countryCode,
    'currency' => $currencyCode,
    'currency_symbol' => ['AUD'=>'A$','USD'=>'$','GBP'=>'£','INR'=>'₹','AED'=>'د.إ','CAD'=>'C$','SGD'=>'S$','JPY'=>'¥','NZD'=>'NZ$','EUR'=>'€'][$currencyCode] ?? '$'
]);
?>
