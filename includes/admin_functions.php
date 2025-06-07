<?php
function checkAdminAccess($conn, $userId) {
    try {
        $sql = "SELECT is_admin FROM users WHERE userId = :userId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['is_admin'] == 1;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Admin check error: " . $e->getMessage());
        return false;
    }
}
?>