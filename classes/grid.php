<?php
/** Grid data script */

require_once 'config.php';

$limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 7;
$start = !empty($_GET['start']) ? (int)$_GET['start'] : 0;
$sort  = !empty($_GET['sort'] and in_array($_GET['sort'], ['browser', 'system'])) ? $_GET['sort'] : 'id';
$dir   = !empty($_GET['dir'] and in_array($_GET['dir'], ['ASC', 'DESC'])) ? $_GET['dir'] : 'ASC';

$query = !empty($_GET['query']) ? json_decode($_GET['query'], true) : '';
$query = !empty($query[0]['value']) ? XssHelper::parse($query[0]['value']) : '';

$connection = pg_connect(
    'host=' . DB_HOST
    . ' port=' . DB_PORT
    . ' dbname= ' . DB_NAME
    . ' user=' . DB_USER
    . ' password=' . DB_PASSWORD
);

// user grid items request
$strSQL = "
    SELECT
      u.id,
      u.ip,
      u.browser,
      u.system,
      (
        SELECT url_from
        FROM user_log ul
        WHERE ul.user_id = u.id
        ORDER BY log_date
        LIMIT 1
      ) url_first_from,
      (
        SELECT url_to
        FROM user_log ul
        WHERE ul.user_id = u.id
        ORDER BY log_date DESC
        LIMIT 1
      ) url_last_to,
      (
        SELECT COUNT(DISTINCT url_to)
        FROM user_log ul
        WHERE ul.user_id = u.id
      ) url_unique_to
    FROM \"user\" u
    WHERE u.ip LIKE '%$query%'
    ORDER BY $sort $dir
    OFFSET $start
    LIMIT $limit;
";

$result = pg_query($connection, $strSQL);

// user grid items total count
$resultSQL = "
    SELECT
      COUNT(u.id)
    FROM \"user\" u
    WHERE u.ip LIKE '%$query%';
";

$totalResult = pg_query($connection, $resultSQL);

pg_close($connection);

echo json_encode([
    'success' => true,
    'total'   => $totalResult ? pg_fetch_all($totalResult)[0]['count'] : 0,
    'users'   => $result ? pg_fetch_all($result) : [],
]);
exit();
