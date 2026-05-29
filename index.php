<?php
require_once 'config.php';
$pageTitle = 'الرئيسية';

// ===== فلترة البحث =====
$search   = isset($_GET['search'])   ? clean($conn, $_GET['search'])   : '';
$filter   = isset($_GET['filter'])   ? clean($conn, $_GET['filter'])   : 'all';
$category = isset($_GET['category']) ? (int)$_GET['category']          : 0;

// ===== بناء الاستعلام =====
$where = "WHERE i.status = 'active'";

if ($filter === 'lost')  $where .= " AND i.type = 'lost'";
if ($filter === 'found') $where .= " AND i.type = 'found'";
if ($category > 0)       $where .= " AND i.category_id = $category";
if ($search !== '')      $where .= " AND (i.title LIKE '%$search%' OR i.description LIKE '%$search%' OR i.building LIKE '%$search%')";

// ===== جلب البلاغات =====
$items_result = $conn->query("
    SELECT i.*, c.name_ar AS cat_name, c.icon AS cat_icon, u.full_name
    FROM items i
    JOIN categories c ON i.category_id = c.id
    JOIN users u ON i.user_id = u.id
    $where
    ORDER BY i.created_at DESC
    LIMIT 20
");
$items = $items_result ? $items_result->fetch_all(MYSQLI_ASSOC) : [];

// ===== جلب التصنيفات للفلتر =====
$cats_result = $conn->query("SELECT * FROM categories");
$cats = $cats_result ? $cats_result->fetch_all(MYSQLI_ASSOC) : [];

// ===== إحصائيات بسيطة =====
$total_lost  = $conn->query("SELECT COUNT(*) FROM items WHERE type='lost'  AND status='active'")->fetch_row()[0] ?? 0;
$total_found = $conn->query("SELECT COUNT(*) FROM items WHERE type='found' AND status='active'")->fetch_row()[0] ?? 0;

include 'includes/header.php';
?>

<div class="container">

    <!-- ===== HERO ===== -->
    <div class="hero">
        <h1><i class="fas fa-search-location"></i> منصة تطمن</h1>
        <p>ساعد في إيجاد المفقودات داخل الجامعة — معاً نطمن بعض 🤝</p>
        <div style="display:flex; gap:24px; justify-content:center; margin-bottom:30px; flex-wrap:wrap;">
            <div style="text-align:center;">
                <div style="font-size:28px; font-weight:bold; color:#C5A880;"><?= $total_lost ?></div>
                <div style="font-size:13px; opacity:0.7;">بلاغ مفقود</div>
            </div>
            <div style="width:1px; background:#C5A880; opacity:0.3;"></div>
            <div style="text-align:center;">
                <div style="font-size:28px; font-weight:bold; color:#C5A880;"><?= $total_found ?></div>
                <div style="font-size:13px; opacity:0.7;">بلاغ موجود</div>
            </div>
        </div>
        <div class="hero-buttons">
            <a href="add_item.php?type=lost"  class="btn-lost"><i class="fas fa-exclamation-circle"></i> أبلغ عن مفقود</a>
            <a href="add_item.php?type=found" class="btn-found"><i class="fas fa-check-circle"></i> أبلغ عن موجود</a>
        </div>
    </div>

    <!-- ===== شريط البحث والفلترة ===== -->
    <form method="GET" action="index.php">
        <div class="search-bar">
            <input
                type="text"
                name="search"
                placeholder="🔍 ابحث عن غرض..."
                value="<?= htmlspecialchars($search) ?>"
            >
            <select name="filter">
                <option value="all"   <?= $filter==='all'   ? 'selected':'' ?>>الكل</option>
                <option value="lost"  <?= $filter==='lost'  ? 'selected':'' ?>>🔴 مفقود</option>
                <option value="found" <?= $filter==='found' ? 'selected':'' ?>>🟢 موجود</option>
            </select>
            <select name="category">
                <option value="0">كل التصنيفات</option>
                <?php foreach ($cats as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category==$cat['id'] ? 'selected':'' ?>>
                        <?= htmlspecialchars($cat['name_ar']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><i class="fas fa-search"></i> بحث</button>
        </div>
    </form>

    <!-- ===== عنوان القسم ===== -->
    <div class="section-title">
        <?php if ($search): ?>
            نتائج البحث عن: "<?= htmlspecialchars($search) ?>"
        <?php elseif ($filter === 'lost'): ?>
            🔴 البلاغات المفقودة
        <?php elseif ($filter === 'found'): ?>
            🟢 البلاغات الموجودة
        <?php else: ?>
            أحدث البلاغات
        <?php endif; ?>
    </div>

    <!-- ===== شبكة البطاقات ===== -->
    <?php if (empty($items)): ?>
        <div style="text-align:center; padding:60px 20px; color:#C5A880; opacity:0.7;">
            <i class="fas fa-box-open" style="font-size:48px; margin-bottom:16px; display:block;"></i>
            <p style="font-size:16px;">لا توجد بلاغات حالياً</p>
            <?php if ($search || $filter !== 'all' || $category > 0): ?>
                <a href="index.php" style="color:#C5A880; margin-top:10px; display:inline-block;">← عرض كل البلاغات</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="cards-grid">
            <?php foreach ($items as $item): ?>
                <a href="item.php?id=<?= $item['id'] ?>" class="card">

                    <!-- صورة البطاقة -->
                    <?php if (!empty($item['image1'])): ?>
                        <img
                            src="<?= SITE_URL ?>/assets/uploads/<?= htmlspecialchars($item['image1']) ?>"
                            alt="<?= htmlspecialchars($item['title']) ?>"
                        >
                    <?php else: ?>
                        <div class="card-no-img">
                            <i class="fas <?= htmlspecialchars($item['cat_icon']) ?>"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <!-- نوع البلاغ -->
                        <span class="card-type <?= $item['type']==='lost' ? 'type-lost' : 'type-found' ?>">
                            <?= $item['type']==='lost' ? '🔴 مفقود' : '🟢 موجود' ?>
                        </span>

                        <!-- عنوان البلاغ -->
                        <div class="card-title"><?= htmlspecialchars($item['title']) ?></div>

                        <!-- معلومات إضافية -->
                        <div class="card-meta">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($item['building']) ?>
                            <?php if (!empty($item['location_desc'])): ?>
                                — <?= htmlspecialchars($item['location_desc']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="card-meta" style="margin-top:4px;">
                            <i class="fas fa-calendar-alt"></i>
                            <?= htmlspecialchars($item['incident_date']) ?>
                        </div>
                        <div class="card-meta" style="margin-top:4px;">
                            <i class="fas fa-tag"></i>
                            <?= htmlspecialchars($item['cat_name']) ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>    