<?php
// api/giang_vien_diem_action.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'get_danh_sach_diem';
        $maLopHp = trim($_GET['ma_lop_hp'] ?? '');
        $maGV = trim($_GET['ma_gv'] ?? 'GV001');

        if ($action === 'get_danh_sach_lop') {
            try {
                $sql = "
                    SELECT 
                        lhp.ma_lhp AS \"MaLop\",
                        mh.ten_mon AS \"TenMonHoc\",
                        lhp.ma_lhp AS \"TenLopHp\",
                        COUNT(dk.id) AS \"SoHocVien\"
                    FROM lop_hoc_phan lhp
                    LEFT JOIN mon_hoc mh ON lhp.ma_mon = mh.ma_mon
                    LEFT JOIN dang_ky_hoc_phan dk ON lhp.ma_lhp = dk.ma_lhp
                    WHERE lhp.ma_gv = :ma_gv OR :ma_gv = 'ALL'
                    GROUP BY lhp.ma_lhp, mh.ten_mon
                    ORDER BY lhp.ma_lhp ASC
                ";
                $stmt = $db->prepare($sql);
                $stmt->execute([':ma_gv' => $maGV]);
                $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($classes)) {
                    // Fallback to fetch all classes if teacher has no assigned classes
                    $stmtAll = $db->query("
                        SELECT 
                            lhp.ma_lhp AS \"MaLop\",
                            mh.ten_mon AS \"TenMonHoc\",
                            lhp.ma_lhp AS \"TenLopHp\",
                            COUNT(dk.id) AS \"SoHocVien\"
                        FROM lop_hoc_phan lhp
                        LEFT JOIN mon_hoc mh ON lhp.ma_mon = mh.ma_mon
                        LEFT JOIN dang_ky_hoc_phan dk ON lhp.ma_lhp = dk.ma_lhp
                        GROUP BY lhp.ma_lhp, mh.ten_mon
                        ORDER BY lhp.ma_lhp ASC
                    ");
                    $classes = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
                }

                echo json_encode(['success' => true, 'classes' => $classes], JSON_UNESCAPED_UNICODE);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        if ($action === 'get_danh_sach_diem') {
            try {
                $keyword = trim($_GET['search'] ?? '');
                $page = max(1, (int)($_GET['page'] ?? 1));
                $perPage = max(1, (int)($_GET['per_page'] ?? 10));

                $where = "WHERE dk.ma_lhp = :ma_lhp";
                $params = [':ma_lhp' => $maLopHp];

                if (!empty($keyword)) {
                    $where .= " AND (sv.ma_sv ILIKE :kw OR sv.ho_ten ILIKE :kw)";
                    $params[':kw'] = '%' . $keyword . '%';
                }

                // Total record count
                $countSql = "
                    SELECT COUNT(*) 
                    FROM dang_ky_hoc_phan dk
                    JOIN sinh_vien sv ON dk.ma_sv = sv.ma_sv
                    $where
                ";
                $stmtCount = $db->prepare($countSql);
                $stmtCount->execute($params);
                $totalRecords = (int)$stmtCount->fetchColumn();

                $totalPages = max(1, (int)ceil($totalRecords / $perPage));
                $page = min($page, $totalPages);
                $offset = ($page - 1) * $perPage;

                // Main data select
                $sql = "
                    SELECT 
                        sv.ma_sv AS \"MSSV\",
                        sv.ho_ten AS \"HoTen\",
                        COALESCE(lsv.ten_lop, sv.ma_lop_sv, 'N/A') AS \"LopSinhHoat\",
                        COALESCE(d.diem_chuyen_can, 0.0) AS \"DiemCC\",
                        COALESCE(d.diem_giua_ky, 0.0) AS \"DiemGK\",
                        COALESCE(d.diem_cuoi_ky, 0.0) AS \"DiemCK\",
                        COALESCE(d.diem_tong_ket, 0.0) AS \"TongKet\",
                        dk.id AS \"DangKyId\"
                    FROM dang_ky_hoc_phan dk
                    JOIN sinh_vien sv ON dk.ma_sv = sv.ma_sv
                    LEFT JOIN lop_sinh_vien lsv ON sv.ma_lop_sv = lsv.ma_lop_sv
                    LEFT JOIN diem d ON dk.id = d.dang_ky_id
                    $where
                    ORDER BY sv.ma_sv ASC
                    LIMIT :limit OFFSET :offset
                ";
                $stmt = $db->prepare($sql);
                foreach ($params as $k => $v) {
                    $stmt->bindValue($k, $v);
                }
                $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as &$r) {
                    $r['TongKet'] = tinhTongKet($r['DiemCC'], $r['DiemGK'], $r['DiemCK']);
                    $r['XepLoai'] = xepLoaiDiem($r['TongKet']);
                }

                // Calculate Statistics for Class
                $statsSql = "
                    SELECT 
                        COALESCE(d.diem_chuyen_can, 0.0) AS cc,
                        COALESCE(d.diem_giua_ky, 0.0) AS gk,
                        COALESCE(d.diem_cuoi_ky, 0.0) AS ck,
                        sv.ho_ten, sv.ma_sv
                    FROM dang_ky_hoc_phan dk
                    JOIN sinh_vien sv ON dk.ma_sv = sv.ma_sv
                    LEFT JOIN diem d ON dk.id = d.dang_ky_id
                    WHERE dk.ma_lhp = :ma_lhp
                ";
                $stmtStats = $db->prepare($statsSql);
                $stmtStats->execute([':ma_lhp' => $maLopHp]);
                $allClassRows = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

                $siSo = count($allClassRows);
                $tongDiem = 0;
                $soDat = 0;
                $maxDiem = -1;
                $topSv = 'N/A';

                foreach ($allClassRows as $cRow) {
                    $tk = tinhTongKet($cRow['cc'], $cRow['gk'], $cRow['ck']);
                    $tongDiem += $tk;
                    if ($tk >= 5.0) $soDat++;
                    if ($tk > $maxDiem) {
                        $maxDiem = $tk;
                        $topSv = $cRow['ho_ten'] . ' (' . $cRow['ma_sv'] . ')';
                    }
                }

                $stats = [
                    'si_so' => $siSo,
                    'diem_tb' => $siSo > 0 ? round($tongDiem / $siSo, 1) : 0.0,
                    'so_dat' => $soDat,
                    'ty_le_dat' => $siSo > 0 ? round(($soDat / $siSo) * 100, 1) : 0.0,
                    'diem_cao_nhat' => $maxDiem >= 0 ? round($maxDiem, 1) : 0.0,
                    'hoc_vien_top' => $topSv
                ];

                echo json_encode([
                    'success' => true,
                    'items' => $rows,
                    'stats' => $stats,
                    'total_records' => $totalRecords,
                    'total_pages' => $totalPages,
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'start_index' => $offset + 1
                ], JSON_UNESCAPED_UNICODE);

            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $action = $input['action'] ?? 'save_grades';
        $maLopHp = trim($input['ma_lop_hp'] ?? $input['ma_lhp'] ?? $input['ma_lop'] ?? '');

        if ($action === 'save_grades') {
            try {
                $grades = $input['grades'] ?? [];
                $successCount = 0;

                foreach ($grades as $mssv => $scoreData) {
                    $cc = round(floatval($scoreData['cc'] ?? 0), 1);
                    $gk = round(floatval($scoreData['gk'] ?? 0), 1);
                    $ck = round(floatval($scoreData['ck'] ?? 0), 1);
                    $tk = tinhTongKet($cc, $gk, $ck);

                    // Find dang_ky_id
                    $stmtDk = $db->prepare("SELECT id FROM dang_ky_hoc_phan WHERE ma_lhp = :ma_lhp AND ma_sv = :ma_sv");
                    $stmtDk->execute([':ma_lhp' => $maLopHp, ':ma_sv' => $mssv]);
                    $dkId = $stmtDk->fetchColumn();

                    if (!$dkId) {
                        // Create dang_ky if not exist
                        $stmtInsDk = $db->prepare("INSERT INTO dang_ky_hoc_phan (ma_sv, ma_lhp) VALUES (:sv, :lhp) RETURNING id");
                        $stmtInsDk->execute([':sv' => $mssv, ':lhp' => $maLopHp]);
                        $dkId = $stmtInsDk->fetchColumn();
                    }

                    // Upsert diem
                    $stmtCheckDiem = $db->prepare("SELECT id FROM diem WHERE dang_ky_id = :dk_id");
                    $stmtCheckDiem->execute([':dk_id' => $dkId]);
                    if ($stmtCheckDiem->fetchColumn()) {
                        $stmtUpd = $db->prepare("
                            UPDATE diem 
                            SET diem_chuyen_can = :cc, diem_giua_ky = :gk, diem_cuoi_ky = :ck, diem_tong_ket = :tk, updated_at = NOW()
                            WHERE dang_ky_id = :dk_id
                        ");
                        $stmtUpd->execute([':cc' => $cc, ':gk' => $gk, ':ck' => $ck, ':tk' => $tk, ':dk_id' => $dkId]);
                    } else {
                        $stmtIns = $db->prepare("
                            INSERT INTO diem (dang_ky_id, diem_chuyen_can, diem_giua_ky, diem_cuoi_ky, diem_tong_ket, nguoi_nhap)
                            VALUES (:dk_id, :cc, :gk, :ck, :tk, 'GV001')
                        ");
                        $stmtIns->execute([':dk_id' => $dkId, ':cc' => $cc, ':gk' => $gk, ':ck' => $ck, ':tk' => $tk]);
                    }
                    $successCount++;
                }

                echo json_encode(['success' => true, 'message' => "Đã lưu thành công điểm cho $successCount sinh viên!"], JSON_UNESCAPED_UNICODE);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        if ($action === 'add_student') {
            try {
                $mssv = trim($input['mssv'] ?? '');
                $hoTen = trim($input['ho_ten'] ?? '');
                $cc = round(floatval($input['diem_cc'] ?? 0), 1);
                $gk = round(floatval($input['diem_gk'] ?? 0), 1);
                $ck = round(floatval($input['diem_ck'] ?? 0), 1);
                $tk = tinhTongKet($cc, $gk, $ck);

                if (!$mssv || !$hoTen) {
                    echo json_encode(['success' => false, 'message' => 'MSV và Họ tên là bắt buộc.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Check or insert sinh_vien
                $stmtSv = $db->prepare("SELECT ma_sv FROM sinh_vien WHERE ma_sv = :ma_sv");
                $stmtSv->execute([':ma_sv' => $mssv]);
                if (!$stmtSv->fetch()) {
                    $stmtInsSv = $db->prepare("INSERT INTO sinh_vien (ma_sv, ho_ten, ma_lop_sv, trang_thai) VALUES (:sv, :ten, 'K65-KTPM-A', 'DANG_HOC')");
                    $stmtInsSv->execute([':sv' => $mssv, ':ten' => $hoTen]);
                }

                // Check or insert dang_ky_hoc_phan
                $stmtDk = $db->prepare("SELECT id FROM dang_ky_hoc_phan WHERE ma_lhp = :lhp AND ma_sv = :sv");
                $stmtDk->execute([':lhp' => $maLopHp, ':sv' => $mssv]);
                $dkId = $stmtDk->fetchColumn();

                if (!$dkId) {
                    $stmtInsDk = $db->prepare("INSERT INTO dang_ky_hoc_phan (ma_sv, ma_lhp) VALUES (:sv, :lhp) RETURNING id");
                    $stmtInsDk->execute([':sv' => $mssv, ':lhp' => $maLopHp]);
                    $dkId = $stmtInsDk->fetchColumn();
                }

                // Upsert diem
                $stmtCheckDiem = $db->prepare("SELECT id FROM diem WHERE dang_ky_id = :dk_id");
                $stmtCheckDiem->execute([':dk_id' => $dkId]);
                if ($stmtCheckDiem->fetchColumn()) {
                    $stmtUpd = $db->prepare("UPDATE diem SET diem_chuyen_can = :cc, diem_giua_ky = :gk, diem_cuoi_ky = :ck, diem_tong_ket = :tk WHERE dang_ky_id = :dk_id");
                    $stmtUpd->execute([':cc' => $cc, ':gk' => $gk, ':ck' => $ck, ':tk' => $tk, ':dk_id' => $dkId]);
                } else {
                    $stmtIns = $db->prepare("INSERT INTO diem (dang_ky_id, diem_chuyen_can, diem_giua_ky, diem_cuoi_ky, diem_tong_ket, nguoi_nhap) VALUES (:dk_id, :cc, :gk, :ck, :tk, 'GV001')");
                    $stmtIns->execute([':dk_id' => $dkId, ':cc' => $cc, ':gk' => $gk, ':ck' => $ck, ':tk' => $tk]);
                }

                echo json_encode(['success' => true, 'message' => "Đã thêm học viên $hoTen ($mssv) và nhập điểm thành công!"], JSON_UNESCAPED_UNICODE);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        if ($action === 'delete_student') {
            try {
                $mssv = trim($input['mssv'] ?? '');
                if (!$mssv || !$maLopHp) {
                    echo json_encode(['success' => false, 'message' => 'Thông tin xóa không hợp lệ.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $stmtDk = $db->prepare("SELECT id FROM dang_ky_hoc_phan WHERE ma_lhp = :lhp AND ma_sv = :sv");
                $stmtDk->execute([':lhp' => $maLopHp, ':sv' => $mssv]);
                $dkId = $stmtDk->fetchColumn();

                if ($dkId) {
                    $db->prepare("DELETE FROM diem WHERE dang_ky_id = :dk_id")->execute([':dk_id' => $dkId]);
                    $db->prepare("DELETE FROM dang_ky_hoc_phan WHERE id = :dk_id")->execute([':dk_id' => $dkId]);
                }

                echo json_encode(['success' => true, 'message' => "Đã xóa sinh viên MSV $mssv khỏi lớp thành công!"], JSON_UNESCAPED_UNICODE);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        break;
}
