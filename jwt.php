<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require __DIR__.'/../api/vendor/autoload.php';

class TokenIssuer {

    public function createToken($userId, $isAdmin, $db_conn) {
        $bannedSql = "SELECT * FROM banned WHERE user_id = '$userId' AND data_end > NOW()";
        $bannedResult = $db_conn->query($bannedSql);
        $isBanned = $bannedResult->num_rows > 0 ? true : false;

        $key = "fgHA@41Jda";
        $issuedAt = time();
        $expirationTime = $issuedAt + 10800;

        $payload = array(
            "id" => $userId,
            "isAdmin" => $isAdmin,
            "isBanned" => $isBanned,
            "iat" => $issuedAt,
            "exp" => $expirationTime
        );

        $token = JWT::encode($payload, $key, 'HS256');
        return $token;
    }

    function validateToken($token) {
        $key = "fgHA@41Jda";
        try {
            $token1 = substr($token, 7);
            $decoded = JWT::decode($token1, new Key($key, 'HS256'));
            return (object) [
                'id' => $decoded->id,
                'isAdmin' => $decoded->isAdmin,
                'isBanned' => $decoded->isBanned
            ];
        } catch (Exception $e) {
            return null;
        }
    }
}
?>