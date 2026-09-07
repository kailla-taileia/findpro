<?php

function create_professional($conn,$user_id){

    $sql = "INSERT INTO professionals
        (user_id)
        VALUES
        (:user_id)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ":user_id" =>$user_id
    ]);

    echo json_encode([
        "message" => "profissional criado com sucesso!"
    ]);


}


//listagem de todos os profissionais
function get_professionals($conn){
    $sql = "SELECT
        users.first_name,
        users.last_name,
        professionals.bio,
        professionals.hourly_rate,
        professionals.service_rate,
        professionals.pricing_type
    FROM users
    INNER JOIN professionals
    ON professionals.user_id = users.id
    WHERE users.user_type = 'professional'";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



//buscar profissional pelo ID
function get_professional_by_id($conn, $id){
    $sql = "SELECT
        users.first_name,
        users.last_name,
        professionals.user_id ,
        professionals.id as professional_id,
        professionals.bio,
        professionals.hourly_rate,
        professionals.service_rate,
        professionals.pricing_type
    FROM users
    INNER JOIN professionals
    ON professionals.user_id = users.id
    WHERE users.user_type = 'professional'
    AND professionals.id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}








function update_user($conn, $id){
    //Lê o corpo da requisição (JSON enviado pelo cliente) e transforma em array PHP.
    $data = json_decode(file_get_contents("php://input"), true);
    
    //verificando se data não é null
    if(!$data){
        http_response_code(400);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(["error" => "Nenhum dado enviado!"]);
        exit;
    }


     //Descobrir o user_id a partir do id do profissional
    try {
        $stmt = $conn->prepare(
            "SELECT user_id FROM professionals WHERE id = :id");
 
        $stmt->execute([
            "id" => $id
        ]);

        $profissional = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$profissional){
            http_response_code(404);
            header('Content_Type: application/json; charsert=utf-8');
            echo json_encode(["error" => "Profissional não econtrado!"]);
            exit;
        }

        $user_id = $profissional["user_id"];

    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: applicatin/json; charset=utf-8');
        echo json_encode(["error" => "Erro a buscar profissional"]);
        exit;
    }

    //Filtrar os campos permitidos
    $allowed_fields = ["first_name", "last_name", "phone", "password"];
    $data_to_update = [];

    foreach($allowed_fields as $field){
        if(array_key_exists($field, $data)){
            $data_to_update[$field] = $data[$field];
        }
    }

    if(empty($data_to_update)){
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["error" => "Nenhum campo válido para atualizar"]);
        exit;
    }

    //hashear la contraseña
    if(isset($data_to_update["password"])){
        $data_to_update["password"] = password_hash(
            $data_to_update["password"], PASSWORD_DEFAULT);
    }



    //Atualizar users usando o user_id encontrado
    $set_parts = [];
    foreach($data_to_update as $field => $value){
        $set_parts[] = "$field = :$field";
    }

    $sql = "UPDATE users SET " . implode(", ", $set_parts) . " WHERE id = :id";

    try{
        $stmt = $conn->prepare($sql);

        foreach($data_to_update as $field=> $value){
            $stmt->bindValue(":$field", $value);
        }

        $stmt->bindValue(":id", $user_id, PDO::PARAM_INT);

        $stmt->execute();

        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["message" => "Usuário atualizado com sucesso!"]);


    }catch(PDOExceptin $e){
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["error" => "Erro ao atualizar usuário"]);
    }



}