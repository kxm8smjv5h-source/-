<?php
include 'config.php';
include 'includes/header.php';
user_id = isset(_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
$success = ""; $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$rec_id = mysqli_real_escape_string($conn, $_POST['rec_id']);
$item_id = mysqli_real_escape_string($conn, $_POST['item_id']);
$text = mysqli_real_escape_string($conn, $_POST['text']);
if (!empty($rec_id) && !empty($text)) {
$sql = "INSERT INTO messages (sender_id, receiver_id, item_id, message_text) VALUES ('$user_id', '$rec_id', '$item_id', '$text')";
if (mysqli_query($conn, $sql)) { $success = "تم الإرسال بنجاح!"; }
else { $error = "حدث خطأ."; }
}
}
$inbox = mysqli_query($conn, "SELECT m., u.username FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = '$user_id' ORDER BY m.id DESC");
$sent = mysqli_query($conn, "SELECT m., u.username FROM messages m JOIN users u ON m.receiver_id = u.id WHERE m.sender_id = '$user_id' ORDER BY m.id DESC");
?>
<div style="max-width: 800px; margin: 20px auto; font-family: sans-serif; direction: rtl;">
<h2>صندوق رسائل المفقودات</h2>
<?php if($success) echo "<p style='color:green;'>$success</p>"; ?>
<form action="messages.php" method="POST" style="background:#f9f9f9; padding:15px; border-radius:8px;">
<h3>إرسال رسالة جديدة</h3>
<label>رقم الطالبة المستلمة:</label>

<input type="number" name="rec_id" required style="width:100%; margin-bottom:10px;">

<label>رقم الغرض (ID):</label>

<input type="number" name="item_id" required style="width:100%; margin-bottom:10px;">

<label>نص الرسالة:</label>

<textarea name="text" rows="3" required style="width:100%; margin-bottom:10px;"></textarea>

<button type="submit" style="background:green; color:white; padding:8px 15px; border:none; border-radius:4px;">إرسال</button>
</form>
<div style="display: flex; gap: 20px; margin-top:20px;">
<div style="flex: 1; border:1px solid #ddd; padding:10px;">
<h3>الرسائل الواردة</h3>
<?php while($row = mysqli_fetch_assoc($inbox)): ?>
<p><b>من:</b> <?php echo $row['username']; ?> (غرض: <?php echo $row['item_id']; ?>)</p>
<p style="background:#eee; padding:5px;"><?php echo $row['message_text']; ?></p>
<?php endwhile; ?>
</div>
<div style="flex: 1; border:1px solid #ddd; padding:10px;">
<h3>الرسائل الصادرة</h3>
<?php while($row = mysqli_fetch_assoc($sent)): ?>
<p><b>إلى:</b> <?php echo $row['username']; ?> (غرض: <?php echo $row['item_id']; ?>)</p>
<p style="background:#e8f4fd; padding:5px;"><?php echo $row['message_text']; ?></p>
<?php endwhile; ?>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>