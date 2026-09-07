
<?php

require_once __DIR__  . "/config/connection.php";
//php -S localhost:8000 index.php;
//require_once __DIR__ . "/users.php";



$method = $_SERVER["REQUEST_METHOD"];
$protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" )
    ? "https://"
    : "http://";

$uri = trim($_SERVER["REQUEST_URI"], "/");
$array_uri = explode("/", $uri);
$uri = $array_uri[1];


//criação de usuário
if($method === "POST" && str_ends_with($uri, "users.php")){
    require_once __DIR__ . "/users.php";
    create_user($conn);

}


//listagem de todos os profissionais
if($method === "GET" &&  str_ends_with($uri, "professionals.php")){
    require_once __DIR__ . "/professionals.php";
    $professionals = get_professionals($conn);


    //status da resposta
    http_response_code(200);

    //o conteudo enviado é JSON
    header('Content-Type: application/json; charset=utf-8');


    //Transforma o array PHP em JSON
    echo json_encode(
        $professionals,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}



//listagem de profissional de acordo com o ID
if ($method === "GET" && isset($array_uri[2]) && $array_uri[1] === "professional.php") {

    require_once __DIR__ . "/professionals.php";
    $id = $array_uri[2];
    $professional = get_professional_by_id($conn, $id);


    //se o id não pretence a nenhum profissional ....
    if(!$professional){
        http_response_code(404);

        echo json_encode([
            "message" => "Professional não encontrado!"
        ]);

        exit;
    }


    http_response_code(200);
    echo json_encode(
        $professional,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );


    // buscar profissional
}


//Edição de usuário
if ($method === "PATCH" && isset($array_uri[2]) && $array_uri[1] === "profissional_edit"){

    require_once __DIR__ . "/professionals.php";

    $id = $array_uri[2];
    update_user($conn, $id);
}