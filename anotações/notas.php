//filter_var($email, FILTER_VALIDATE_EMAIL) >> verifica se um email é valido 

//http_response_code($status) >> Isso define o status HTTP da resposta.
//header('Content-Type: application/json; charset=utf-8');
//"O conteúdo que estou enviando é JSON."

//echo json_encode($data) >>pega um array PHP e transforma em JSON.
//JSON_UNESCAPED_UNICODE >>Faz com que caracteres especiais continuem aparecendo normalmente.

//JSON_UNESCAPED_SLASHES >>>Evita que as barras / sejam escapadas desnecessariamente.


$matchedUri = regularExpressionMatchArrayRooutes($uri, $routes);
