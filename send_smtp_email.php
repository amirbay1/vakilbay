<?php
// فراخوانی کلاس‌های PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// بارگذاری فایل‌های مورد نیاز
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// فقط در صورتی که فرم ارسال شده باشد، کد را اجرا کن
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ساخت یک نمونه جدید از PHPMailer
    $mail = new PHPMailer(true);

    try {
        // ------------------ تنظیمات سرور و SMTP (بدون تغییر) ------------------
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'vakilbay1@gmail.com';
        $mail->Password   = 'hsen wjrx uigz rbbt'; // گذرواژه برنامه شما
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        // ------------------ تنظیمات گیرندگان (بدون تغییر) ------------------
        $mail->setFrom('vakilbay1@gmail.com', 'وبسایت حقوقی امیرحسین بای');
        $mail->addAddress('amirbay19@gmail.com', 'وکیل بای');
        
        // دریافت اطلاعات از فرم
        $name = htmlspecialchars(trim($_POST['name']));
        $phone = htmlspecialchars(trim($_POST['phone']));
        $message = htmlspecialchars(trim($_POST['message']));

        // ------------------ محتوای ایمیل (قالب جدید و شیک) ------------------
        $mail->isHTML(true);
        $mail->Subject = 'پیام جدید از فرم تماس | وب‌سایت وکیل بای';

        // طراحی قالب HTML برای ایمیل
        $mail->Body = "
        <html lang='fa' dir='rtl'>
        <head>
            <style>
                body { font-family: 'Tahoma', Arial, sans-serif; direction: rtl; text-align: right; }
                .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                .header { background-color: #0056b3; color: white; padding: 10px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; }
                .content table { width: 100%; border-collapse: collapse; }
                .content td { padding: 10px; border-bottom: 1px solid #eee; }
                .content td.label { font-weight: bold; color: #333; width: 120px; }
                .message-box { background-color: #ffffff; border: 1px solid #ddd; padding: 15px; margin-top: 15px; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>پیام جدید از فرم تماس وب‌سایت</h2>
                </div>
                <div class='content'>
                    <table>
                        <tr>
                            <td class='label'>نام فرستنده:</td>
                            <td>" . $name . "</td>
                        </tr>
                        <tr>
                            <td class='label'>شماره تماس:</td>
                            <td>" . $phone . "</td>
                        </tr>
                    </table>
                    <div class='message-box'>
                        <p style='font-weight: bold;'>متن پیام:</p>
                        <p>" . nl2br($message) . "</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";

        // نسخه متنی ساده برای کلاینت‌هایی که HTML پشتیبانی نمی‌کنند
        $mail->AltBody = "پیام جدید از فرم تماس:\n\nنام فرستنده: " . $name . "\nشماره تماس: " . $phone . "\n\nمتن پیام:\n" . $message;

        // ارسال ایمیل
        $mail->send();

        // **مرحله جدید: بازگرداندن کاربر به صفحه قبلی با پیام موفقیت**
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '/#success');
        exit();

    } catch (Exception $e) {
        // **مرحله جدید: بازگرداندن کاربر در صورت خطا**
        // می‌توانید پیام خطا را نیز به آدرس اضافه کنید تا در صورت نیاز آن را نمایش دهید
        $errorMessage = urlencode($mail->ErrorInfo);
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?status=error&msg=' . $errorMessage);
        exit();
    }
} else {
    // اگر کسی مستقیماً به این فایل دسترسی پیدا کرد، او را به صفحه اصلی هدایت کن
    header('Location: /'); // آدرس صفحه اصلی یا فرم خود را اینجا وارد کنید
    exit();
}
?>