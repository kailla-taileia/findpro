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
        professionals.pricing_type,
        service_categories.service
    FROM users
    INNER JOIN professionals
    ON professionals.user_id = users.id
    LEFT JOIN service_categories
    ON professionals.service_category_id = service_categories.id
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





