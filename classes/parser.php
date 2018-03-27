<?php
/** Log files parser script */

require_once 'config.php';

$userData = fopen('../log/user.log', 'r') or die('File opening failed');
$logData  = fopen('../log/user_log.log', 'r') or die('File opening failed');

$userKeys = ['ip', 'browser', 'system'];
$logKeys  = ['date', 'time', 'ip', 'url_from', 'url_to'];

$logParser = new LogParser();

$users = $logParser->parse(LogParser::TYPE_USER, $userData, $userKeys);
$logs  = $logParser->parse(LogParser::TYPE_USER_LOG, $logData, $logKeys);

if ($users && $logs) {
    $connection = pg_connect(
        'host=' . DB_HOST
        . ' port=' . DB_PORT
        . ' dbname= ' . DB_NAME
        . ' user=' . DB_USER
        . ' password=' . DB_PASSWORD
    );

    if (!$connection) {
        echo 'Please check your DB credentials';
        exit();
    }

    // ip => id storage for user log request
    $userResult  = [];

    // db rows to insert
    $userRecords = [];
    $logRecords  = [];

    foreach ($users as $user) {
        $userRecords[] = "(nextval('user_id_seq'), '"
            . XssHelper::parse($user['ip']) . "', '"
            . XssHelper::parse($user['browser']) . "', '"
            . XssHelper::parse($user['system']) . "')";
    }

    $sql = "INSERT INTO \"user\" (id, ip, browser, system) VALUES " . implode(',', $userRecords) . " RETURNING id, ip";

    $result = pg_query($connection, $sql);

    while ($row = pg_fetch_row($result)) {
        $userResult[$row[1]] = $row[0];
    }

    foreach ($logs as $log) {
        // check whether we have ip user
        if (!empty($userResult[$log['ip']])) {
            $logRecords[] = "(nextval('user_log_id_seq'), '"
                . $userResult[$log['ip']] . "', '"
                . XssHelper::parse($log['url_from']) . "', '"
                . XssHelper::parse($log['url_to']) . "', '"
                . XssHelper::parse(date($log['date'] . ' ' . $log['time'])) . "')";
        }
    }

    $sql = "INSERT INTO user_log (id, user_id, url_from, url_to, log_date) VALUES " . implode(',', $logRecords);

    $result = pg_query($connection, $sql);

    pg_close($connection);

    if ($result) {
        echo 'Parse process completed';
    } else {
        echo 'Something went wrong';
    }
} else {
    echo 'Nothing added';
}

exit();
