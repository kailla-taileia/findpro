<?php
    require_once __DIR__  . "/../../config/connection.php";
    require_once __DIR__ . '/cron.php';

    while(true){
        $update = cron_request_status($conn);

        echo "Verificação reaizada. Pedidos atualizados : $updated" . PHP_EOL;

        sleep(60);
    }