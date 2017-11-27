<?php
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license      GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package
 * @author       XOOPS Development Team
 */

require_once __DIR__ . '/admin_header.php';

xoops_cp_header();

$aboutAdmin = \Xmf\Module\Admin::getInstance();

$aboutAdmin->displayNavigation(basename(__FILE__));
$aboutAdmin->displayAbout('nobuhiro.yasutomi@nifty.ne.jp', false);

require_once __DIR__ . '/admin_footer.php';
