<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $isp_name; ?></title>
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; line-height: 1.6; background-color: #f7f7f7;">
        <div class="container" style="max-width: 700px; width:90%; margin: 10px auto; border: 1px solid #ddd;border-radius: 20px;padding: 20px;border-top: 2px solid blue;border-left:2px solid blue;box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);border-bottom: 2px solid red;border-right: 2px solid red;background:white;">
    
            <section style="padding: 0 20px;">
                <div style="text-align: center;">
                  %%CID_LOGO%%
                </div>
                <h2 style="text-align: left;font-size:clamp(.75rem,2vw,1.5rem)"><?php echo $title; ?></h2>
            </section>
            <section style="padding: 0 20px;">
                <?php if (!empty($recipient_name)) { ?>
                <p style="margin: 0 0 20px; font-size: clamp(.75rem,1vw,1rem);">Dear <?php echo $recipient_name; ?>,</p>
                <?php } ?>
                <p style="margin: 0 0 20px; font-size: clamp(.75rem,1vw,1rem);text-align: justify;"><?php echo $email_content; ?></p>
            </section>
            <section style="">
                <p style="font-size: clamp(.75rem,1vw,1rem);">Automated Email Sent By <?php echo $sender_name; ?>. Please Do Not Reply</p>
            </section>
            <section style="">
                <?php if (!empty($company_email)) { ?>
                    <p style="font-size: 12px; color: #666666;text-align: center;">For support, Please contact <?php echo $company_email; ?></p>
                <?php } ?>
            </section>
        </div>
    </body>
</html>