<?php

function create_client($conn,$user_id){
    $sql = "INSERT INTO clients
        (user_id)
        VALUES
        (:user_id)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ":user_id" =>$user_id
    ]);

    echo json_encode([
        "message" => "cliente criado com sucesso!"
    ]);


}
