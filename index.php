<?php
// اتصال به پایگاه داده
$dbConnection = new mysqli("localhost", "root", "", "phoness");
if ($dbConnection->connect_error) {
    die("خطا در اتصال به پایگاه داده: " . $dbConnection->connect_error);
}

// تابعی جهت اضافه کردن گوشی جدید (بدون رنگ)
function insertPhone($db, $data) {
    $query = $db->prepare("INSERT INTO phones (name, model, ram, cpu) VALUES (?, ?, ?, ?)");
    if (!$query) {
        die("خطا در آماده‌سازی پرس‌وجو: " . $db->error);
    }
    $query->bind_param("ssss", $data['name'], $data['model'], $data['ram'], $data['cpu']);
    $result = $query->execute();
    $query->close();
    return $result;
}

// تابعی جهت جستجوی گوشی بر اساس نام (بدون رنگ)
function fetchPhones($db, $searchTerm) {
    $query = $db->prepare("SELECT id, name, model, ram, cpu FROM phones WHERE name LIKE CONCAT('%', ?, '%')");
    if (!$query) {
        die("خطا در آماده‌سازی پرس‌وجو: " . $db->error);
    }
    $query->bind_param("s", $searchTerm);
    $query->execute();
    $resultData = $query->get_result();
    $phones = [];
    while ($row = $resultData->fetch_assoc()) {
        $phones[] = $row;
    }
    $query->close();
    return $phones;
}

// تابعی جهت ویرایش گوشی بر اساس شناسه (بدون رنگ)
function updatePhone($db, $data) {
    $query = $db->prepare("UPDATE phones SET name = ?, model = ?, ram = ?, cpu = ? WHERE id = ?");
    if (!$query) {
        die("خطا در آماده‌سازی پرس‌وجو: " . $db->error);
    }
    $query->bind_param("ssssi", $data['name'], $data['model'], $data['ram'], $data['cpu'], $data['id']);
    $result = $query->execute();
    $query->close();
    return $result;
}

// تابعی جهت حذف گوشی بر اساس شناسه
function deletePhone($db, $id) {
    $query = $db->prepare("DELETE FROM phones WHERE id = ?");
    if (!$query) {
        die("خطا در آماده‌سازی پرس‌وجو: " . $db->error);
    }
    $query->bind_param("i", $id);
    $result = $query->execute();
    $query->close();
    return $result;
}

// تابعی جهت نمایش همه گوشی‌ها (بدون رنگ)
function listPhones($db) {
    $sql = "SELECT id, name, model, ram, cpu FROM phones";
    $result = $db->query($sql);
    $phones = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $phones[] = $row;
        }
    }
    return $phones;
}

$notification = "";
$searchOutput = "";
$listOutput = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ثبت گوشی
    if (isset($_POST['add_phone'])) {
        $phoneData = [
            'name'  => $_POST['name'],
            'model' => $_POST['model'],
            'ram'   => $_POST['ram'],
            'cpu'   => $_POST['cpu']
        ];
        if (insertPhone($dbConnection, $phoneData)) {
            $notification = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                گوشی با موفقیت ثبت شد!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                             </div>';
        } else {
            $notification = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                خطا در ثبت گوشی.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                             </div>';
        }
    }

    // جستجوی گوشی
    if (isset($_POST['search_phone'])) {
        $searchTerm = $_POST['search_name'];
        $results = fetchPhones($dbConnection, $searchTerm);
        if (count($results) > 0) {
            foreach ($results as $phone) {
                $searchOutput .= '<div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">📱 نام: ' . htmlspecialchars($phone['name']) . ' (ID: ' . $phone['id'] . ')</h5>
                                        <p class="card-text">📌 مدل: ' . htmlspecialchars($phone['model']) . '</p>
                                        <p class="card-text">💾 رم: ' . htmlspecialchars($phone['ram']) . '</p>
                                        <p class="card-text">⚙️ سی پی یو: ' . htmlspecialchars($phone['cpu']) . '</p>
                                    </div>
                                  </div>';
            }
        } else {
            $searchOutput = '<div class="alert alert-warning" role="alert">
                                ❌ هیچ گوشی‌ای با این نام یافت نشد.
                             </div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>📱 مدیریت گوشی‌ها</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/2331/2331970.png">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            font-family: 'Arial', sans-serif;
        }

        .container {
            background: rgba(255, 255, 255, 0.15);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.3);
        }

        .nav-tabs .nav-link {
            color: white;
            font-weight: bold;
        }

        .nav-tabs .nav-link.active {
            background: white;
            color: black;
            border-radius: 10px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 10px;
            border-radius: 8px;
        }

        .btn {
            background-color: #ffcc00;
            color: black;
            border-radius: 8px;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #e6b800;
        }

        h2 {
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">📱 مدیریت گوشی‌ها</h2>
    <ul class="nav nav-tabs justify-content-center" id="phoneTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="add-phone-tab" data-bs-toggle="tab" data-bs-target="#addPhoneTab" type="button" role="tab" aria-controls="addPhoneTab" aria-selected="true">📥 ثبت گوشی</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="search-phone-tab" data-bs-toggle="tab" data-bs-target="#searchPhoneTab" type="button" role="tab" aria-controls="searchPhoneTab" aria-selected="false">🔍 جستجوی گوشی</button>
        </li>
    </ul>

    <div class="tab-content mt-3" id="phoneTabContent">
        <div class="tab-pane fade show active pt-4" id="addPhoneTab" role="tabpanel">
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">نام گوشی</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="model" class="form-label">مدل</label>
                    <input type="text" class="form-control" id="model" name="model" required>
                </div>
                <div class="mb-3">
                    <label for="ram" class="form-label">رم</label>
                    <input type="text" class="form-control" id="ram" name="ram" required>
                </div>
                <div class="mb-3">
                    <label for="cpu" class="form-label">سی پی یو</label>
                    <input type="text" class="form-control" id="cpu" name="cpu" required>
                </div>
                <button type="submit" class="btn w-100" name="add_phone">✅ ثبت گوشی</button>
            </form>
        </div>

        <div class="tab-pane fade pt-4" id="searchPhoneTab" role="tabpanel">
            <form method="POST">
                <div class="mb-3">
                    <label for="search_name" class="form-label">نام گوشی جهت جستجو:</label>
                    <input type="text" class="form-control" id="search_name" name="search_name" required>
                </div>
                <button type="submit" class="btn w-100" name="search_phone">🔎 جستجو</button>
            </form>
            <div class="mt-4">
                <h4>نتایج جستجو:</h4>
                <?php echo $searchOutput; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
