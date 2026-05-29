<?php
include 'config.php';
include 'includes/header.php';

$keyword  = '';
$category = '';
$building = '';
$type     = '';

if (isset($_GET['keyword']))  $keyword  = clean($conn, $_GET['keyword']);
if (isset($_GET['category'])) $category = clean($conn, $_GET['category']);
if (isset($_GET['building'])) $building = clean($conn, $_GET['building']);
if (isset($_GET['type']))     $type     = clean($conn, $_GET['type']);

$sql = "SELECT items.*, categories.name_ar AS category_name 
        FROM items 
        LEFT JOIN categories ON items.category_id = categories.id 
        WHERE items.status = 'active'";

if ($keyword != '') {
    $sql = $sql . " AND (items.title LIKE '%" . $keyword . "%' OR items.description LIKE '%" . $keyword . "%')";
}

if ($category != '') {
    $sql = $sql . " AND items.category_id = " . $category;
}

if ($building != '') {
    $sql = $sql . " AND items.building = '" . $building . "'";
}

if ($type != '') {
    $sql = $sql . " AND items.type = '" . $type . "'";
}

$sql = $sql . " ORDER BY items.id DESC";

$result = mysqli_query($conn, $sql);
$num    = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>البحث - مفقودات جامعة الطائف</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="search-wrapper">

    <form method="GET" action="search.php">

        <h2>البحث عن المفقودات</h2>

        <div class="form-group">
            <label>بحث بالكلمة:</label>
            <input type="text" name="keyword" value="<?php echo $keyword; ?>" placeholder="اكتب اسم الغرض...">
        </div>

        <div class="form-group">
            <label>التصنيف:</label>
            <select name="category">
                <option value="">-- جميع التصنيفات --</option>
                <?php
                $cat_result = mysqli_query($conn, "SELECT * FROM categories");
                while ($cat = mysqli_fetch_array($cat_result)) {
                    if ($category == $cat['id']) {
                        echo "<option value='" . $cat['id'] . "' selected>" . $cat['name_ar'] . "</option>";
                    } else {
                        echo "<option value='" . $cat['id'] . "'>" . $cat['name_ar'] . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>النوع:</label>
            <select name="type">
                <option value="">-- الكل --</option>
                <?php
                if ($type == 'lost') {
                    echo "<option value='lost' selected>مفقود</option>";
                    echo "<option value='found'>موجود</option>";
                } else if ($type == 'found') {
                    echo "<option value='lost'>مفقود</option>";
                    echo "<option value='found' selected>موجود</option>";
                } else {
                    echo "<option value='lost'>مفقود</option>";
                    echo "<option value='found'>موجود</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>المبنى:</label>
            <select name="building">
                <option value="">-- جميع المباني --</option>
                <?php
                $buildings = array('مبنى 2', 'مبنى 6', 'مبنى 7', 'مبنى 9', 'مبنى 12', 'مبنى 16');
                foreach ($buildings as $b) {
                    if ($building == $b) {
                        echo "<option value='" . $b . "' selected>" . $b . "</option>";
                    } else {
                        echo "<option value='" . $b . "'>" . $b . "</option>";
                    }
                }
                ?>
            </select>
        </div>

        <input type="submit" value="بحث" class="btn-search">
        <a href="search.php" class="btn-clear">مسح الفلاتر</a>

    </form>

    <p class="results-count">عدد النتائج: <?php echo $num; ?></p>

    <div class="results-grid">
    <?php
    if ($num == 0) {
        echo "<p class='no-results'>لا توجد نتائج للبحث</p>";
        } else {
        while ($row = mysqli_fetch_array($result)) {
            echo "<div class='item-card'>";

            if ($row['image1'] != '') {
                echo "<img src='uploads/" . $row['image1'] . "' alt='صورة'>";
            } else {
                echo "<div class='no-img'>لا توجد صورة</div>";
            }

            echo "<div class='item-card-body'>";
            echo "<p class='item-cat'>" . $row['category_name'] . " | " . $row['building'] . "</p>";
            echo "<h3>" . $row['title'] . "</h3>";
            echo "<p>" . $row['description'] . "</p>";

            if ($row['type'] == 'lost') {
                echo "<span class='badge-lost'>مفقود</span>";
            } else {
                echo "<span class='badge-found'>موجود</span>";
            }

            echo "<br><a href='item.php?id=" . $row['id'] . "' class='btn-details'>عرض التفاصيل</a>";
            echo "</div>";
            echo "</div>";
        }
    }
    ?>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>