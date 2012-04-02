(1) Configure database connectivity settings in ./application/config/config.php
(2) Import Database ( ./uplb_xts.sql )
(3) Now connect to your URL.

-----------------------
PayPal Payment Specifics
See controller: ./controllers/paypal.php
-----------------------
* IMPORTANT!!! If you are testing this in your PC (i.e., not web-hosting), payment via 
PayPal requires that your PC is accessible over the internet. That is,
assuming your Internet Service Provider gave your PC or router or any other
device that you use to connect to the ISP the IP address 112.20.34.19,
accessing http://112.20.34.19 from some random computer connected to the Internet
and that is not in your local network will output whatever is served by
the web server you are running. You must explicitly define your IP address
( constant NON_HOSTING_IPADDR ). If you don't
do this, PayPal won't be able to communicate with the application to send
payment details and thus payment won't be processed.

* Merchant Email
This identifies which seller to pay to. ( constant PAYPAL_MERCHANT_EMAIL ).

* Test mode
This identifies whether to submit payment to PayPal SandBox/Developer Site
(no real money involved) or to a real account.
(constant ISPAYPAL_TEST_MODE ).