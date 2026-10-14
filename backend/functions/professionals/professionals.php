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
        professionals.id,
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

    header('Content-Type: application/json; charset=utf-8');
 //buscar o profissional
   $sql = "SELECT
        users.first_name,
        users.last_name,
        professionals.user_id,
        professionals.id AS professional_id,
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
    WHERE users.user_type = 'professional'
    AND professionals.id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    $professional = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$professional){
        http_response_code(500);


        echo json_encode([
        "error" => "Profissional não identificado!"
        ]);

        exit;
    }

    //média de avaliação
    $sql_rating = "SELECT
        AVG(rating) AS average_rating
        FROM service_reviews
        WHERE professional_id = :professional_id";

    $stmt_rating = $conn->prepare($sql_rating);

    $stmt_rating->bindParam(
        ":professional_id",
        $id,
        PDO::PARAM_INT
    );

    $stmt_rating->execute();

    $rating = $stmt_rating->fetch(PDO::FETCH_ASSOC);





    //Dados de reviews
    $sql_reviews = "SELECT
        rating,
        comment,
        DATE_FORMAT(created_at, '%d/%m/%Y') AS created_at
        FROM service_reviews
        WHERE professional_id = :professional_id
        ORDER BY created_at DESC
    ";

    $stmt_reviews = $conn->prepare($sql_reviews);

    $stmt_reviews->bindParam(
        ":professional_id",
        $id,
        PDO::PARAM_INT
    );

    $stmt_reviews->execute();

    $reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);

    if (!$reviews) {
        $professional["reviews_message"] = "Nenhuma avaliação registrada!";
    } else {
        $professional["reviews_message"] = null;
    }


    //informação completa

   
    if ($rating["average_rating"] === null) {
        $professional["average_rating"] = 0;
    } else {
        $professional["average_rating"] = number_format(
            $rating["average_rating"],
            1
        );
    }

    $professional["reviews"] = $reviews;



    echo json_encode($professional);
    return;

}





