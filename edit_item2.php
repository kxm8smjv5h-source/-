<?php
require_once 'config.php';
if (!isLoggedIn()) redirect('login.php');

$pageTitle = 'تعديل الإعلان المسجل';
$student_user_id = $_SESSION['user_id'];

$record_id = 0;
if (isset($_GET['id'])) {
    $record_id = (int)$_GET['id'];
}

$submission_error_msg = '';

$report_details = $conn->query("SELECT * FROM items WHERE id=$record_id AND user_id=$student_user_id")->fetch_assoc();
if (!$report_details) {
    redirect('dashboard.php');
}

$all_categories_list = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_title       = clean($conn, $_POST['title']);
    $input_description = clean($conn, $_POST['description']);
    $selected_cat_id   = (int)$_POST['category_id'];
    $input_city        = clean($conn, $_POST['city']);
    $input_district    = clean($conn, $_POST['district']);
    $event_date        = clean($conn, $_POST['incident_date']);

    if (empty($input_title) || empty($input_description)) {
        $submission_error_msg = 'تنبيه: الحقول الأساسية مطلوبة ولا يمكن تركها فارغة.';
    } else {
        $conn->query("UPDATE items SET 
                        title='$input_title', 
                        description='$input_description',
                        category_id=$selected_cat_id, 
                        city='$input_city', 
                        district='$input_district',
                        incident_date='$event_date'
                      WHERE id=$record_id AND user_id=$student_user_id");
                      
        redirect("item.php?id=$record_id");
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container" style="max-width:680px; background: #fff; padding: 25px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin: 30px auto;">
        
        <div class="form-title" style="font-size: 20px; font-weight: bold; color: #0277bd; margin-bottom: 20px; border-bottom: 2px solid #f5f5f5; padding-bottom: 10px;">
            <i class="fas fa-pen-square"></i> تحديث بيانات الإعلان
        </div>

        <?php if ($submission_error_msg): ?>
            <div class="alert alert-error" style="background: #ffebee; color: #c62828; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;">
                <?= $submission_error_msg ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">عنوان الإعلان *</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($report_details['title']) ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">القسم أو التصنيف</label>
                <select name="category_id" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    <?php foreach ($all_categories_list as $category_item): ?>
                        <option value="<?= $category_item['id'] ?>" <?= $report_details['category_id'] == $category_item['id'] ? 'selected' : '' ?>>
                            <?= $category_item['name_ar'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">تفاصيل الوصف *</label>
                <textarea name="description" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; min-height: 100px;"><?= htmlspecialchars($report_details['description']) ?></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom: 15px;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">المدينة *</label>
                    <input type="text" name="city" required value="<?= htmlspecialchars($report_details['city']) ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">الموقع / المبنى الداخلي</label>
                    <input type="text" name="district" value="<?= htmlspecialchars($report_details['district'] ?? '') ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">تاريخ رصد الغرض</label>
                <input type="date" name="incident_date" value="<?= $report_details['incident_date'] ?>" max="<?= date('Y-m-d') ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>

            <button type="submit" class="btn-submit" style="background: #2e7d32; color: #fff; border: none; padding: 12px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; width: 100%; font-size: 15px;">تعديل وحفظ البيانات</button>
            
            <div class="form-link" style="text-align: center; margin-top: 15px;">
                <a href="dashboard.php" style="color: #757575; font-size: 13px; text-decoration: none;"><i class="fas fa-arrow-right"></i> العودة إلى الرئيسية</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
