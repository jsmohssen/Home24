<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>مدیریت منزل اجاره‌ای</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
        .btn-gray {
            background-color: #6c757d;
            color: white;
        }
        .btn-yellow {
            background-color: #ffc107;
            color: white;
        }
        .btn-green {
            background-color: #28a745;
            color: white;
        }
        .btn-container {
            text-align: center;
            margin-top: 20px;
        }
        .alert-success, .alert-info {
            display: none;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body class="container mt-5">
    <h1 class="mb-4">مدیریت منزل اجاره‌ای</h1>
    <div class="alert alert-success" id="successMessage">
        رکورد شما با موفقیت ثبت شد ✅
    </div>
    <form id="rentalForm" method="post">
        <div class="mb-3">
            <label for="name" class="form-label">نام</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">تاریخ</label>
            <input type="text" class="form-control datepicker" id="date" name="date" required>
        </div>
        <div class="mb-3">
            <label for="unitNumber" class="form-label">واحد شماره</label>
            <select class="form-control" id="unitNumber" name="unitNumber" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="totalAmount" class="form-label">مبلغ کل</label>
            <input type="text" class="form-control" id="totalAmount" name="totalAmount" required oninput="formatAmount(this)">
        </div>
        <div class="mb-3">
            <label for="paidAmount" class="form-label">مبلغ پرداختی</label>
            <input type="text" class="form-control" id="paidAmount" name="paidAmount" required oninput="formatAmount(this)">
        </div>
        <div class="mb-3">
            <label for="remainingAmount" class="form-label">باقیمانده حساب</label>
            <input type="text" class="form-control" id="remainingAmount" name="remainingAmount" readonly>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">توضیحات</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <div class="btn-container">
            <button type="button" class="btn btn-green" id="saveButton" onclick="saveRecord()">ذخیره &#x1F4BE;</button>
            <a href="view_records.php" class="btn btn-gray">مشاهده اطلاعات ثبت شده &#x1F4DD;</a>
            <button type="button" class="btn btn-yellow" onclick="downloadJSON()">دانلود اطلاعات &#x1F4E5;</button>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datepicker').persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true
            });

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('edit') === 'true') {
                const index = localStorage.getItem('editRecordIndex');
                fetchRecords().then(records => {
                    if (records[index]) {
                        const record = records[index];
                        $('#name').val(record.name);
                        $('#date').val(record.date);
                        $('#unitNumber').val(record.unitNumber);
                        $('#totalAmount').val(record.totalAmount.replace(/,/g, ''));
                        $('#paidAmount').val(record.paidAmount.replace(/,/g, ''));
                        $('#description').val(record.description);
                        updateRemainingAmount();
                        localStorage.setItem('editMode', true);
                        $('#saveButton').text('بروزرسانی 🛠️').removeClass('btn-green').addClass('btn-blue').attr('onclick', 'updateRecord()');
                    }
                });
            }
        });

        function formatAmount(input) {
            let value = input.value.replace(/,/g, '');
            if (!isNaN(value) && value.length > 0) {
                input.value = parseInt(value).toLocaleString('en-US');
            } else {
                input.value = '';
            }

            updateRemainingAmount();
        }

        function updateRemainingAmount() {
            const totalAmount = parseInt($('#totalAmount').val().replace(/,/g, '')) || 0;
            const paidAmount = parseInt($('#paidAmount').val().replace(/,/g, '')) || 0;
            let remainingAmount = totalAmount - paidAmount;
            let sign = remainingAmount > 0 ? '-' : '+';
            
            $('#remainingAmount').val(sign + Math.abs(remainingAmount).toLocaleString('en-US'));
        }

        async function fetchRecords() {
            const response = await fetch('get_records.php');
            const records = await response.json();
            return records;
        }

        function saveRecord() {
            const name = $('#name').val();
            const date = $('#date').val();
            const unitNumber = $('#unitNumber').val();
            const totalAmount = $('#totalAmount').val().replace(/,/g, '');
            const paidAmount = $('#paidAmount').val().replace(/,/g, '');
            const description = $('#description').val();
            
            // چک کردن فیلدهای خالی
            if (!name || !date || !unitNumber || !totalAmount || !paidAmount) {
                alert('لطفا تمامی فیلدها را پر کنید.');
                return;
            }

            let remainingAmount = totalAmount - paidAmount;
            let sign = remainingAmount > 0 ? '-' : '+';
            remainingAmount = Math.abs(remainingAmount);

            const record = {
                name: name,
                date: date,
                unitNumber: unitNumber,
                totalAmount: parseInt(totalAmount).toLocaleString('en-US'),
                paidAmount: parseInt(paidAmount).toLocaleString('en-US'),
                remainingAmount: sign + remainingAmount.toLocaleString('en-US'),
                description: description
            };

            fetchRecords().then(records => {
                if (localStorage.getItem('editMode')) {
                    const index = localStorage.getItem('editRecordIndex');
                    records[index] = record;
                    localStorage.removeItem('editRecordIndex');
                    localStorage.removeItem('editMode');
                } else {
                    records.push(record);
                }

                saveToServer(records).then(() => {
                    $('#successMessage').html('رکورد شما با موفقیت ثبت شد &#x2705;').show();
                });
            });
        }

        function updateRecord() {
            const name = $('#name').val();
            const date = $('#date').val();
            const unitNumber = $('#unitNumber').val();
            const totalAmount = $('#totalAmount').val().replace(/,/g, '');
            const paidAmount = $('#paidAmount').val().replace(/,/g, '');
            const description = $('#description').val();
            
            // چک کردن فیلدهای خالی
            if (!name || !date || !unitNumber || !totalAmount || !paidAmount) {
                alert('لطفا تمامی فیلدها را پر کنید.');
                return;
            }

            let remainingAmount = totalAmount - paidAmount;
            let sign = remainingAmount > 0 ? '-' : '+';
            remainingAmount = Math.abs(remainingAmount);

            const record = {
                name: name,
                date: date,
                unitNumber: unitNumber,
                totalAmount: parseInt(totalAmount).toLocaleString('en-US'),
                paidAmount: parseInt(paidAmount).toLocaleString('en-US'),
                remainingAmount: sign + remainingAmount.toLocaleString('en-US'),
                description: description
            };

            fetchRecords().then(records => {
                const index = localStorage.getItem('editRecordIndex');
                records[index] = record;
                localStorage.removeItem('editRecordIndex');
                localStorage.removeItem('editMode');

                saveToServer(records).then(() => {
                    $('#successMessage').html('رکورد شما با موفقیت بروزرسانی شد &#x1F4C8;').show();
                });
            });
        }

        async function saveToServer(records) {
            const response = await fetch('save_records.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({records})
            });
            const result = await response.text();
            console.log(result); // برای اشکال‌زدایی
        }

        function downloadJSON() {
            fetchRecords().then(records => {
                const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(records));
                const downloadAnchorNode = document.createElement('a');
                downloadAnchorNode.setAttribute("href", dataStr);
                downloadAnchorNode.setAttribute("download", "records.json");
                document.body.appendChild(downloadAnchorNode);
                downloadAnchorNode.click();
                downloadAnchorNode.remove();
            });
        }
    </script>
</body>
</html>
