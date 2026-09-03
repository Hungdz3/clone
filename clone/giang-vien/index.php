<?php
/**
 * Trang Chủ Giảng Viên - HNDA Portal System
 */
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

$maGV = $_SESSION['ma_gv'] ?? 'GV001';
$hoTenGV = $_SESSION['ho_ten'] ?? 'TS. Nguyễn Minh Châu';

$db = getDB();
$gvInfo = null;

try {
    $stmt = $db->prepare("
        SELECT gv.*, k.ten_khoa 
        FROM giao_vien gv
        LEFT JOIN khoa k ON gv.ma_khoa = k.ma_khoa
        WHERE gv.ma_gv = :ma OR gv.tai_khoan_id = :uid
        LIMIT 1
    ");
    $stmt->execute([':ma' => $maGV, ':uid' => $_SESSION['user_id'] ?? 0]);
    $gvInfo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback if query error
}

$displayTen = $gvInfo['ho_ten'] ?? $hoTenGV;
$displayMa = $gvInfo['ma_gv'] ?? 'GV20180045';
$displayKhoa = $gvInfo['ten_khoa'] ?? 'Công nghệ thông tin';
$displayEmail = $gvInfo['email'] ?? 'nguyenminhchau@hnda.edu.vn';
$displaySdt = $gvInfo['so_dien_thoai'] ?? '0983278902';
?>

<div style="max-width: 1240px; margin: 24px auto; padding: 0 20px;">

    <!-- Banner Chào Giảng Viên -->
    <div style="background: #1E3A8A; color: white; padding: 24px 32px; border-radius: 14px; margin-bottom: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 16px rgba(30, 58, 138, 0.15);">
        <div style="background: rgba(255, 255, 255, 0.18); width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 2px solid rgba(255, 255, 255, 0.3);">
            👨‍🏫
        </div>
        <div>
            <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff;">Xin chào, <?= e($displayTen) ?></h2>
            <p style="margin: 4px 0 0 0; font-size: 13px; color: #93c5fd;">Bộ môn: Công nghệ phần mềm • Khoa <?= e($displayKhoa) ?></p>
        </div>
    </div>

    <!-- Hàng 2 cột: Thông tin giảng viên & Thông tin công tác -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
        
        <!-- Cột Trái: THÔNG TIN GIẢNG VIÊN -->
        <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <div style="display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 16px;">
                <span style="font-size: 18px; color: #1E3A8A;">👤</span>
                <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: #1E3A8A; text-transform: uppercase; letter-spacing: 0.3px;">THÔNG TIN GIẢNG VIÊN</h3>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px dashed #f1f5f9;">
                    <span style="color: #64748b; font-weight: 500;">Họ và tên</span>
                    <strong style="color: #0f172a; font-weight: 700;"><?= e($displayTen) ?></strong>
                </div>

                <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px dashed #f1f5f9;">
                    <span style="color: #64748b; font-weight: 500;">Mã giảng viên</span>
                    <strong style="color: #0f172a; font-weight: 700;"><?= e($displayMa) ?></strong>
                </div>

                <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px dashed #f1f5f9;">
                    <span style="color: #64748b; font-weight: 500;">Khoa</span>
                    <strong style="color: #0f172a; font-weight: 700;"><?= e($displayKhoa) ?></strong>
                </div>

                <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px dashed #f1f5f9;">
                    <span style="color: #64748b; font-weight: 500;">Bộ môn</span>
                    <strong style="color: #0f172a; font-weight: 700;">Công nghệ phần mềm</strong>
                </div>

                <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px dashed #f1f5f9;">
                    <span style="color: #64748b; font-weight: 500;">Học vị</span>
                    <strong style="color: #0f172a; font-weight: 700;">Tiến sĩ</strong>
                </div>

                <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px dashed #f1f5f9;">
                    <span style="color: #64748b; font-weight: 500;">Email</span>
                    <strong style="color: #0f172a; font-weight: 700;"><?= e($displayEmail) ?></strong>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b; font-weight: 500;">Số điện thoại</span>
                    <strong style="color: #0f172a; font-weight: 700;"><?= e($displaySdt) ?></strong>
                </div>
            </div>
        </div>

        <!-- Cột Phải: THÔNG TIN CÔNG TÁC HIỆN TẠI -->
        <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <div style="display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 16px;">
                <span style="font-size: 18px; color: #1E3A8A;">🏛️</span>
                <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: #1E3A8A; text-transform: uppercase; letter-spacing: 0.3px;">THÔNG TIN CÔNG TÁC HIỆN TẠI</h3>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px dashed #f1f5f9;">
                    <span style="color: #64748b; font-weight: 500;">Học kỳ</span>
                    <strong style="color: #0f172a; font-weight: 800; font-size: 14px;">HK1 - 2026</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px dashed #f1f5f9;">
                    <span style="color: #64748b; font-weight: 500;">Năm học</span>
                    <strong style="color: #0f172a; font-weight: 800; font-size: 14px;">2026 - 2027</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: #64748b; font-weight: 500;">Trạng thái</span>
                    <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 12px; border: 1px solid #bbf7d0;">
                        ✓ Đang công tác
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Khối Dưới: THÔNG BÁO CHUNG -->
    <div style="background: white; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <div style="display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 16px;">
            <span style="font-size: 18px; color: #1E3A8A;">🔔</span>
            <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: #1E3A8A; text-transform: uppercase; letter-spacing: 0.3px;">THÔNG BÁO CHUNG</h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; transition: all 0.2s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#f8fafc'">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 18px;">📄</span>
                    <span style="font-size: 13.5px; font-weight: 600; color: #1e293b;">Thông báo lịch giảng dạy học kỳ mới HK1 - 2026</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 12px; color: #94a3b8;">18/05/2026</span>
                    <span style="color: #94a3b8; font-weight: 700;">›</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; transition: all 0.2s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#f8fafc'">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 18px;">📄</span>
                    <span style="font-size: 13.5px; font-weight: 600; color: #1e293b;">Thông báo cập nhật thông tin giảng viên hồ sơ cá nhân</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 12px; color: #94a3b8;">10/05/2026</span>
                    <span style="color: #94a3b8; font-weight: 700;">›</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; transition: all 0.2s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#f8fafc'">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 18px;">📄</span>
                    <span style="font-size: 13.5px; font-weight: 600; color: #1e293b;">Thông báo kế hoạch đào tạo và hướng dẫn sinh viên đăng ký học phần</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 12px; color: #94a3b8;">09/05/2026</span>
                    <span style="color: #94a3b8; font-weight: 700;">›</span>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
