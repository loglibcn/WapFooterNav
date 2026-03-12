<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * WAP版悬浮底部导航栏插件
 * 
 * @package WapFooterNav
 * @author 李文君's Blog
 * @version 1.0.0
 * @link https://loglib.cn
 */
class WapFooterNav_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        // 注册底部输出钩子
        Typecho_Plugin::factory('Widget_Archive')->footer = array('WapFooterNav_Plugin', 'renderFooterNav');
        return _t('WAP悬浮底部导航栏插件已激活');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        return _t('WAP悬浮底部导航栏插件已禁用');
    }

    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // 导航项配置
        $navItems = new Typecho_Widget_Helper_Form_Element_Textarea(
            'navItems',
            null,
            '首页|/|https://loglib.cn/xxx.svg
相册|/xc.html|https://loglib.cn/xxx.svg
友圈|/yq.html|https://loglib.cn/xxx.svg
友链|/link.html|https://loglib.cn/xxx.svg
关于|/start-page.html|https://loglib.cn/xxx.svg',
            _t('导航项目配置'),
            _t('每行一个导航项，格式：名称|链接|图标地址
示例：首页|/|https://loglib.cn/xxx.svg')
        );
        $form->addInput($navItems);

        // 高亮颜色配置
        $activeColor = new Typecho_Widget_Helper_Form_Element_Text(
            'activeColor',
            null,
            '#0088ff',
            _t('高亮颜色'),
            _t('导航项激活/悬浮时的文字颜色，例如：#0088ff')
        );
        $form->addInput($activeColor);

        // 导航栏高度
        $navHeight = new Typecho_Widget_Helper_Form_Element_Text(
            'navHeight',
            null,
            '55',
            _t('导航栏高度(px)'),
            _t('底部导航栏的高度，默认55px')
        );
        $form->addInput($navHeight);

        // 图标大小
        $iconSize = new Typecho_Widget_Helper_Form_Element_Text(
            'iconSize',
            null,
            '20',
            _t('图标大小(px)'),
            _t('导航图标宽度和高度，默认20px')
        );
        $form->addInput($iconSize);

        // 日间背景色
        $lightBg = new Typecho_Widget_Helper_Form_Element_Text(
            'lightBg',
            null,
            '#ffffff',
            _t('日间背景色'),
            _t('默认白色背景 #ffffff')
        );
        $form->addInput($lightBg);

        // 夜间背景色
        $darkBg = new Typecho_Widget_Helper_Form_Element_Text(
            'darkBg',
            null,
            '#121212',
            _t('夜间背景色'),
            _t('默认黑色背景 #121212')
        );
        $form->addInput($darkBg);
    }

    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
        // 个人配置面板留空
    }

    /**
     * 渲染底部导航栏
     * 
     * @access public
     * @return void
     */
    public static function renderFooterNav()
    {
        // 获取插件配置
        $options = Typecho_Widget::widget('Widget_Options')->plugin('WapFooterNav');
        
        // 解析导航项
        $navItems = [];
        $lines = explode("\n", $options->navItems);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $parts = explode('|', $line);
            if (count($parts) >= 3) {
                $navItems[] = [
                    'name' => trim($parts[0]),
                    'href' => trim($parts[1]),
                    'icon' => trim($parts[2])
                ];
            }
        }

        // 配置参数
        $activeColor = $options->activeColor ?: '#0088ff';
        $navHeight = $options->navHeight ?: 55;
        $iconSize = $options->iconSize ?: 20;
        $lightBg = $options->lightBg ?: '#ffffff';
        $darkBg = $options->darkBg ?: '#121212';
        $paddingBottom = $navHeight + 5; // 主体底部内边距

        // 输出HTML和CSS
        echo '
<!-- WAP版悬浮底部导航栏 - Typecho插件自动生成 -->
<style>
/* 移动端悬浮底部导航核心样式 */
.footer-wap.fixed-bottom {
    display: none; /* 默认隐藏，仅移动端显示 */
    position: fixed !important; /* 强制固定悬浮 */
    bottom: 0 !important; /* 死死贴在底部 */
    left: 0 !important;
    right: 0 !important;
    /* 核心：设置实色背景 + 强制不透明 */
    background-color: ' . $lightBg . ' !important; /* 纯白实色背景 */
    background-opacity: 1 !important; /* 强制不透明（兜底） */
    opacity: 1 !important; /* 整体不透明 */
    border-top: 1px solid #e5e7eb; /* 顶部浅灰分隔线 */
    z-index: 9999 !important; /* 最高层级，避免被其他元素遮挡 */
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05); /* 顶部轻微阴影，更立体 */
    /* 圆角样式 */
    border-radius: 16px 16px 0 0 !important; /* 顶部左右圆角，底部直角贴底 */
    overflow: hidden; /* 防止导航内容溢出圆角区域 */
    padding-top: 2px; /* 避免内容紧贴圆角边缘 */
}

/* 仅在移动端显示 */
@media (max-width: 768px) {
    /* 隐藏PC端底部 */
    .footer.pc {
        display: none !important;
    }
    /* 显示悬浮导航 */
    .footer-wap.fixed-bottom {
        display: block;
    }
    /* 导航容器布局 */
    .wap-nav {
        display: flex;
        justify-content: space-around; /* 均匀分布 */
        align-items: center;
        height: ' . $navHeight . 'px; /* 导航栏高度 */
        padding: 0 5px;
        padding-bottom: env(safe-area-inset-bottom); /* 适配苹果手机底部刘海 */
    }
    /* 单个导航项 */
    .wap-item {
        display: flex;
        flex-direction: column; /* 图标在上，文字在下 */
        align-items: center;
        justify-content: center;
        text-decoration: none; /* 去掉下划线 */
        color: #666666; /* 默认文字颜色 */
        font-size: 11px; /* 小字更精致 */
        gap: 2px; /* 图标和文字间距 */
        flex: 1; /* 等分宽度 */
        text-align: center;
    }
    /* 图标样式 */
    .wap-icon {
        width: ' . $iconSize . 'px; /* 图标大小，可自行调整 */
        height: ' . $iconSize . 'px;
        object-fit: contain; /* 保持图标比例，不拉伸 */
    }
    /* 激活/悬浮高亮 */
    .wap-item.active,
    .wap-item:hover {
        color: ' . $activeColor . '; /* 高亮颜色，匹配你的主题色 */
    }
    .wap-item.active .wap-icon,
    .wap-item:hover .wap-icon {
        opacity: 0.8; /* 仅图标悬浮透明，背景仍不透明 */
    }
    /* 关键：给页面主体加底部内边距，避免内容被导航栏遮挡 */
    body {
        padding-bottom: ' . $paddingBottom . 'px !important; /* 比导航栏高度多5px，适配更友好 */
    }
}

/* 大屏设备强制隐藏悬浮导航 */
@media (min-width: 769px) {
    .footer-wap.fixed-bottom {
        display: none !important;
    }
}

/* ========== 黑夜模式适配 ========== */
body.dark .footer-wap.fixed-bottom,
body.night .footer-wap.fixed-bottom {
    background-color: ' . $darkBg . ' !important; /* 黑夜实色背景 */
    background-opacity: 1 !important;
    opacity: 1 !important;
    border-top: 1px solid #2d2d2d !important;
}
body.dark .wap-item,
body.night .wap-item {
    color: #cccccc !important;
}
body.dark .wap-item.active,
body.dark .wap-item:hover,
body.night .wap-item.active,
body.night .wap-item:hover {
    color: #409eff !important;
}
</style>

<div class="footer-wap fixed-bottom">
    <div class="wap-nav">';
        
        // 输出导航项
        foreach ($navItems as $item) {
            echo '
        <a href="' . $item['href'] . '" class="wap-item">
            <img class="wap-icon" src="' . $item['icon'] . '" alt="' . $item['name'] . '">
            <span>' . $item['name'] . '</span>
        </a>';
        }
        
        echo '
    </div>
</div>

<script>
$(function() {
    // 核心：同步边框+背景色（适配无CSS变量的主题）
    function syncBorderAndBg() {
        var $body = $("body");
        var $footer = $(".footer-wap.fixed-bottom");
        var isDark = $body.hasClass("dark") || $body.hasClass("night");

        // 方案1：读取主题的实际边框/背景色（优先级最高）
        var bodyBg = $body.css("background-color");
        var bodyBorder = $body.css("border-top-color") || $body.css("border-color");

        // 方案2：插件配置的主题色（备用）
        var lightBg = "' . $lightBg . '";       // 日间背景
        var lightBorder = "#e5e7eb";   // 日间边框
        var darkBg = "' . $darkBg . '";        // 黑夜背景
        var darkBorder = "#2d2d2d";    // 黑夜边框

        // 赋值：优先用主题实际颜色，兜底用插件配置色
        if (isDark) {
            $footer.css({
                "background-color": bodyBg || darkBg,
                "border-top-color": bodyBorder || darkBorder
            });
        } else {
            $footer.css({
                "background-color": bodyBg || lightBg,
                "border-top-color": bodyBorder || lightBorder
            });
        }
    }

    // 初始化+监听切换
    syncBorderAndBg(); // 页面加载时同步
    $("#night2, .night-mode-switch").on("click change", function() {
        setTimeout(syncBorderAndBg, 100); // 延迟确保主题类名更新
    });

    // 保留原有高亮逻辑
    var currentPath = window.location.pathname;
    $(".wap-item").each(function() {
        var itemHref = $(this).attr("href");
        if (currentPath === itemHref || (currentPath === "/" && itemHref === "/")) {
            $(this).addClass("active");
        }
    });
});
</script>';
    }
}