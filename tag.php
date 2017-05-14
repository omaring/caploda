<?php
require_once 'config.php';
require_once 'function.php';
require_once 'ImageView.php';

//index.phpから操作を行うImageViewのインスタンスを取得
$image_view = ImageView::get_instance();

//セッション情報をもとにユーザIDを取得
session_start();
$user_id = $image_view->get_user_info();

//画像のアップロードを行う場合の処理
$image_title = filter_input(INPUT_POST, "image_title");
$tag_str = filter_input(INPUT_POST, "image_tag_str");
if (isset($image_title) || isset($tag_str)) {
    $success = false;
}
if (isset($_FILES['upload_file']) && $_FILES['upload_file']['size'] > 0) {
    $success = $image_view->upload_image($_FILES['upload_file'], $user_id, $tag_str, $image_title);
}

//
$search_tag = filter_input(INPUT_GET, "tag");
//var_dump($search_tag);
?>

<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= h(TITLE); ?>　</title>
        <!-- 自分で設定したCSSの読み込み -->
        <link rel="stylesheet" href="mystyle.css">
        <!-- BootstrapのCSS読み込み -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <!-- jQuery読み込み -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <!-- BootstrapのJS読み込み -->
        <script src="js/bootstrap.min.js"></script>
    </head>

    <body>
        <!--ナビゲーションバー-->
        <nav class="navbar navbar-default navbar-static-top" role="navigation">
            <div class="container">
                <div class="navbar_header">
                    <button type="button" class="navbar-toggle collapsed">
                        <span class="sr-only">Toggle</span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href=""><?= h(TITLE) ?></a>
                </div>
                <div class="navbar-collapse collapse">                
                    <ul class="nav navbar-nav">
                        <li><a href="index.php">ホーム</a></li>
                        <li class="active"><a href="tag.php">タグ</a></li>
                        <li><a href="myimage.php">自分が投稿した画像</a></li>
                    </ul>
                    <form class="navbar-form navbar-right" role="form">
                        <!--モーダルウィンドウを表示-->
                        <!--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#loginModal">ログイン</button>-->
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#uploadModal">画像のアップロード</button>
                    </form>
                </div>
            </div>
        </nav>

        <!--アップロードの成功/失敗-->
        <?php if (isset($success)) $image_view->show_alert_bar($success); ?>

<!--        <div class='page-header'>
            <div class="container">
                <div class="col-sm-3">
                    <select class="form-control" onChange="top.location.href = value">
                        <option value="?sort=1">投稿日が新しい順</option>
                        <option value="?sort=2">投稿日が古い順</option>
                        <option value="?sort=3">閲覧数が多い順</option>
                        <option value="?sort=4">閲覧数が少ない順</option>
                    </select>
                </div>
            </div>
        </div>-->

        <div class="container">
            <?php if(!isset($search_tag)) : ?>
                <h3>タグ一覧</h3>
                <?php $image_view->show_tag(); ?>
            <?php else : ?>
                <h3>タグ検索："<?=h($search_tag); ?>"</h3>
                <?php $image_view->show_image_from_tag($search_tag); ?>
            <?php endif; ?>
        </div>


        <!-- モーダルウィンドウの中身 -->
        <div class="modal" id="loginModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form class="form-horizontal" method="post" action="index.php" enctype="multipart/form-data" accept-charset="utf-8">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">ログイン情報の入力</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label form="inputEmail3" class="col-sm-3 control-label">ユーザ名</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="user_name" placeholder="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label form="inputPassword3" class="col-sm-3 control-label">パスワード</label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control" name="password" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" name="upload">ログイン</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal" id="uploadModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form class="form-horizontal" method="post" action="index.php" enctype="multipart/form-data" accept-charset="utf-8">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">画像のアップロード</h4>
                        </div>
                        <div class="modal-body">
                            <div class="imgInput" align="center">
                                <p><img src="noname.png" name="image" class="imgView" height="300"></p>
                                <input type="file" name="upload_file" accept="image/*">
                                <p class="help-block">画像サイズは<?= h(IMAGE_SIZE_MAX) ?>MBまで</p>
                            </div>
                            <div class="form-group">
                                <label form="inputEmail3" class="col-sm-3 control-label">タイトル</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="image_title" placeholder="無題">
                                </div>
                            </div>
                            <div class="form-group">
                                <label form="inputPassword3" class="col-sm-3 control-label">タグ</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="image_tag_str" placeholder="複数のタグをつける場合は,(半角カンマ)で区切る">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" name="upload">アップロード</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--画像プレビュー用のスクリプト-->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="preview_image.js"></script>
    </body>
</html>
