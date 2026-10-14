<?php

function create_user($conn){

        $data = json_decode(file_get_contents("php://input"), true);


        $first_name = $data["first_name"];
        $last_name = $data["last_name"];
        $email = $data["email"];
        $phone = $data["phone"];
        $user_type = $data["user_type"];

        $clave = $data["password"];
        $password = password_hash($clave, PASSWORD_DEFAULT);


        if ($user_type !== "client" && $user_type !== "professional" && $user_type === "") {
                echo json_encode([
                        "message" => "Error ao criar user!"
                ]);

                return;
        };

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "E-mail inválido";
                return;
        }




        $sql = "INSERT INTO users
                (first_name, last_name, email, password, user_type, phone)
                VALUES
                (:first_name, :last_name, :email, :password, :user_type, :phone )";

        $stmt = $conn->prepare($sql);


        $stmt -> execute([
                ":first_name" => $first_name,
                ":last_name" => $last_name,
                ":email" => $email,
                ":password" => $password,
                ":user_type" => $user_type,
                ":phone" => $phone
        ]);

       

        if($user_type === "client"){
                require_once __DIR__ . "/../../functions/clients/clients.php";

                $user_id = $conn->lastInsertId();
                create_client($conn,$user_id);
        
        }elseif($user_type === "professional"){
                require_once __DIR__ . "/../../functions/professionals/professionals.php";

                $user_id = $conn->lastInsertId();
                create_professional($conn,$user_id);
        }else{
                echo json_encode([
                        "message" => "Error ao identificar tipo de user!"
                ]);
        }
}








//function LOGIN
function login_user($conn){

        require_once __DIR__ . "/../../config/auth.php";
        $data = json_decode(file_get_contents("php://input"), true);
        

        
        if(!$data){
                http_response_code(400);
                echo json_encode(["error" => "Nenhum dado enviado!"]);
                exit;
        }

        
        if(!isset($data['email']) || !isset($data["password"])){
                http_response_code(400);
                echo json_encode([
                        "error" => "Email e senha são obrigatórios!"
                ]);

                exit;
        }


        $email = $data['email'] ;
        $password = $data["password"];

        //consulta na tabela de users o email;
        $sql = "SELECT * FROM users WHERE email = :email";

        try {
                $stmt = $conn->prepare($sql);

                $stmt->bindValue(":email", $email);
                $stmt->execute();

                $user = $stmt->fetch(PDO::FETCH_ASSOC);


                //verifica se o usuário existe
                if(!$user){
                        http_response_code(401);

                        echo json_encode([
                                "error" => "Email inválidos"
                        ]);

                        exit;
                }
                

                if(!password_verify($password, $user["password"])){
                        http_response_code(401);

                        echo json_encode([
                                "error" => "senha inválidos"
                        ]);

                        exit;
                }

                //token gerado
               $token = generate_token($user["id"], $secret_key);

                http_response_code(200);


                echo json_encode([
                        "message" => "Login realizado com sucesso!",
                        "token" => $token,
                        "user" => [
                                "id" => $user["id"],
                                "first_name" => $user["first_name"],
                                "last_name" => $user["last_name"],
                                "email" => $user["email"],
                                "user_type" => $user["user_type"]
                        ]
                ]);



        } catch (PDOException $e) {
                http_response_code(500);


                echo json_encode([
                "error" => "Erro interno do servidor"
                ]);
        }

}






//function de UPdate de usuários
function update_users($conn, $user_id){

        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents("php://input"), true);
    
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


        $response_messages[] = [];


        //informações da tabela users
        $allowed_fields = [
                "first_name", 
                "last_name", 
                "phone", 
                "password"
        ];
        $data_to_update = [];

        foreach($allowed_fields as $field){
                if(array_key_exists($field, $data)){
                        $data_to_update[$field] = $data[$field];
                }
        }



        //informações da tabela profissional
        $allowed_professional_fields = [
                        "service_category_id",
                        "bio",
                        "pricing_type"
        ];

        $data_professional_to_update = [];


        //tipo de serviço

        if(!isset($data["pricing_type"])){

                http_response_code(400);
                echo json_encode(["error" => "O campo pricing_type é obrigatório"]);
                exit;

        }
        
        if($data["pricing_type"] === "hourly"){
                $allowed_professional_fields[] = "hourly_rate";
        }
        
        if($data["pricing_type"] === "fixed"){
               $allowed_professional_fields[] = "service_rate"; 
        }

        if ($data["pricing_type"] !== "hourly" && $data["pricing_type"] !== "fixed") {

                http_response_code(400);

                echo json_encode([
                        "error" => "pricing_type deve ser 'hourly' ou 'fixed'"
                ]);

                exit;
        }

        foreach ($allowed_professional_fields as $field) {
                if(array_key_exists($field, $data)){
                        $data_professional_to_update[$field] = $data[$field];
                }
        }

        


        if(empty($data_to_update) && empty($data_professional_to_update)){
                http_response_code(400);
                echo json_encode(["error" => "Nenhum campo válido para atualizar"]);
                exit;
        }


        if(!empty($data_to_update)){

                if(isset($data_to_update["password"])){
                        $data_to_update["password"] = password_hash(
                        $data_to_update["password"], PASSWORD_DEFAULT);
                }


                $set_parts = [];
                foreach($data_to_update as $field => $value){
                        $set_parts[] = "$field = :$field";
                }

                $sql = "UPDATE users SET "
                        . implode(", ", $set_parts)
                        . " WHERE id = :id";

                try{
                        $stmt = $conn->prepare($sql);

                        foreach($data_to_update as $field=> $value){
                                $stmt->bindValue(":$field", $value);
                        }

                        $stmt->bindValue(":id", $user_id, PDO::PARAM_INT);

                        $stmt->execute();
                        $response_messages[] = "Usuário atualizado com sucesso!";


                }catch(PDOException $e){
                        http_response_code(500);
                        echo json_encode(["error" => "Erro ao atualizar usuário"]);
                        exit;
                }
        }




        //buscar o tipo de usuário no banco de dados
        $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = :id");
        $stmt->bindValue(":id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $user_type = $user ? $user["user_type"] : null;

        //se for profissional e tivers campos enviados dessa tabela atualiza
        if($user_type === "professional" && !empty($data_professional_to_update)){
        

                // Verifica se foi enviada uma categoria de serviço
                if (array_key_exists("service_category_id", $data_professional_to_update)) {

                        $stmt = $conn->prepare("
                                SELECT id 
                                FROM service_categories 
                                WHERE id = :service_category_id
                        ");

                        $stmt->bindValue(
                                ":service_category_id",
                                $data_professional_to_update["service_category_id"],
                                PDO::PARAM_INT
                        );

                        $stmt->execute();

                        $category = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (!$category) {
                                http_response_code(400);

                                echo json_encode([
                                        "error" => "A categoria de serviço informada não existe."
                                ]);

                                exit;
                        }
                }


                $set_parts = [];
                foreach($data_professional_to_update as $field => $value){
                         $set_parts[] = "$field = :$field";
                }


                $sql = "UPDATE professionals SET " 
                . implode(", ", $set_parts) 
                . " WHERE user_id = :user_id";

                        
                try{

                        $stmt = $conn->prepare($sql);

                        foreach($data_professional_to_update as $field=> $value){
                                $stmt->bindValue(":$field", $value);
                        }

                        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

                        $stmt->execute();
                
                        $response_messages[] = "Perfil profissional atualizado com sucesso!";


                                
                                
                }catch(PDOException $e){
                        http_response_code(500);
                
                        echo json_encode(["error" => "Erro ao atualizar perfil profissional"]);
                        exit;
                }

        }

        http_response_code(200);
        echo json_encode([
                "message" => $response_messages
        ]);


}




//informações do perfil
function perfil_user($conn, $user_id){

        header('Content-Type: application/json; charset=utf-8');

        //tipo de user
        $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = :id");
        $stmt->bindValue(":id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
                http_response_code(404);

                echo json_encode([
                "message" => "Usuário não encontrado"
                ]);

                return;
        }

        $user_type = $user["user_type"];

        //se for profissional
        if($user_type === "professional"){
                 $sql = "SELECT
                        users.first_name,
                        users.last_name,
                        users.email,
                        users.password,
                        users.phone,
                        professionals.bio,
                        professionals.hourly_rate,
                        professionals.service_rate,
                        professionals.pricing_type,
                        professionals.service_category_id
                FROM users
                INNER JOIN professionals
                ON professionals.user_id = users.id
                WHERE users.id = :id";


                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
                $stmt->execute();

                $profile = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$profile) {
                        http_response_code(404);

                        echo json_encode([
                                "message" => "Perfil não encontrado"
                        ]);

                        return;
                }

                $profile["password"] = "********";
                echo json_encode($profile);
                return;
        }


        //se for cliente
        if($user_type === "client"){
                $sql = "SELECT
                        first_name,
                        last_name,
                        email,
                        password,
                        phone
                        FROM users
                        WHERE id = :id";


                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);
                $stmt->execute();

                $profile = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$profile) {
                        http_response_code(404);

                        echo json_encode([
                                "message" => "Perfil não encontrado"
                        ]);

                        return;
                }

                $profile["password"] = "********";
                echo json_encode($profile);
                return;
        }


        http_response_code(500);
        
        echo json_encode(["error" => "Erro ao encontrar perfil"]);
        exit;



}





