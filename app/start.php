<?php

require __DIR__ . '/../vendor/autoload.php';


$paypal = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        'AZ7z5y3rt0tTnl8ToZtYRo9AJsbPcbrQuV01fm0hAXC53BDBLtCrNAwpV4FSBH2ect6pH8OPTdJD56qH',
        'ELBq1gvnUzzcmeS59_veLBzXZuwVv0_b-jY5sAeMPSJME17B5KcTYDWOhyKA0E1Mwv23fP7FstKCBro6'
    )
);