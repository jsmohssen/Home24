<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>مشاهده اطلاعات ثبت شده</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
        .btn-gray {
            background-color: #6c757d;
            color: white;
        }
        .btn-red {
            background-color: #dc3545;
            color: white;
        }
        .btn-warning {
            background-color: #ffc107;
            color: white;
        }
        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
        .btn-container {
            text-align: center;
            margin-top: 20px;
        }
        .records-container {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body class="container mt-5">
    <h1 class="mb-4">مشاهده اطلاعات ثبت شده</h1>
    <div class="records-container" id="recordsContainer">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>نام</th>
                    <th>تاریخ</th>
                    <th>واحد شماره</th>
                    <th>مبلغ کل</th>
                    <th>مبلغ پرداختی</th>
                    <th>باقیمانده حساب</th>
                    <th>توضیحات</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody id="recordsTableBody">
            </tbody>
        </table>
    </div>
    <div class="btn-container">
        <a href="index.php" class="btn btn-gray">بازگشت به فرم &#x1F4C4;</a>
        <button class="btn btn-red" onclick="confirmDeleteAll()">حذف کل &#x1F5D1;</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetchRecords().then(records => {
                const recordsTableBody = document.getElementById('recordsTableBody');

                if (records.length === 0) {
                    recordsTableBody.innerHTML = '<tr><td colspan="8">هیچ رکوردی ثبت نشده است.</td></tr>';
                    return;
                }

                records.forEach((record, index) => {
                    const reminderStatus = record.reminderEnabled ? '🔔' : '🔕';
                    const recordHTML = `
                        <tr>
                            <td>${record.name}</td>
                            <td>${record.date}</td>
                            <td>${record.unitNumber}</td>
                            <td>${record.totalAmount}</td>
                            <td>${record.paidAmount}</td>
                            <td>${record.remainingAmount}</td>
                            <td>${record.description}</td>
                            <td>
                                <button class="btn btn-warning" onclick="editRecord(${index})">✏️</button>
                                <button class="btn btn-danger" onclick="confirmDelete(${index})">🗑️</button>
                                <button class="btn btn-info" onclick="toggleReminder(${index})">${reminderStatus}</button>
                            </td>
                        </tr>
                    `;
                    recordsTableBody.innerHTML += recordHTML;
                });
            });
        });

        async function fetchRecords() {
            const response = await fetch('get_records.php');
            const records = await response.json();
            return records;
        }

        function editRecord(index) {
            fetchRecords().then(records => {
                const record = records[index];
                if (record) {
                    localStorage.setItem('editRecordIndex', index);
                    window.location.href = 'index.php?edit=true';
                }
            });
        }

        function confirmDelete(index) {
            if (confirm('آیا مطمئن هستید که می‌خواهید این رکورد را حذف کنید؟')) {
                deleteRecord(index);
            }
        }

        function deleteRecord(index) {
            fetchRecords().then(records => {
                records.splice(index, 1);
                saveToServer(records).then(() => {
                    location.reload();
                });
            });
        }

        function confirmDeleteAll() {
            if (confirm('آیا مطمئن هستید که می‌خواهید تمام رکوردها را حذف کنید؟')) {
                deleteAllRecords();
            }
        }

        function deleteAllRecords() {
            saveToServer([]).then(() => {
                location.reload();
            });
        }

        function toggleReminder(index) {
            fetchRecords().then(records => {
                const record = records[index];
                if (record) {
                    const reminderEnabled = record.reminderEnabled ? false : true;
                    const confirmationMessage = reminderEnabled ?
                        'آیا می‌خواهید برای این رکورد یادآوری از سمت تلگرام برای شما ارسال شود؟' :
                        'آیا می‌خواهید یادآوری تلگرام را برای این رکورد خاموش کنید؟';

                    if (confirm(confirmationMessage)) {
                        record.reminderEnabled = reminderEnabled;
                        saveToServer(records).then(() => {
                            if (reminderEnabled) {
                                sendReminder(record);
                            }
                            location.reload();
                        });
                    }
                }
            });
        }

        function sendReminder(record) {
            fetch('send_reminder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ record })
            }).then(response => response.text())
              .then(result => alert(result));
        }

        async function saveToServer(records) {
            const response = await fetch('save_records.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ records })
            });
            const result = await response.text();
            console.log(result); // برای اشکال‌زدایی
        }
    </script>
</body>
</html>
