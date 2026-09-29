<?php

    $secret_key = "789456findpro";

    //função de gerar token
    function generate_token($user_id, $secret_key){

        //definir oque tem no token
        $payload = [
            "id" => $user_id,
            //quando o token foi criado
            "iat" => time(),
            //o token é válido por 24hrs
            "exp" => time() + (24*60*60)
        ];

        //tranformando em strings
        $json_payload = json_encode($payload);

        //codificando o payload
        $payload_encoded = base64_encode($json_payload);

        //assinatura
        $signature = hash_hmac(
            //algoritmo utilizado
            "sha256",
            //oque quero proteger
            $payload_encoded,
            //é a chave que solo o servidor conhece
            $secret_key
        );

        $token = $payload_encoded . "." . $signature;

        return $token;
    }


    function validate_token($secret_key){

        // Pegando os headers enviados pelo cliente
        $headers = getallheaders();

        // Pegando o header Authorization
        $authorization = $headers["Authorization"] ?? '';


        // Verificar se o cliente enviou o Authorization
        if($authorization === ''){
            http_response_code(401);

            echo json_encode([
                "error" => "Token não enviado"
            ]);

            exit;
        }


        // Verificar se começa com Bearer
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
        //Isso protege contra alguém alterar o token.
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
        //A data de expiração já passou?
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