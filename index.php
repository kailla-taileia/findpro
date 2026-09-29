
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
if($method === "POST" && $array_uri[1] === "users.php"){
    require_once __DIR__ . "/functions/users/users.php";
    create_user($conn);

}


//LOGIN
if($method === "POST" && str_ends_with($uri, "login_user")){
    require_once __DIR__ . "/functions/users/users.php";
    login_user($conn);
    
}

if($method === "POST" &&  $array_uri[1] === "service_request"){
    require_once __DIR__ . "/functions/services/service_request.php";
    require_once __DIR__ . "/config/auth.php";

    //valida o token
    $user_id = validate_token($secret_key);
    ordem_request($conn, $user_id);
}


//listagem de todos os profissionais
if($method === "GET" &&  str_ends_with($uri, "professionals.php")){
    require_once __DIR__ . "/functions/professionals/professionals.php";
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

    require_once __DIR__ . "/functions/professionals/professionals.php";
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

}

//mostrar o perfil
if($method === "GET" && $array_uri[1] === "perfil"){

    require_once __DIR__ . "/functions/users/users.php";
    require_once __DIR__ . "/config/auth.php";

    //valida o token
    $user_id = validate_token($secret_key);

    $perfil = perfil_user($conn, $user_id);

}


//mostrar os pedidos de acordo com id de user
if($method === "GET" && $array_uri[1] === "service_request" && $array_uri[2] === "list"){

    require_once __DIR__ . "/functions/services/list_services_request.php";
    require_once __DIR__ . "/config/auth.php";

    //valida o token
    $user_id = validate_token($secret_key);

    $services = list_service_requests($conn, $user_id);

}


//Edição de usuário
if ($method === "PATCH" && $array_uri[1] === "profissional_edit"){

    require_once __DIR__ . "/functions/users/users.php";
    require_once __DIR__ . "/config/auth.php";

    //valida o token
    $user_id = validate_token($secret_key);
    $users_update = update_users($conn, $user_id);
}


//alteração de request de serviço
if($method === "PATCH" && $array_uri[1] === "respond_service_request"){
    require_once __DIR__ . "/functions/services/respond_service_request.php";
    require_once __DIR__ . "/config/auth.php";

    //valida o token
    $user_id = validate_token($secret_key);
    $respond_update = respond_service_request($conn, $user_id);
}

//caminho de cancelar atividade
if ($method === "PATCH" && $array_uri[1] === "service_request" && $array_uri[2] === "cancel"){
    require_once __DIR__ . "/functions/services/manage_service_request.php";
    require_once __DIR__ . "/config/auth.php";

    //valida o token
    $user_id = validate_token($secret_key);
    $cancel_update = service_request_cancel($conn, $user_id);
}

//caminho para alterra estatus para === completed
if($method === "PATCH" && $array_uri[1] === "service_request" && $array_uri[2] === "completed"){
    require_once __DIR__ . "/functions/services/manage_service_request.php";
    require_once __DIR__ . "/config/auth.php";

    //valida o token
    $user_id = validate_token($secret_key);
    $completed_update = service_request_completed($conn, $user_id);
}
