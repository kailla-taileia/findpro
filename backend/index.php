
<?php

require_once __DIR__  . "/config/connection.php";


header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

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


//criar um pedido
if($method === "POST" &&  $array_uri[1] === "service_request"){

    if(!isset($array_uri[2])){
        require_once __DIR__ . "/functions/services/service_request.php";
        require_once __DIR__ . "/config/auth.php";

        //valida o token
        $user_id = validate_token($secret_key);
        ordem_request($conn, $user_id);
    }
    
}


//mandar um review de serviço
if($method === "POST" &&  $array_uri[1] === "review"){
    require_once __DIR__ . "/functions/services/review.php";
    require_once __DIR__ . "/config/auth.php";

    //valida o token
    $user_id = validate_token($secret_key);
    review_request($conn, $user_id);
}





//listagem de todos os profissionais
if($method === "GET" &&  str_ends_with($uri, "professionals")){
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
if ($method === "GET" && isset($array_uri[2]) && $array_uri[1] === "professional") {

    require_once __DIR__ . "/functions/professionals/professionals.php";
    $id = $array_uri[2];
    
    get_professional_by_id($conn, $id);

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
if ($method === "PATCH" && $array_uri[1] === "edit"){

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
