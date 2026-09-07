<?php

function create_user($conn){

         //file_get_contents("php://input") >> pega exatamente esse JSON como texto.
        //json_decode($json, true) >> Transforme o JSON em um array associativo.
        $data = json_decode(file_get_contents("php://input"), true);


        $first_name = $data["first_name"];
        $last_name = $data["last_name"];
        $email = $data["email"];
        $phone = $data["phone"];
        $user_type = $data["user_type"];

        $clave = $data["password"];
        $password = password_hash($clave, PASSWORD_DEFAULT);

        //verificação para não ir inf inválido

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



        //código mysql para add

        $sql = "INSERT INTO users
                (first_name, last_name, email, password, user_type, phone)
                VALUES
                (:first_name, :last_name, :email, :password, :user_type, :phone )";

        //preparar uma consulta SQL para ser executada no banco de dados.
        $stmt = $conn->prepare($sql);


        //Pegue os valores que eu estou fornecendo e coloque nos parâmetros correspondentes do SQL.
        $stmt -> execute([
                ":first_name" => $first_name,
                ":last_name" => $last_name,
                ":email" => $email,
                ":password" => $password,
                ":user_type" => $user_type,
                ":phone" => $phone
        ]);

        //verificação de tipo de usuário

        if($user_type === "client"){
                require_once __DIR__ . "/clients.php";

                $user_id = $conn->lastInsertId();
                create_client($conn,$user_id);
        
        }elseif($user_type === "professional"){
                require_once __DIR__ . "/professionals.php";

                $user_id = $conn->lastInsertId();
                create_professional($conn,$user_id);
        }else{
                echo json_encode([
                        "message" => "Error ao identificar tipo de user!"
                ]);
        }
}






