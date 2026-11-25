<?php
/**
 * Input validation + authentication for the reseller HTTP API (api/*.php).
 *
 * SECURITY FIX: these endpoints concatenated user/key straight into SQL after
 * Xss::xssClean(), which strips HTML but does not escape quotes. A request such as
 * user=x' OR (id=3 AND api_status='1') -- -   authenticated as an arbitrary reseller and
 * spent their wallet. Every parameter is now whitelisted before any query runs, and the
 * account is looked up with a prepared statement and the key compared in constant time.
 */

/** Rejects the request unless every given parameter matches its pattern. */
function midhub_api_validate(array $values)
{
    $patterns = array(
        'user'   => '/^[A-Za-z0-9_.@-]{1,50}$/',
        'key'    => '/^[A-Za-z0-9]{6,100}$/',
        'number' => '/^01[3-9]\d{8}$/',
        'amount' => '/^\d{1,6}$/',
        'type'   => '/^\d{1,2}$/',
        'rid'    => '/^\d{1,11}$/',
    );
    foreach ($values as $name => $value) {
        if (!isset($patterns[$name]) || !is_string($value) || !preg_match($patterns[$name], $value)) {
            header('Content-Type: application/json');
            echo json_encode(array('RESPONSE' => 'ERROR : INVALID REQUEST'));
            exit();
        }
    }
}

/**
 * @return array{0:object|null,1:int} [reseller row as object, 1 when authenticated else 0]
 */
function midhub_api_auth($user, $key)
{
    global $connect;
    $stmt = mysqli_prepare($connect, "SELECT * FROM user WHERE user = ? AND active = '1' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $user);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_object($res) : null;
    mysqli_stmt_close($stmt);
    if (!$row || (string) $row->api_key === '' || (string) $row->api_key === '0' || !hash_equals((string) $row->api_key, (string) $key)) {
        return array(null, 0);
    }
    return array($row, 1);
}
