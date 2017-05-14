<?php

/*
 * ImageControllerからデータを受け取り，SQLを実行して，その結果を返す
 */

require_once 'config.php';

class ImageModel {

    private static $instance = null;
    private $pdo;

    /*
     * ImageModelクラスのインスタンスを取得（Singleton）
     */

    function get_instance() {
        if (!isset($instance)) {
            $instance = new ImageModel();
            try {
                $instance->pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
                //エラーをブラウザに出力する
                $instance->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                exit('データベース接続失敗。' . $e->getMessage());
            }
        }
        return $instance;
    }
    
    /*
     * 
     */
    function get_order($sort){
        switch ($sort){
            case ORDER_CREATED_DESC:
                $sql = "order by images.created desc ";
                break;
            case ORDER_CREATED_ASC:
                $sql = "order by images.created asc ";
                break;
            case ORDER_VIEWED_DESC:
                $sql = "order by images.viewed desc ";
                break;
            case ORDER_VIEWED_ASC:
                $sql = "order by images.viewed asc ";
                break;
            default:
                $sql = "";
                break;
        }
        return $sql;
    }
    /*
     * imagesテーブルから，レコードを全件抽出
     * 
     * out: レコードの連想配列
     */

    function select_image_all($sort = ORDER_CREATED_DESC) {
        $stmt = $this->pdo->prepare("select * from images " . $this->get_order($sort));
        $stmt->execute();
        $image = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $image;
    }
    /*
     * imagesテーブルから，指定したimage_idを持つレコードを抽出
     * （image_idはprimaryなので，抽出されるレコードは1件）
     * 
     * in:  $image_id   画像ID
     * out: レコードの配列
     */

    function select_image(int $image_id) {
        $stmt = $this->pdo->prepare("select * from images where image_id = :id　limit 1");
        $stmt->bindValue(":id", $image_id, PDO::PARAM_INT);
        $stmt->execute();
        $image = $stmt->fetch();
        return $image;
    }

    /*
     * imagesテーブルから，指定したimage_idを持つレコードを抽出
     * （image_idはprimaryなので，抽出されるレコードは1件）
     * 
     * in:  $image_id   画像ID
     * out: レコードの配列
     */

    function select_image_from_user_id($user_id) {
        $stmt = $this->pdo->prepare("select * from images where user_id = :id order by created desc");
        $stmt->bindValue(":id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $images;
    }

    /*
     * タグごとに，そのタグがついている画像の枚数を，連想配列として返す
     */
    function select_image_from_tag($tag) {
        $sql = <<< SQL
select
    i.* 
from 
    images as i 
inner join 
    images_tags as it 
on 
    it.image_id = i.image_id 
inner join 
    tags as t 
on 
    t.tag_id = it.tag_id 
and 
    t.tag_name = :tag
order by
    i.created desc
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":tag", $tag, PDO::PARAM_STR);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $images;
    }

    /*
     * usersテーブルから，指定したuser_nameとpasswordを持つレコードを検索し，そのuser_idを返す
     * （user_nameはuniqueなので，抽出されるレコードは1件）
     * 
     * in:  $user_name  ユーザ名
     *      $password   パスワード
     * out: $user_id    ユーザID
     */

    function select_user_id($user_name, $password) {
        $sql = <<< SQL
select 
    user_id 
from 
    users 
where 
    user_name = :user_name 
and 
    password = :password 
limit 
    1
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":user_name", $user_name, PDO::PARAM_STR);
        $stmt->bindValue(":password", $password, PDO::PARAM_STR);
        $stmt->execute();
        $user_id = $stmt->fetch();
        return $user_id;
    }

    /*
     * 全タグを取得
     */

    function select_tag_all() {
        $sql = <<< SQL
select
    t.tag_id, 
    t.tag_name, 
    count(it.image_id) as image_num
from
    tags as t, 
    images_tags as it
where
    t.tag_id = it.tag_id
group by 
    t.tag_id
order by
    image_num desc,
    t.tag_name asc
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $tags;
    }

    /*
     * tagsテーブルから，指定したimage_idの画像が持っているタグのレコードを抽出する
     * ただし，画像とタグの関係はimages_tagsテーブルに記録されているため，テーブルを内部連結する
     * 
     * in:  $image_id   画像ID
     * out: $tags       画像が持っているタグの連想配列
     */

    function select_tags_from_image($image_id) {
        $sql = <<< SQL
select 
    * 
from 
    tags as t 
inner join 
    images_tags as it 
on 
    t.tag_id = it.tag_id 
where 
    it.image_id = :image_id 
order by 
    t.tag_id asc
SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":image_id", $image_id, PDO::PARAM_INT);
        $stmt->execute();
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $tags;
    }

    /*
     * タグ名からタグIDを取得
     */

    function select_tag_id_from_tag_name($tag_name) {
        $stmt = $this->pdo->prepare("select tag_id from tags where tag_name = :tag_name limit 1");
        $stmt->bindValue(":tag_name", $tag_name, PDO::PARAM_STR);
        $stmt->execute();
        $tag_id = $stmt->fetch();
        return $tag_id;
    }

    /*
     * imagesテーブルにレコードを追加
     * 
     * TODO image_nameとcreatedを追加
     */

    function insert_image($user_id, $image_name, $image_dir) {
        //画像タイトルが指定されていない場合は，画像タイトルを画像ID.拡張子にする
        $stmt = $this->pdo->prepare("insert into images (user_id, image_name, image_dir) values (:user_id, :image_name, :image_dir)");
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindValue(":image_name", $image_name, PDO::PARAM_STR);
        $stmt->bindValue(":image_dir", $image_dir, PDO::PARAM_STR);
        $stmt->execute();
    }

    /*
     * tagsテーブルにレコードを追加
     * 
     * TODO createdを追加，重複するタグを無効
     */

    function insert_tag($tag_name) {
        $stmt = $this->pdo->prepare("insert ignore into tags (tag_name) values (:tag_name)");
        $stmt->bindValue(":tag_name", $tag_name, PDO::PARAM_STR);
        $stmt->execute();
    }

    /*
     * images_tagsテーブルにレコードを追加
     */

    function insert_image_tag($image_id, $tag_id) {
        $stmt = $this->pdo->prepare("insert into images_tags (image_id, tag_id) values (:image_id, :tag_id)");
        $stmt->bindValue(":image_id", $image_id, PDO::PARAM_INT);
        $stmt->bindValue(":tag_id", $tag_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /*
     * usersテーブルにレコードを追加
     * 
     * TODO createdを追加，重複するユーザ名を無効
     */

    function insert_user($user_name, $password) {
        $stmt = $this->pdo->prepare("insert into users (user_name, password) values (:user_name, :password)");
        $stmt->bindValue(":user_name", $user_name, PDO::PARAM_STR);
        $stmt->bindValue(":password", $password, PDO::PARAM_STR);
        $stmt->execute();
    }

    /*
     * favoriteテーブルにレコードを追加
     */

    function insert_favorite($image_id, $user_id, $comment) {
        $stmt = $this->pdo->prepare("insert into favorites (image_id, user_id, comment) values (:image_id, :user_id, :comment)");
        $stmt->bindValue(":image_id", $image_id, PDO::PARAM_INT);
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindValue(":comment", $comment, PDO::PARAM_STR);
        $stmt->execute();
    }

    /*
     * 画像をテーブルに登録する一連の処理
     *  - トランザクション開始
     *  - imagesテーブルに登録
     *  - tagsテーブルに登録
     *  - images_tagsテーブルに画像とタグの関係を登録
     *  - トランザクション終了
     */

    function add_image_transaction($user_id, $tags, $image_title, $image_dir) {
        $this->pdo->beginTransaction();
        try {
            $this->insert_image($user_id, $image_title, $image_dir);
            $image_id = $this->pdo->lastInsertId();
            foreach ($tags as $tag) {
                $this->insert_tag($tag);
                $tag_id = $this->select_tag_id_from_tag_name($tag)["tag_id"];
                $this->insert_image_tag($image_id, $tag_id);
            }
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            exit($e->getMessage());
        }
        return $image_id;
    }

}
