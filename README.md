eguide
========

Xoops Modules

## 說明
* eguide是一個功能強大的Xoops報名模組，擁有彈性自訂各類表單輸入項、複數人數報名、報名審核機制、自動或手動發信機制、重複開放報名、必填欄位表單驗證、前台彈性顯示報名欄位資料、自動統計、匯出CSV或Excel格式報名資料...等功能
* 此模組原作者為nbuy( https://github.com/nbuy )
* 此修正版本樣板套用bootstrap3，您的佈景需支援bootstrap3，例如：xBootstrap( http://xoops.org/modules/news/article.php?storyid=6571 )
* 建立及修改報名，日期選擇器改採bootstrap-datepicker( https://github.com/eternicode/bootstrap-datepicker )，所見即所得編輯器改採TinyMCE4( http://www.tinymce.com/ )

## 使用
* 下載解壓縮後將模組資料夾名稱由 xoops-modules-eguide 更改為 eguide 後安裝，安裝方式和一般Xoops模組相同
* 啟用TinyMCE，至偏好設定輸入use_fckeditor=default
* 升級使用者，活動狀態標示可至偏好設定為
```
0,<span class="label label-default">關閉中</span>,50,<span class="label label label-success">報名中</span>,100,<span class="label label-warning">反應熱烈</span>,101,<span class="label label-danger">已額滿</span>
```
