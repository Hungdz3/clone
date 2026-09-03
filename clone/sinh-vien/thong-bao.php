<?php
// thong-bao.php
session_start();
require_once __DIR__ . '/../config/db.php';

$ma_sv = $_SESSION['ma_sv'] ?? 'SV001';
$db = getDB();

$sinh_vien = null;
try {
    $stmt = $db->prepare("
        SELECT sv.ma_sv, sv.ho_ten, lsv.ten_lop, k.ten_khoa
        FROM sinh_vien sv
        JOIN lop_sinh_vien lsv ON sv.ma_lop_sv = lsv.ma_lop_sv
        JOIN nganh n ON lsv.ma_nganh = n.ma_nganh
        JOIN khoa k ON n.ma_khoa = k.ma_khoa
        WHERE sv.ma_sv = :ma_sv
    ");
    $stmt->execute([':ma_sv' => $ma_sv]);
    $sinh_vien = $stmt->fetch();
} catch (Exception $e) {
    // Silent catch
}

if (!$sinh_vien) {
    $sinh_vien = [
        'ma_sv' => $ma_sv,
        'ho_ten' => 'NGUYỄN VĂN A',
        'ten_lop' => 'CNTT D2024A',
        'ten_khoa' => 'Khoa Toán - CNTT'
    ];
}

// Danh sách thông báo
$notices = [
    [
        'id' => 1,
        'tag' => 'ĐĂNG KÝ HỌC',
        'tag_class' => 'tag-registration',
        'title' => 'Thông báo về việc đăng ký học phần Học kỳ 1 năm học 2026 - 2027',
        'date' => '15/08/2026',
        'summary' => 'Phòng Đào tạo thông báo kế hoạch mở cổng đăng ký học phần chính thức dành cho sinh viên khóa K64, K65 và K66 từ ngày 22/08/2026 đến ngày 10/09/2026.',
        'content' => 'Kính gửi toàn thể sinh viên trường Đại học HNDA,<br><br>Căn cứ kế hoạch đào tạo năm học 2026 - 2027, Phòng Đào tạo thông báo lịch đăng ký học phần Học kỳ 1 như sau:<br><br><strong>1. Đối tượng áp dụng:</strong> Sinh viên các khóa K64, K65, K66 hệ chính quy.<br><br><strong>2. Thời gian đăng ký trực tuyến:</strong> Từ 08h00 ngày 22/08/2026 đến 17h00 ngày 10/09/2026.<br><br><strong>3. Nguyên tắc đăng ký:</strong><br>- Sinh viên đăng ký theo đúng chương trình đào tạo của ngành học.<br>- Số tín chỉ đăng ký tối thiểu là 12 TC, tối đa là 24 TC.<br>- Đăng ký qua cổng thông tin sinh viên trực tuyến.<br><br>Yêu cầu sinh viên thực hiện đăng ký đúng thời gian quy định. Mọi thắc mắc xin liên hệ văn phòng Phòng Đào tạo tại tầng 2 nhà A.'
    ],
    [
        'id' => 2,
        'tag' => 'KẾ HOẠCH HỌC TẬP',
        'tag_class' => 'tag-plan',
        'title' => 'Kế hoạch giảng dạy và thời khóa biểu dự kiến Học kỳ 1 năm học 2026 - 2027',
        'date' => '10/08/2026',
        'summary' => 'Chi tiết thời gian bắt đầu năm học mới, thời gian học lý thuyết, thực hành và kế hoạch thi cử học kỳ 1 năm học 2026 - 2027.',
        'content' => 'Nhằm chuẩn bị tốt cho năm học mới, Nhà trường thông báo kế hoạch học tập chi tiết như sau:<br><br><strong>1. Ngày bắt đầu học kỳ:</strong> 07/09/2026.<br><br><strong>2. Thời gian học lý thuyết:</strong> 15 tuần (từ 07/09/2026 đến 20/12/2026).<br><br><strong>3. Nghỉ giữa kỳ:</strong> 1 tuần (dự kiến tuần thứ 8).<br><br><strong>4. Thời gian thi cuối kỳ:</strong> Từ 22/12/2026 đến 15/01/2027.<br><br>Đề nghị các Khoa chuyên môn và sinh viên chú ý theo dõi để thực hiện đúng tiến độ.'
    ],
    [
        'id' => 3,
        'tag' => 'HỌC PHÍ',
        'tag_class' => 'tag-tuition',
        'title' => 'Thông báo nộp học phí Học kỳ 1 (2026-2027) và các chính sách miễn giảm',
        'date' => '05/08/2026',
        'summary' => 'Hướng dẫn chi tiết hình thức nộp học phí qua tài khoản ngân hàng liên kết, hạn nộp học phí và hồ sơ xét miễn giảm học phí.',
        'content' => 'Phòng Kế hoạch - Tài chính thông báo về việc thu học phí Học kỳ 1 năm học 2026-2027:<br><br><strong>1. Định mức học phí:</strong> Theo quy định hiện hành đối với từng khối ngành và số tín chỉ đăng ký.<br><br><strong>2. Thời hạn nộp:</strong> Từ ngày 25/08/2026 đến hết ngày 30/09/2026.<br><br><strong>3. Phương thức nộp:</strong><br>- Sinh viên chuyển khoản qua ngân hàng AgriBank hoặc VietinBank theo số tài khoản định danh cá nhân trên hệ thống của mình.<br>- Tra cứu công nợ học phí tại mục Tài chính trên cổng thông tin.<br><br><em>Lưu ý: Quá thời hạn trên, sinh viên chưa hoàn thành học phí sẽ bị khóa quyền đăng ký thi học kỳ và không được xét học bổng khuyến khích học tập.</em>'
    ],
    [
        'id' => 4,
        'tag' => 'THÔNG BÁO CHUNG',
        'tag_class' => 'tag-general',
        'title' => 'Hướng dẫn sử dụng Cổng thông tin đăng ký học phần mới (Phiên bản 2.0)',
        'date' => '01/08/2026',
        'summary' => 'Hướng dẫn sinh viên các thao tác tìm kiếm lớp học phần, đăng ký, hủy đăng ký, xem lịch học cá nhân trực quan trên Cổng thông tin mới nâng cấp.',
        'content' => 'Cổng thông tin sinh viên HNDA chính thức cập nhật giao diện phiên bản 2.0:<br><br><strong>Các tính năng nổi bật được nâng cấp bao gồm:</strong><br><br>1. <strong>Đăng ký học phần nhanh chóng:</strong> Tra cứu lớp học phần nhanh gọn với bộ lọc từ khóa động và nút đăng ký tức thời.<br>2. <strong>Thời khóa biểu trực quan:</strong> Xem lịch học cá nhân dạng lưới (CSS Grid) chia theo ca sáng/chiều cùng mã màu phân loại môn học.<br>3. <strong>Hồ sơ tích lũy:</strong> Tải trang thông tin sinh viên kèm bảng điểm quy đổi hệ 10 và hệ 4 tự động theo từng kỳ.<br>4. <strong>Hệ thống thông báo Toast & Modal:</strong> Các cảnh báo trùng lịch, trùng môn, đầy sĩ số được hiển thị sinh động và thân thiện hơn.<br><br>Sinh viên vui lòng đọc kỹ tài liệu hướng dẫn hoặc liên hệ đội ngũ hỗ trợ kỹ thuật tại Phòng Máy tính tầng 1 nếu gặp khó khăn trong quá trình thao tác.'
    ]
];

$id_selected = isset($_GET['id']) ? intval($_GET['id']) : 1;
$current_notice = null;
foreach ($notices as $n) {
    if ($n['id'] === $id_selected) {
        $current_notice = $n;
        break;
    }
}
if (!$current_notice && !empty($notices)) {
    $current_notice = $notices[0];
}

require_once 'includes/header.php';
?>

<section class="banner-title-row">
  <h2>THÔNG BÁO TỪ NHÀ TRƯỜNG</h2>
  <p class="subtitle-text">Thông báo kế hoạch giảng dạy, đăng ký học tập & tin tức chung từ Phòng Đào tạo</p>
</section>

<div class="notice-board-layout">
  
  <!-- Cột bên trái: Danh sách các tin thông báo -->
  <div class="notice-list">
    <?php foreach ($notices as $n): 
        $is_active = ($n['id'] === $current_notice['id']);
    ?>
      <a href="thong-bao.php?id=<?= $n['id'] ?>" class="notice-card <?= $is_active ? 'active' : '' ?>">
        <div class="notice-card-header">
          <span class="tag-badge <?= $n['tag_class'] ?>"><?= htmlspecialchars($n['tag']) ?></span>
          <span class="notice-date">📅 <?= htmlspecialchars($n['date']) ?></span>
        </div>
        <h4 class="notice-card-title"><?= htmlspecialchars($n['title']) ?></h4>
        <p class="notice-card-summary"><?= htmlspecialchars($n['summary']) ?></p>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Cột bên phải: Chi tiết thông báo được chọn -->
  <article class="notice-detail-container">
    <?php if ($current_notice): ?>
      <div class="notice-detail-header">
        <span class="tag-badge <?= $current_notice['tag_class'] ?>"><?= htmlspecialchars($current_notice['tag']) ?></span>
        <span class="notice-date">Ngày đăng: <?= htmlspecialchars($current_notice['date']) ?></span>
      </div>
      <h2 class="notice-detail-title"><?= htmlspecialchars($current_notice['title']) ?></h2>
      <hr class="notice-divider">
      <div class="notice-detail-content">
        <?= $current_notice['content'] ?>
      </div>
    <?php else: ?>
      <div class="no-notice-selected">
        <p>Vui lòng chọn một thông báo từ danh sách để xem chi tiết.</p>
      </div>
    <?php endif; ?>
  </article>

</div>

<?php require_once 'includes/footer.php'; ?>
