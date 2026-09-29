<?php

    function ordem_request($conn, $user_id){

        header('Content-Type: application/json; charset=utf-8');

        //buscar o tipo de usuário 
        $stmt = $conn->prepare("
            SELECT user_type
            FROM users
            Where id =:id
        ");

        $stmt->bindValue(":id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user || $user["user_type"] !== "client"){
            http_response_code(403);

            echo json_encode([
                "error" => "Apenas clientes podem criar solicitações de serviço."
            ]);

            exit;
        }

        //Lê o corpo da requisição (JSON enviado pelo cliente) e transforma em array PHP.
        $data = json_decode(file_get_contents("php://input"), true);
    
        //verificando se data não é null
        if ($data === null) {
                http_response_code(400);
                echo json_encode(["error" => "JSON inválido"]);
                exit;
        }

        if (empty($data)) {
                http_response_code(400);
                echo json_encode(["error" => "Nenhum dado enviado!"]);
                exit;
        }


        //campos obrigatórios da solicitação
        $required_fields = [
                "professional_id",
                "description", 
                "agreed_price",
                "scheduled_date",
                "scheduled_start_time",
                "scheduled_end_time",
        ];

        foreach ($required_fields as $field){

            if(!array_key_exists($field, $data)){

                http_response_code(400);

                echo json_encode([
                    "error" => "O campo '$field' é orbigatório."
                ]);

                exit;
            }
        }

        //add service_category_id e verificação sobre o profissional
        $professional_id = $data["professional_id"];

        $stmt = $conn->prepare("
            SELECT service_category_id
            FROM professionals
            WHERE id = :professional_id
        ");

        $stmt->bindValue(":professional_id", $professional_id, PDO::PARAM_INT);
        $stmt->execute();

        $professional = $stmt->fetch(PDO::FETCH_ASSOC);


        if(!$professional){
            http_response_code(400);

            echo json_encode([
                "error" => "profissional não encontrado!"
            ]);

            exit;
        }

        if($professional["service_category_id"] === null){
            http_response_code(400);

            echo json_encode([
                "error" => "O profissional não possui uma categoria de serviço cadastrada."
            ]);

            exit;
        }

        $service_category_id = $professional["service_category_id"];


        //verificação sobre a data 
        $today = date("Y-m-d");

        if($data["scheduled_date"] < $today){
            http_response_code(400);

            echo json_encode([
                "error" => "Data inválida!"
            ]);

            exit;
        }

        //verificação de horário
        $start_time = DateTime::createFromFormat(
            "H:i:s",
            $data["scheduled_start_time"]
        );

        $end_time = DateTime::createFromFormat(
            "H:i:s",
            $data["scheduled_end_time"]
        );

        $min_time = new DateTime("08:00:00");
        $max_time = new DateTime("18:00:00");

        if(
        $start_time < $min_time
        || $start_time > $max_time
        || $end_time < $min_time
        || $end_time > $max_time){

            http_response_code(400);

            echo json_encode([
                "error" => "Horário inválido, selecione um horário de serviço válido!"
            ]);

            exit;
        }

        if($start_time >= $end_time){
            http_response_code(400);

            echo json_encode([
                "error" => "Horário inválido para inicio e encerramento de atividade!"
            ]);

            exit;
        }


        //pegar o id client
        $stmt = $conn->prepare("
            SELECT id
            FROM clients
            WHERE user_id = :user_id
        ");

        $stmt->execute([
            ":user_id" => $user_id
        ]);

        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$client){
            http_response_code(400);

            echo json_encode([
                "error" => "Erro ao encontrar cliente"
            ]);

            exit;
        }


        $params = ["client_id" => $client["id"], ];
        $params["service_category_id"] = $service_category_id;

        foreach ($required_fields as $field) {
            $params[$field] = $data[$field];
        }


        
        $sql = "INSERT INTO service_requests(
                client_id,
                professional_id,
                service_category_id,
                description,
                agreed_price,
                scheduled_date,
                scheduled_start_time,
                scheduled_end_time
            ) VALUES (
                :client_id,
                :professional_id,
                :service_category_id,
                :description,
                :agreed_price,
                :scheduled_date,
                :scheduled_start_time,
                :scheduled_end_time
            )";

        try{

            $conn->prepare($sql)->execute($params);

            echo json_encode([
                "message" => "Pedido feito com sucesso!"
            ]);

        }catch(PDOException $e){
            http_response_code(500);
            echo json_encode(["error" =>  $e->getMessage()]);
            exit;
        }

    }