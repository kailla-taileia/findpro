<?php
    function review_request($conn, $user_id){
        header("Content_type: application/json; charset-utf-8");

        //identificando o usuário

        $stmt = $conn->prepare("
            SELECT user_type
            FROM users
            WHERE id = :id
        ");

        $stmt->bindValue(":id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        if(!$user || $user["user_type"] !== "client"){
            http_response_code(403);

            echo json_encode([
                "error" => "Apenas clientes podem enviar review de serviço."
            ]);

            exit;
        }


        $data = json_decode(file_get_contents("php://input"), true);

        if($data === null){
            http_response_code(400);
            echo json_encode(["error" => "JSON inválido"]);
            exit;
        }

        if(empty($data)){
            http_response_code(400);
            echo json_encode(["error" => "nenhum dado enviado!"]);
            exit;
        }

        //informações que podem enviar
        $required_fields = [
            "service_request_id",
            "rating",
            "comment"
        ];

        foreach ($required_fields as $field) {
            
            if(!array_key_exists($field,$data)){
                http_response_code(400);

                echo json_encode([
                    "error" => "O campo '$field' é obrigatório."
                ]);

                exit;
            }
        }

        $id_service = $data["service_request_id"];
        $rating = $data["rating"] ?? null;
        $comment = $data["comment"];

        $stmt = $conn->prepare("
            SELECT id
            FROM clients
            WHERE user_id = :user_id
        ");
        
        $stmt->execute([
            ":user_id" => $user_id
        ]);

        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        $client_id = $client["id"];



        //verificação para não avaliar mais que uma vez
        $stmt = $conn->prepare("
            SELECT id
            FROM service_reviews
            WHERE service_request_id = :service_request_id
            AND client_id = :client_id
        ");

        $stmt->execute([
            ":service_request_id" => $id_service,
            ":client_id" => $client_id
        ]);

        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($review) {
            http_response_code(409);

            echo json_encode([
                "error" => "Este serviço já foi avaliado, obrigado pela sua avaliação!"
            ]);

            exit;
        }


        //Tarefa pertence ao client e se o id é de um pedido existente

        $stmt = $conn->prepare("
            SELECT id, professional_id
            FROM service_requests
            WHERE id = :id_service
            AND client_id = :client_id
        ");

        $stmt->execute([
            ":id_service" => $id_service,
            ":client_id" => $client_id
        ]);

        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$service) {
            http_response_code(404);

            echo json_encode([
                "error" => "Solicitação não encontrada ou não pertence a este cliente."
            ]);

            exit;
        }


        //verificação de valor de avaliação

        if(!is_int($rating) || $rating < 1 || $rating > 5){
            http_response_code(400);

            echo json_encode([
                "error" => "A avaliação deve ser um número inteiro entre 1 e 5."
            ]);

            exit;
        }

        try {

            $sql = "INSERT INTO service_reviews(
                service_request_id,
                professional_id,
                client_id,
                rating,
                comment
            ) VALUES(
                :service_request_id,
                :professional_id,
                :client_id,
                :rating,
                :comment
            )";

            $stmt = $conn->prepare($sql);

        $stmt->execute([
            ":service_request_id" => $id_service,
            ":professional_id" => $service["professional_id"],
            ":client_id" => $client_id,
            ":rating" => $rating,
            ":comment" => $comment
        ]);

        } catch (\Throwable $e) {
            http_response_code(500);


            echo json_encode([
            "error" => "Erro interno do servidor $e"
            ]);
        }



    }

