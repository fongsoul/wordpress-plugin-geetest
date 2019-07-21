<div class="wrap">
    <h2>插件设置</h2>
    <form method="post" action="options.php">
        <?php
            settings_fields('geetest-setting-group');
        ?>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label>常规设置</label>
                    </th>
                    <td>
                        <ul style="margin-top: 0">
                            <?php $fields = array(
                                array(
                                    'title' => 'ID',
                                    'key' => 'ID',
                                    'input_type' => 'text'
                                ),
                                array(
                                    'title' => 'KEY',
                                    'key' => 'KEY',
                                    'input_type' => 'text'
                                ),
                                array(
                                    'title' => __('启用极验在登陆表单'),
                                    'key' => 'enable_geetset_in_login_form',
                                    'input_type' => 'checkbox'
                                ),
                                array(
                                    'title' => __('启用极验在注册表单'),
                                    'key' => 'enable_geetset_in_register_form',
                                    'input_type' => 'checkbox'
                                ),
                                array(
                                    'title' => __('启用极验在找回密码表单'),
                                    'key' => 'enable_geetset_in_lost_pass_form',
                                    'input_type' => 'checkbox'
                                ),
                                array(
                                    'title' => __('启用极验在评论表单'),
                                    'key' => 'enable_geetset_in_comment_form',
                                    'input_type' => 'checkbox'
                                ),
                            );
                        foreach ($fields as $key => $v) {
                            ?>
                            <li style="margin-bottom: 20px">
                                <code><?php echo $v['title']; ?></code>
                                <input class="regular-<?php echo $v['input_type']?>"
                                name="<?php echo GeeTest::geetestOptionKey($v['key']); ?>"
                                type="<?php echo $v['input_type']?>"
                                <?php if($v['input_type'] === 'text') echo 'value="'.GeeTest::geetestGetOptions($v['key']).'"' ?>
                                <?php if($v['input_type'] === 'checkbox') echo GeeTest::geetestGetOptions($v['key']) ? 'checked': ''?>/>
                                <?php if (isset($v['tips'])) {
                                ?>
                                <p class="description" style="font-size: 12px;">
                                    <?php echo $v['tips']; ?>
                                </p>
                                <?php
                            } ?>
                            </li>
                            <?php
                        }
                        ?>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" class="button button-primary" name="save"
                value="<?php _e('保存设置'); ?>" />
        </p>
    </form>
</div>