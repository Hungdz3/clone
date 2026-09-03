<?php

function getNotificationStatus($title, $description)
{
    if (empty($title) || empty($description)) {
        return "Thiếu thông tin";
    }

    if (mb_strlen($description, 'UTF-8') >= 100) {
        return "Thông báo chi tiết";
    }

    return "Thông báo ngắn";
}

function getPublishedNotifications(PDO $db, ?int $limit = null): array
{
    $sql = "
        SELECT id, tieu_de AS title, noi_dung AS description,
               file_dinh_kem, ngay_dang
        FROM thong_bao
        WHERE trang_thai = :trang_thai
          AND doi_tuong_nhan IN (:tat_ca, :sinh_vien)
        ORDER BY ngay_dang DESC, id DESC
    ";
    if ($limit !== null) {
        $sql .= ' LIMIT ' . max(1, $limit);
    }

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':trang_thai' => 'DA_GUI',
        ':tat_ca' => 'TAT_CA',
        ':sinh_vien' => 'SINH_VIEN',
    ]);

    return $stmt->fetchAll();
}

function getNotificationImage(array $notification): string
{
    $file = $notification['file_dinh_kem'] ?? '';
    if ($file !== '' && preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $file)) {
        return $file;
    }

    return 'assets/illustration.png';
}
