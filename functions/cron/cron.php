<?php
    function cron_request_status($conn){

        $stmt = $conn->prepare("
            UPDATE service_requests
            SET status = 'in_progress'
            WHERE status = 'accepted'
                AND TIMESTAMP(
                    scheduled_date,
                    scheduled_start_time
                ) <= now()
        ");

        $stmt->execute();
        return $stmt->rowCount();
    }