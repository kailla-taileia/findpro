<?php

    function respond_service_request($conn, $user_id){

        header("Content-Type: application/json; charset=utf-8");

        //buscar tipo de usuário
        $stmt = $conn->prepare("
            SELECT user_type
            FROM users
            WHERE id = :id
        ");

        $stmt->bindValue(":id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user || $user["user_type"] !== "professional"){
            http_response_code(403);

            echo json_encode([
                "error" => "Apenas profissionais podem alterar solicitações de serviço."
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


        $required_fields = [
            "id_service",
            "status"
        ];

        $allowed_fields = [
            "status"
        ];

        //Verificação de campos obrigtórios
        foreach($required_fields as $field){
                if(!array_key_exists($field, $data)){
                    http_response_code(400);

                    echo json_encode([
                        "error" => "O campo '$field' é obrigatório."
                    ]);

                    exit;
                }
        }

        $id_service = $data["id_service"];
        $status = $data["status"];

        //verificação sobre os valores que podem enviar em status
        $allowed_status = [
            "accepted",
            "rejected"
        ];

        if(!in_array($status, $allowed_status, true)){
            http_response_code(400);

            echo json_encode([
                "error" => "Status inválido."
            ]);

            exit;
        }

 
        //Tarefa pertence ao profissional e se o id é de um pedido existente
        $stmt = $conn->prepare("
            SELECT id
            FROM professionals
            WHERE user_id = :user_id
        ");

        $stmt->execute([
            ":user_id" => $user_id 
        ]);

        $professional = $stmt->fetch(PDO::FETCH_ASSOC);
        $professional_id = $professional["id"];


        $stmt = $conn->prepare("
            SELECT id
            FROM service_requests
            WHERE id = :id_service
            AND professional_id = :professional_id
        ");

        $stmt->execute([
            ":id_service" => $id_service,
            ":professional_id" => $professional_id
        ]);

        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$service) {
            http_response_code(404);

            echo json_encode([
                "error" => "Solicitação não encontrada ou não pertence a este profissional."
            ]);

            exit;
        }

        try {

            $stmt = $conn->prepare("
                UPDATE service_requests
                SET status = :status
                WHERE id = :id_service
                AND professional_id = :professional_id
            ");

            $stmt->execute([
                ":status" => $status,
                ":id_service" => $id_service,
                "professional_id" => $professional_id
            ]);

            echo json_encode([
                "message" => "Status da solicitação atualizado com sucesso."
            ]);
        } catch (\Throwable $e) {
            echo json_encode([
                "error" => "Solicitação não encontrada ou não pertence a este profissional. [ERROR]:$e"
            ]);

            exit;
        }

        

    }

    