<?php
    define("registrationId", " user registration id  ");      //visa registration id here
    define("entityId", " entity id ");     //visa entity id here
   
    define("Authorization","Authorization:Bearer Token");   // enter authorization bearer token here
    define("amount", "110.00");     // set price
    define("currency", "SAR");      //  set currency
    define("paymentType", "DB");
    define("standingInstructionSource", "CIT");
    define("standingInstructionMode", "REPEATED");
    define("standingInstructionType", "UNSCHEDULED");
    

        // function to check user is registered or not
    function checkRegistration() {
        $url = "https://eu-test.oppwa.com/v1/checkouts";
        $data = "entityId=".entityId .
                    "&amount=".amount .
                    "&currency=".currency .
                    "&paymentType=".paymentType .
                    "&registrations[0].id=".registrationId.
                    "&standingInstruction.source=".standingInstructionSource .
                    "&standingInstruction.mode=".standingInstructionMode .
                    "&standingInstruction.type=".standingInstructionType;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(Authorization));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($responseData,true);
    }


            // function to get checkout id for first time payment
    function newCheckout() {
        $url = "https://eu-test.oppwa.com/v1/checkouts";
        $data = "entityId=".entityId .
                "&amount=".amount .
                "&currency=".currency .
                "&paymentType=".paymentType .
                "&createRegistration=true";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(Authorization));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($responseData,true);
    }


            // function to check payment status
    function paymentStatus() {
        $url = "https://eu-test.oppwa.com/v1/checkouts/".$_GET['id']."/payment";
        $url .= "?entityId=".entityId;
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(Authorization));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($responseData,true);
    }


    $registrationResponse = checkRegistration();
  

    if (isset($registrationResponse['id'])) {
        if (!isset($_GET['id'])) {
            $checkoutId=$registrationResponse['id'];
            $url= "https://eu-test.oppwa.com/v1/paymentWidgets.js?checkoutId=".$checkoutId;
        } else {
            $paymentStatus=paymentStatus();
            print_r($paymentStatus);
        }  
    } else {
        if (!isset($_GET['id'])) {
            $newCheckout = newCheckout();
            $checkoutId=$newCheckout['id'];
            $url= "https://eu-test.oppwa.com/v1/paymentWidgets.js?checkoutId=".$checkoutId;
        } else { 
            $paymentStatus = paymentStatus();
            print_r($paymentStatus);
        }
    }

    if (!isset($_GET['id'])) {
        echo"
            <html>
                <head>
                    <title>hyper Pay</title>
                </head>
                <body>
                <script>
                    function myfun() {
                        var hyperPayScriptElement=document.createElement('form');
                        hyperPayScriptElement.setAttribute('action','https://dev.kuroit.com/hyperpay/index.php');
                        hyperPayScriptElement.setAttribute('class','paymentWidgets');
                        hyperPayScriptElement.setAttribute('data-brands','VISA MADA');
                        document.body.append(hyperPayScriptElement);

                        var hyperPayScriptElement=document.createElement('script');
                        hyperPayScriptElement.type = 'text/javascript';
                        hyperPayScriptElement.src = '".$url."';
                        document.head.appendChild(hyperPayScriptElement);

                        var wpwlOptions = {
                            paymentTarget:'_top',
                        }
                    }       
                    myfun();
                </script>
                </body>
            </html>";
    }
