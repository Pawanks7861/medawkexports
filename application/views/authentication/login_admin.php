<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php'); ?>

<style>
.login-page-wrapper {
    display: flex;
    min-height: 100vh;
    align-items: center;
    background: #f5f5f5;
}

.login-logo-section {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px;
}

.login-form-section {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px;
}

.login-box {
    width: 100%;
    max-width: 450px;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 20px rgba(0,0,0,.08);
}

.company-logo img {
    max-width: 350px;
    height: auto;
}

@media(max-width:768px){
    .login-page-wrapper{
        flex-direction: column;
    }

    .login-logo-section{
        padding:30px 20px 10px;
    }

    .login-form-section{
        width:100%;
        padding:20px;
    }

    .company-logo img{
        max-width:250px;
    }
}
</style>

<body class="login_admin">

<div class="login-page-wrapper">

    <!-- Left Side Logo -->
    <div class="login-logo-section">
        <div class="company-logo">
            <?php get_dark_company_logo(); ?>
        </div>
    </div>

    <!-- Right Side Login Form -->
    <div class="login-form-section">

        <div class="login-box">

            <!--<h1 class="tw-text-2xl tw-text-neutral-800 text-center tw-font-semibold tw-mb-5">-->
            <!--    <?php echo _l('admin_auth_login_heading'); ?>-->
            <!--</h1>-->

            <?php $this->load->view('authentication/includes/alerts'); ?>

            <?php echo form_open($this->uri->uri_string()); ?>

            <?php echo validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>

            <?php hooks()->do_action('after_admin_login_form_start'); ?>

            <div class="form-group">
                <label for="email">
                    <?php echo _l('admin_auth_login_email'); ?>
                </label>
                <input type="email" id="email" name="email" class="form-control" autofocus>
            </div>

            <div class="form-group">
                <label for="password">
                    <?php echo _l('admin_auth_login_password'); ?>
                </label>
                <input type="password" id="password" name="password" class="form-control">
            </div>

            <?php if (show_recaptcha()) { ?>
                <div class="g-recaptcha tw-mb-4"
                    data-sitekey="<?php echo get_option('recaptcha_site_key'); ?>">
                </div>
            <?php } ?>

            <div class="form-group">
                <div class="checkbox checkbox-inline">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">
                        <?php echo _l('admin_auth_login_remember_me'); ?>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">
                    <?php echo _l('admin_auth_login_button'); ?>
                </button>
            </div>

            <!--<div class="form-group">-->
            <!--    <a href="<?php echo admin_url('authentication/forgot_password'); ?>">-->
            <!--        <?php echo _l('admin_auth_login_fp'); ?>-->
            <!--    </a>-->
            <!--</div>-->

            <?php hooks()->do_action('before_admin_login_form_close'); ?>

            <?php echo form_close(); ?>

            <hr>

           
            <footer class="login-footer" style="text-align:center;">
                <p>
                    <a href="#" target="_blank">
                        <img src="<?php echo base_url('assets/images/buildify_logo.png'); ?>"
                             alt="Buildify360 Logo"
                             width="45">
                    </a>
                    Powered by
                    <strong>Buildify360</strong>
                </p>
            </footer>

        </div>

    </div>

</div>

</body>
</html>