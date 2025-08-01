<?php
$title = 'Admin Profile';
require_once('includes/header.php');

$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Edit Profile</h2>
    <form action="profile_actions.php" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                <input type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update Profile</button>
        </div>
    </form>
</div>

<?php require_once('includes/footer.php'); ?>
