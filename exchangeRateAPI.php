<?php

function getExchangeRatesFromAPI(string $currency): stdClass
{
    $url = "https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/$currency.json";
    $fallbackUrl = "https://latest.currency-api.pages.dev/v1/currencies/$currency.json";

    $curlHandle = curl_init();

    curl_setopt_array($curlHandle, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    $response = curl_exec($curlHandle);

    if (!$response) {
        curl_setopt($curlHandle, CURLOPT_URL, $fallbackUrl);
        $response = curl_exec($curlHandle);

        if (!$response) {
            curl_close($curlHandle);
            exit("Failed to connect to exchange rate API.\n");
        }
    }

    $httpCode = (curl_getinfo($curlHandle, CURLINFO_HTTP_CODE));
    curl_close($curlHandle);

    if ($httpCode === 404) {
        exit("Could not find the specified original currency ($currency).\n");
    }
    if ($httpCode !== 200) {
        exit("Unable to get exchange rate. HTTP status code - $httpCode.\n");
    }

    return json_decode($response);
}

while (true) {
    echo "Enter the amount and the currency (e.g. '100 EUR').\n";
    $userInput = strtolower(readline("Input - "));
    $userInput = explode(" ", $userInput);
    if (count($userInput) != 2) {
        echo "Invalid input. Input should be a number and a currency name separated by a space.\n";
        continue;
    }

    $value = $userInput[0];
    $originalCurrency = $userInput[1];

    if (!is_numeric($value)) {
        echo "Value must be a number.\n";
        continue;
    }
    if ($value <= 0) {
        echo "Value must be a positive number.\n";
        continue;
    }

    break;
}

while (true) {
    echo "Enter the currency you wish to convert to.\n";
    $targetCurrency = strtolower(readline("Target currency: "));

    if ($targetCurrency === "") {
        echo "Target currency cannot be empty.\n";
        continue;
    }
    break;
}

$exchangeRates = getExchangeRatesFromAPI($originalCurrency);

if (!property_exists($exchangeRates->$originalCurrency, $targetCurrency)) {
    exit("Could not find the target currency to convert to.\n");
}

$conversionRate = $exchangeRates->$originalCurrency->$targetCurrency;

$value = number_format($value, 2);
$convertedValue = number_format($value * $conversionRate, 2);
$originalCurrency = strtoupper($originalCurrency);
$targetCurrency = strtoupper($targetCurrency);

echo "$value $originalCurrency is $convertedValue $targetCurrency.\n";
