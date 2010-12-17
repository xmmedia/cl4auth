<h1>Forgot Password</h1>

<p>Please send me a link to reset my password.</p>

<?php echo Form::open('login/forgot'); ?>
<p>To start, enter email address: <?php echo Form::input('reset_username'); ?></p>
<p>Also enter the characters you see in the pictures below.</p>
<?php
echo recaptcha_get_html(RECAPTCHA_PUBLIC_KEY);
echo Form::submit(NULL, 'Reset Password');
echo Form::close();