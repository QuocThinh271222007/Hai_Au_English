<?php

class ContactController
{
    function form()
    {
        require 'view/contact/form.php';
    }

    function sendEmail()
    {
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $message = $_POST['content'];

        $to = SHOP_OWNER;
        $subject = 'Godashop - Liên hệ';
        $website = get_domain();
        $content = "
        Xin chào chủ cửa hàng, <br>
        Dưới đây là thông tin khách hàng liên hệ <br>
        Tên đầy đủ: $fullname, <br>
        Số điện thoại: $mobile, <br>
        Địa chỉ email: $email, <br>
        Nội dung: $message, <br>
        ----------------------------------<br>
        Được gởi từ trang web $website
        ";
        $emailService = new EmailService();
        $emailService->send($to, $subject, $content);
        echo 'Đã gởi mail thành công';
    }
}
