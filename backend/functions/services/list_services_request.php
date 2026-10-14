<?php

function list_service_requests($conn, $user_id){
    try {
        
        $sql = "SELECT user_type
            FROM users
            WHERE id = :user_id";

        $stmt= $conn->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user){
            http_response_code(404);

            echo json_encode([
                "error" => "Usuário não encontrado."
            ]);

            return;
        }



        if($user['user_type'] === 'professional'){

            $sql = "SELECT id
                FROM professionals
                WHERE user_id = :user_id
            ";

            $stmt= $conn->prepare($sql);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                http_response_code(404);

                echo json_encode([
                    "error" => "Perfil profissional não encontrado."
                ]);

                return;
            }

            $profile_id = $profile['id'];
            $column = 'professional_id';

        } elseif ($user['user_type'] === 'client') {
            
        
            $sql = "SELECT id
                FROM clients
                WHERE user_id = :user_id
            ";

            $stmt= $conn->prepare($sql);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                http_response_code(404);

                echo json_encode([
                    "error" => "Perfil do cliente não encontrado."
                ]);

                return;
            }

            $profile_id = $profile['id'];
            $column = 'client_id';

        }else{

            http_response_code(403);

            echo json_encode([
                "error" => "Tipo de usuário inválido."
            ]);

            return;
        }


        $sql = "SELECT *
            FROM service_requests
            WHERE $column = :profile_id
            ORDER BY created_at DESC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':profile_id', $profile_id, PDO::PARAM_INT);
        $stmt->execute();

        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);

        echo json_encode([
            "success" => true,
            "requests" => $requests
        ]);
     
     } catch (\Throwable $e) {
        http_response_code(500);

        echo json_encode([
            "error" => "Erro ao buscar os pedidos. $e"
        ]);
     }
}

     