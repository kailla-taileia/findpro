<?php

    $secret_key = "789456findpro";

    //função de gerar token
    function generate_token($user_id, $secret_key){

        //definir o token
        $payload = [
            "id" => $user_id,
            "iat" => time(),
            "exp" => time() + (24*60*60)
        ];

        //tranformando em strings
        $json_payload = json_encode($payload);

        //codificando o payload
        $payload_encoded = base64_encode($json_payload);

        //assinatura
        $signature = hash_hmac(
            "sha256",
            $payload_encoded,
            $secret_key
        );

        $token = $payload_encoded . "." . $signature;

        return $token;
    }


    function validate_token($secret_key){

        $headers = getallheaders();

        $authorization = $headers["Authorization"] ?? '';


        if($authorization === ''){
            http_response_code(401);

            echo json_encode([
                "error" => "Token não enviado"
            ]);

            exit;
        }


        if (!str_starts_with($authorization, "Bearer ")) {
            http_response_code(401);

            echo json_encode([
                "error" => "Formato do token inválido"
            ]);

            exit;
        }

        //Retirar o Bearer
        $token = substr($authorization, 7);


        //separar o payload da assinatura
        $parts = explode(".", $token);

        //verificar se existe duas partes
        if(count($parts) !== 2){
            http_response_code(401);

            echo json_encode([
                "error" => "Token inválido"
            ]);

            exit;
        }

        $payload_encoded = $parts[0];
        $signature = $parts[1];


        //recalcular a assinatura
        $expected_signature = hash_hmac(
            "sha256",
            $payload_encoded,
            $secret_key
        );


        //comparando as assinaturas
        if (!hash_equals($expected_signature, $signature)) {
            http_response_code(401);

            echo json_encode([
                "error" => "Token inválido"
            ]);

            exit;
        }

        //recuperando o payload
        $json_payload = base64_decode($payload_encoded);
        $payload = json_decode($json_payload, true);



        //verifcar a expiração
        if($payload["exp"] < time()){
            http_response_code(401);

            echo json_encode([
                "error" => "Token expirado"
            ]);

            exit;
        }

        // Retornar ID do usuário
        return $payload["id"];
    }