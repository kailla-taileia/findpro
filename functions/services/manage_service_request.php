<?php
    function service_request_cancel($conn, $user_id){
          
        header("Content_type: application/json; charset=utf-8");

        if(!$user_id){
            http_response_code(403);

            echo json_encode([
                "error" => "Você precisa estar logado para acessar essa pagina!"
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
            "status",
            "reason"
        ];

        $allowed_fields = [
            "status",
            "reason"
        ];

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
        $reason = $data["reason"];

         //verificação sobre o valor que pode enviar em status
        $allowed_status = [
            "cancelled"
        ];

        if(!in_array($status, $allowed_status, true)){
            http_response_code(400);

            echo json_encode([
                "error" => "Status inválido."
            ]);

            exit;
        }


        $stmt = $conn->prepare("
            SELECT user_type
            FROM users
            WHERE id = :id
        ");

        $stmt->bindValue(":id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        $professional_id = NULL;
        $client_id = NULL;

        if($user["user_type"] === "professional"){
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
        }

        if($user["user_type"] === "client"){
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
        }



        //saber se tem relação com o pedido
        $stmt = $conn->prepare("
            SELECT id, client_id, professional_id, status
            from service_requests
            WHERE id = :request_id
                AND(
                    client_id = :client_id
                    or professional_id = :professional_id
                )
        ");

        $stmt->execute([
            ":request_id" => $id_service,
            ":client_id" => $client_id,
            ":professional_id" => $professional_id
        ]);

        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) {

            http_response_code(403);

            echo json_encode([
                "error" => "Você não tem permissão para alterar este pedido."
            ]);

            exit;
        }

        try {
            
            $stmt = $conn->prepare("
                UPDATE service_requests
                SET status = :status,
                    reason = :reason
                WHERE id = :id_service
                AND (
                    client_id = :client_id
                    OR professional_id = :professional_id
                )
            ");

            $stmt->execute([
                ":status" => $status,
                ":reason" => $reason,
                ":id_service" => $id_service,
                ":client_id" => $client_id,
                ":professional_id" => $professional_id
            ]);

            echo json_encode([
                "message" => "pedido cancelado!"
            ]);

        } catch (\Throwable $e) {
            echo json_encode([
                "error" => "Erro ao cancelar o pedido. [ERROR]:$e"
            ]);

            exit;
        }
        
    }


    function service_request_completed($conn, $user_id){

        header("Content_type: application/json; charset-utf-8");

        if(!$user_id){
            http_response_code(403);

            echo json_encode([
                "error" => "Você precisa estar logado para acessar essa pagina!"
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
            "status",
        ];

        $allowed_fields = [
            "status"
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

        $id_service = $data["id_service"];
        $status = $data["status"];


        $allowed_status= [
            "completed"
        ];


        if(!in_array($status, $allowed_status, true)){
            http_response_code(400);

            echo json_encode([
                "error" => "Status inválido."
            ]);

            exit;
        }

        $stmt = $conn->prepare("
            SELECT id
            FROM professionals
            WHERE user_id = :user_id
        ");

        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $professional = $stmt->fetch(PDO::FETCH_ASSOC);

    
        if(!$professional){
            http_response_code(403);

            echo json_encode([
                "success" => false,
                "message" => "Apenas profissionais podem concluir serviços."
            ]);

            exit;
        }

        $professional_id = $professional["id"];

        try {
            $stmt = $conn->prepare("
                UPDATE service_requests
                SET status = 'completed'
                WHERE id = :service_id
                AND professional_id = :professional_id
                AND status = 'in_progress'
            ");

            $stmt->execute([
                ":service_id" => $id_service,
                ":professional_id"=> $professional_id
            ]);

            if($stmt->rowCount()=== 0){
                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "message" => "Pedido não encontrado, não pertence a você ou não está em andamento."
                ]);

                exit;
            }

            echo json_encode([
                "success" => true,
                "message" => "Serviço concluído com sucesso."
            ]);

        } catch (\Throwable $th) {
            http_response_code(400);

            echo json_encode([
                "error" => "ERROR $th"
            ]);

            exit;
        }

    

        
    }