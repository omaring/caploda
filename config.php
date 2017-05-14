<?php

// MySQL
define("DB_DSN", "mysql:dbname=CapLoda;host=localhost;charset=utf8");
define("DB_USER", "omaring");
define("DB_PASS", "omaru514");

define("TITLE", "キャプろだ");
define("TAG_DELIMITER", ",");
define("DIR_UPLOAD", "./upload");
define("FORMAT_DATETIME", "Y-m-d H:i:s");

define("LIMIT_IMAGE", 12);
define("LIMIT_TAG", 5);
define("IMAGE_SIZE_MAX", 1);    // MB
define("IMAGE_HEIGHT", "100px");
define("ARTICLE_SIZE", "200px");
define("DEFAULT_IMAGE_NAME", "noname");

//奇数はDESC，偶数はASC
define("ORDER_CREATED_DESC", 1);
define("ORDER_CREATED_ASC", 2);
define("ORDER_VIEWED_DESC", 3);
define("ORDER_VIEWED_ASC", 4);
