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
                                ),
                                array(
                                    'title' => 'KEY',
                                    'key' => 'KEY',
                                ),
                            );
                        foreach ($fields as $key => $v) {
                            ?>
                            <li style="margin-bottom: 20px">
                                <code><?php echo $v['title']; ?></code>
                                <input
                                    name="<?php echo GeeTest::geetestOptionKey($v['key']); ?>"
                                    type="text"
                                    value="<?php echo GeeTest::geetestGetOptions($v['key']); ?>"
                                    class="regular-text" />
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