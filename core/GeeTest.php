<?php

final class GeeTest
{
    private static $geetest_instance;
    private $geetest_options;

    private function __construct($geetest_lib)
    {
        register_setting('geetest-setting-group', 'geetest_options');

        if (is_admin()) {
            add_action('admin_menu', array($this, 'geetestMenu'));
            add_filter('plugin_action_links', array($this, 'geetestSettingLink'), 10, 2);
        }

        $this->geetest_options = get_option('geetest_options');

        if (!empty($this->geetest_options['ID']) && !empty($this->geetest_options['KEY'])) {
            try {
                $this->geetest_lib = new $geetest_lib($this->geetest_options['ID'], $this->geetest_options['KEY']);
                if ($this->geetest_lib instanceof GeetestLib) {
                    if (!is_user_logged_in()) {
                        $this->session_start();

                        add_action('wp_ajax_nopriv_startCaptcha', array($this, 'startCaptcha'));

                        $enable_in_login_form = $this->geetest_options['enable_geetset_in_login_form'];
                        $enable_in_register_form = $this->geetest_options['enable_geetset_in_register_form'];
                        $enable_in_lost_pass_form = $this->geetest_options['enable_geetset_in_lost_pass_form'];
                        $enable_in_comment_form = $this->geetest_options['enable_geetset_in_comment_form'];

                        if ($enable_in_login_form || $enable_in_register_form || $enable_in_lost_pass_form) {
                            add_action('login_enqueue_scripts', array($this, 'scriptGeetestInComments'));
                        }

                        if ($enable_in_login_form) {
                            add_action('login_form', array($this, 'showGeetest'));
                            add_filter('wp_authenticate_user', array($this, 'validateGeetestLogin'));
                        }

                        if ($enable_in_register_form) {
                            add_action('register_form', array($this, 'showGeetest'));
                            add_filter('registration_errors', function (WP_ERROR $errors) {
                                if (!$this->validate()) {
                                    wp_shake_js();
                                    $errors->add('invalid_captchas', '<strong>ERROR</strong>: 验证码未通过。');
                                }

                                return $errors;
                            });
                        }

                        if ($enable_in_lost_pass_form) {
                            add_action('lostpassword_form', array($this, 'showGeetest'));
                            add_action('lostpassword_post', function (WP_ERROR $errors) {
                                if (!$this->validate()) {
                                    wp_shake_js();
                                    $errors->add('invalid_captcha', '<strong>ERROR</strong>: 验证码未通过。');
                                }
                            });
                        }

                        if ($enable_in_comment_form) {
                            add_action('wp_enqueue_scripts', array($this, 'scriptGeetestInComments'));
                            add_filter('comment_form_defaults', array($this, 'addGeetestToCommentForm'));
                            add_filter('preprocess_comment', array($this, 'validateGeetestComment'));
                        }
                    }

                    add_filter('login_redirect', array($this, 'login_redirect'), 10, 3);
                }
            } catch (\Error $e) { }
        }
    }

    public static function instance($geetest_lib = null)
    {
        if (!(self::$geetest_instance instanceof self)) {
            self::$geetest_instance = new self($geetest_lib);
        }
    }

    /**
     * 在插件页面给“极验“添加一个链接.
     */
    public function geetestSettingLink($links, $file)
    {
        if ($file === plugin_basename(GEETEST_BASE)) {
            $links[] = '<a href="' . esc_url(add_query_arg(array('page' => 'geetest'), admin_url('options-general.php'))) . '">' . esc_html__('设置', 'geetest') . '</a>';
        }

        return $links;
    }

    /**
     * 插件启用.
     */
    public static function geetestActivation()
    {
        $geetest_options = get_option('geetest_options');

        if ($geetest_options === false) {
            add_option('geetest_options', array('ID' => '', 'KEY' => ''));
        }
    }

    /**
     * 插件卸载.
     */
    public static function geetestUninstall()
    {
        delete_option('geetest_options');
    }

    /**
     * 后台添加极验管理菜单页面.
     */
    public function geetestMenu()
    {
        $geetest_icon = 'data:image/svg+xml;base64,' . base64_encode('<svg class="icon" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" width="20" height="20"><defs><style/></defs><path d="M911.36 471.04H660.48C640 399.36 568.32 348.16 496.64 358.4c-76.8 10.24-133.12 76.8-133.12 153.6s56.32 138.24 133.12 148.48c76.8 10.24 148.48-40.96 163.84-112.64h174.08c-15.36 143.36-122.88 256-261.12 281.6-138.24 25.6-281.6-40.96-343.04-168.96-66.56-128-40.96-281.6 56.32-378.88 102.4-102.4 256-122.88 378.88-56.32-20.48 56.32 0 117.76 51.2 148.48 51.2 30.72 117.76 15.36 153.6-30.72 35.84-46.08 35.84-112.64-5.12-158.72s-107.52-51.2-158.72-20.48C552.96 76.8 358.4 102.4 230.4 230.4S76.8 552.96 163.84 711.68C256 870.4 440.32 947.2 614.4 901.12 788.48 855.04 911.36 696.32 911.36 512v-40.96zM512 588.8c-40.96 0-76.8-35.84-76.8-76.8s35.84-76.8 76.8-76.8 76.8 35.84 76.8 76.8-35.84 76.8-76.8 76.8zm266.24-368.64c20.48 0 35.84 10.24 40.96 25.6 5.12 15.36 5.12 35.84-10.24 51.2s-30.72 15.36-51.2 10.24c-15.36-5.12-25.6-25.6-25.6-40.96 0-25.6 20.48-46.08 46.08-46.08z" fill="#fff"/></svg>');
        add_menu_page('geetest', '极验设置', 'manage_options', 'geetest', array($this, 'geetestSettingView'), $geetest_icon);
    }

    /**
     * 引入管理视图.
     */
    public function geetestSettingView()
    {
        require_once 'setting-view.php';
    }

    /**
     * 根据键获取极验设置值
     *
     * @param String $key
     *
     * @return string
     */
    public static function geetestGetOptions($key)
    {
        $geetest_options = get_option('geetest_options');

        if (isset($geetest_options[$key])) {
            return $geetest_options[$key];
        }

        return '';
    }

    /**
     * 获取极验设置的键.
     *
     * @param String $key
     * @return string
     */
    public static function geetestOptionKey(String $key)
    {
        if ($key) {
            return "geetest_options[$key]";
        }

        return '';
    }

    /**
     * 连接 GeeTest.
     */
    public function startCaptcha()
    {
        $data = array(
            'user_id' => 'test',
            'client_type' => 'web',
            'ip_address' => '127.0.0.1',
        );
        $status = $this->geetest_lib->pre_process($data, 1);

        $_SESSION['gtserver'] = $status;
        die($this->geetest_lib->get_response_str());
    }

    /**
     * 验证登陆表单.
     *
     * @param Int $user_id
     * @return boolean
     */
    public function validate($user_id = null)
    {
        if (!is_null($user_id) && $user_id === 1) {
            return true;
        }

        $challenge = esc_attr($_POST['geetest_challenge']);
        $validate = esc_attr($_POST['geetest_validate']);
        $seccode = esc_attr($_POST['geetest_seccode']);

        $data = array(
            'user_id' => 'test',
            'client_type' => 'web',
            'ip_address' => '127.0.0.1',
        );
        if (1 === $_SESSION['gtserver']) {
            if (!$this->geetest_lib->success_validate($challenge, $validate, $seccode, $data)) {
                return false;
            }
        } else {
            if (!$this->geetest_lib->fail_validate($challenge, $validate, $seccode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 验证登陆表单
     *
     * @param  $user
     * @return mixed
     */
    public function validateGeetestLogin($user)
    {
        if (!$this->validate()) {
            add_action('login_head', 'wp_shake_js', 12);

            return new WP_Error('broke', __('验证未通过'));
        }

        return $user;
    }

    /**
     * 验证评论表单
     *
     * @param $comment_data
     * @return array
     */
    public function validateGeetestComment($comment_data)
    {
        if (!$this->validate($comment_data['user_ID'])) {
            wp_die(__('<strong>ERROR</strong>: 验证码未通过。'), '', array('back_link' => true));
        }

        return $comment_data;
    }

    /**
     * 添加GeeTest验证模块到登陆表单.
     */
    public function showGeetest()
    {
        echo '<style>.geetest_holder{margin-right: 6px; min-width: auto !important;}</style><div id="embed-captcha" style="margin-bottom: 16px;"><div id="geetest-wait" style="height: 42px; line-height: 42px;">验证码加载中....</div></div>';
    }

    /**
     * 添加 GeeTest 验证模块到评论表单
     *
     * @param $default
     * @return array
     */
    public function addGeetestToCommentForm($default)
    {
        $default['submit_field'] = '<div id="embed-captcha"><div id="geetest-wait" style="height: 42px; line-height: 42px;">验证码加载中....</div></div>' . $default['submit_field'];

        return $default;
    }

    /**
     * GeeTest 脚本.
     */
    public function scriptGeetestInComments()
    {
        if ((is_singular() && comments_open()) || $this->is_login_or_register()) {
            wp_enqueue_script('wp-geetest', GEETEST_URL . '/assets/gt.js', array(), null, true);
            wp_localize_script('wp-geetest', 'geetest', array(
                'api' => admin_url('admin-ajax.php')
            ));
        }
    }

    /**
     * 判断是否登陆页面或注册页面
     *
     * @return boolean
     */
    private function is_login_or_register()
    {
        return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
    }

    public function login_redirect($redirect_to, $request, $user)
    {
        if (is_user_logged_in()) {
            exit(wp_redirect($redirect_to));
        }

        if (isset($user->roles) && is_array($user->roles)) {
            if (in_array('administrator', $user->roles)) {
                return $redirect_to;
            } else {
                return home_url();
            }
        } else {
            return $redirect_to;
        }
    }

    /**
     * 开启 SESSION
     *
     */
    private function session_start()
    {
        session_cache_limiter('public');
        session_start();
    }

    private function __clone()
    { }
}
