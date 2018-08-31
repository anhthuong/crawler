<?php

//url
//$redirect = "https://magiamgia.com/";
//$redirect = "https://magiamgia.com/ma-giam-gia-lazada/"; // Lazada
$redirect = "http://boich.top/cham-soc-suc-khoe-va-lam-dep/";

$event = getEvents($redirect);
echo "Dates are : <br>" . $event;

//---------Uncomment this to go to the site and check the data-----------
//header("Location: $redirect");
function getEvents($url) {
    //For Guidance
    //https://code.tutsplus.com/tutorials/techniques-for-mastering-curl--net-8470
    //include("simple_html_dom.php");
    //----------Using file_get_contents()-------------
    /* $url='http://www.uniprot.org/';
      //file_get_contents() reads remote webpage content
      $lines_string=file_get_contents($url);
      //output, you can also save it locally on the server
      echo htmlspecialchars($lines_string); */
    //--------------Using PHP/Curl--------------------
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "User-Agent: {Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729)}",
        "Accept-Language: {en-us,en;q=0.5}"
    ));
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $info = curl_getinfo($curl);
    $wrap_content = "/\<li class=\"voucher-item seller-item J_Items \"\ (.*?)>(.*?)\<\/li\>/is";	

    if ($result === FALSE) {
        echo "Error: " . curl_error($curl);
    } else {
        // Connection DB local       
//        $servername = "localhost";
//        $username   = "root";
//        $password   = "";
//        $dbname     = "boich_news";
//        $conn       = new mysqli($servername, $username, $password, $dbname);
//        mysqli_set_charset( $conn, 'utf8');
        
        // Connection DB boich       
        $servername = "45.76.188.251";
        $username   = "admin_boich";
        $password   = "6hhsEwOvLT";
        $dbname     = "admin_boich";
        $conn       = new mysqli($servername, $username, $password, $dbname);
        mysqli_set_charset( $conn, 'utf8');        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // BASE_URL local
        //define('BASE_URL', 'http://localhost/wordpress/boich/');
        
        // BASE_URL boich
        define('BASE_URL', 'http://boich.top/');
        
        // Category LAZADA production
        $categoryLazMall = 78;
        
//        $categoryID = 77; // Dụng cụ vệ sinh
//        $categoryID = 76; // Chăm sóc thú cưng
//        $categoryID = 75; // Giường ngủ & Nhà tắm
//        $categoryID = 74; // Văn phòng phẩm và nghề thủ công
//        $categoryID = 73; // Máy ảnh & Máy quay phim
//        $categoryID = 72; // Bếp & Phòng ăn
//        $categoryID = 71; // Đồ gia dụng
//        $categoryID = 70; // Bách hóa Online
//        $categoryID = 69; // Thể thao
//        $categoryID = 68; // Đồ nội thất & trang trí
//        $categoryID = 67; // Đồng hồ, Mắt kính, Trang sức
//        $categoryID = 66; // Đồ chơi & Trò chơi
//        $categoryID = 65; // Túi xách và Vali túi du lịch
//        $categoryID = 64; // TV, Video, Âm thanh, Game & Thiết bị đeo công nghệ
//        $categoryID = 63; // Công cụ, Đồ thủ công & Ngoài trời
//        $categoryID = 62; // Máy vi tính & Laptop
//        $categoryID = 61; // Điện thoại & Máy tính bảng
//        $categoryID = 57; // Thời trang & Du lịch
//        $categoryID = 60; // Trẻ sơ sinh & Trẻ nhỏ
//        $categoryID = 58; // Chăm sóc sức khỏe & Làm đẹp
        $categoryID = 59; // Ôtô – Xe máy & Thiết bị định vị

        preg_match_all($wrap_content, $result, $matches);
        $content = $matches[0];
        //print_r($content); die('yyy');
        $data = array();

        foreach ($content as $value) {
            // Tieu de
            $title_content = "/\<div class=\"store-title\"\>(.*?)\<\/div\>/is";
            preg_match($title_content, $value, $title_matches);
            $title = @htmlspecialchars($title_matches[1], ENT_QUOTES);
            $data['title'] = $title;
            $title_url = convert_vi_to_en($title);
            $data['title_url'] = $title_url;
            //echo $title_convert;die;
            
            // LazMall 
            $lazMall_content = "/\<div class=\"store-icon\"\>(.*?)\<\/div\>/is";            
            preg_match($lazMall_content, $value, $lazMall_matches);
            
            //$categoryLazMall
            $insertLazMall = '';
            if(!empty($lazMall_matches)){
                $insertLazMall = 1;
            }
            
            
            // link
            $link_content = '/<a class="seller-link" href="(.*?)"/s';
            preg_match($link_content, $value, $link_matches);
            $link = $link_matches[1];
            $data['link'] = $link;
            
            // lay anh
            $img_content = '/(?<!_)src=([\'"])?(.*?)\\1/';
            preg_match($img_content, $value, $img_matches);
            $img = $img_matches[2];
            $img = str_replace('_120x120q80.jpg_.webp', '', $img);
            $data['img'] = $img;
            save_image($img, 'images/'.$categoryID.'/'.$title_url.'.jpg');
            //save_image($img, "D:\www\htdocs\wordpress\boich\wp-content\uploads\2018\08\images\\".$title_url.'.jpg');
            //echo $title . ' ' . $link;die;
            
            
            // lay description
            $des_content = "/\<div class=\"main-desc big\"\>(.*?)\<\/div\>/is";
            preg_match($des_content, $value, $des_matches);
            //print_r($des_matches);die;
            $des = @htmlspecialchars($des_matches[1], ENT_QUOTES);
            $data['des'] = $des;
            //echo $des; die;
            
            // ngay bat dau
            $start_content = "/\<span class=\"start-date\"\>(.*?)\<\/span\>/is";
            preg_match($start_content, $value, $start_matches);
            //print_r($des_matches);die;
            $startDate = $start_matches[1];
            $data['startDate'] = $startDate;
            //echo $startDate; die;
            
            // ngay ket thuc
            $end_content = "/\<span class=\"end-date\"\>(.*?)\<\/span\>/is";
            preg_match($end_content, $value, $end_matches);
            //print_r($des_matches);die;
            $endDate = $end_matches[1];
            $explode = explode('.', $endDate);
            $endDate = $explode[2] . '-' . $explode[1] . '-' . $explode[0];
            $data['endDate'] = $endDate;
            //echo $endDate; die;
            
            // Luot su dung con lai
            $count_content = "/\<span class=\"valid-cnt\"\>(.*?)\<\/span\>/is";
            preg_match($count_content, $value, $count_matches);
            //print_r($des_matches);die;
            $countLuotconlai = $count_matches[1];
            $data['countLuotconlai'] = $countLuotconlai;
            //echo $countLuotconlai; die;
            
            // Code
            $code_content = "/\<span class=\"code\"\>(.*?)\<\/span\>/is";
            preg_match($code_content, $value, $code_matches);
            //print_r($des_matches);die;
            $codeCoupon = $code_matches[1];
            $data['codeCoupon'] = $codeCoupon;
            //echo $codeDate; die;
            //print_r($data);die;
            
            // Lay ID MAX
            $sqlMaxID   = "SELECT ID FROM wp_posts WHERE id=(SELECT max(id) FROM wp_posts)";
            $queryID     = $conn->query($sqlMaxID);
            //print_r($result);die('xxx');
            if ($queryID->num_rows > 0) {
                $result = $queryID->fetch_array();
                $maxID  = $result['ID'] + 1;
                //print_r($result);die;
                // output data of each row
//                while($row = $result->fetch_assoc()) {
//                    print_r($row);die;
//                }
            } else {
                die("Không lấy được ID MAX trong bảng wp_posts");
            }
            
            // Insert counpon
            $insertCoupon = "INSERT INTO
                              `wp_posts`(
                                `ID`,
                                `post_author`,
                                `post_date`,
                                `post_date_gmt`,
                                `post_content`,
                                `post_title`,
                                `post_excerpt`,
                                `post_status`,
                                `comment_status`,
                                `ping_status`,
                                `post_password`,
                                `post_name`,
                                `to_ping`,
                                `pinged`,
                                `post_modified`,
                                `post_modified_gmt`,
                                `post_content_filtered`,
                                `post_parent`,
                                `guid`,
                                `menu_order`,
                                `post_type`,
                                `post_mime_type`,
                                `comment_count`
                              )
                            VALUES(
                              '0',
                              '1',
                              '2018-08-21 09:03:19',
                              '2018-08-21 09:03:19',
                              '',
                              '$title',
                              '',
                              'publish',
                              'closed',
                              'closed',
                              '',
                              '$title_url',
                              '',
                              '',
                              '2018-08-21 09:03:19',
                              '2018-08-21 09:03:19',
                              '',
                              '0',
                              '".BASE_URL."?post_type=wpcd_coupons&#038;p=$maxID',
                              '0',
                              'wpcd_coupons',
                              '',
                              '0'
                            );";
            
            if ($conn->query($insertCoupon) === TRUE) {
                $imgID = $maxID + 1;
                $insertImg = "INSERT INTO
                                      `wp_posts`(
                                        `ID`,
                                        `post_author`,
                                        `post_date`,
                                        `post_date_gmt`,
                                        `post_content`,
                                        `post_title`,
                                        `post_excerpt`,
                                        `post_status`,
                                        `comment_status`,
                                        `ping_status`,
                                        `post_password`,
                                        `post_name`,
                                        `to_ping`,
                                        `pinged`,
                                        `post_modified`,
                                        `post_modified_gmt`,
                                        `post_content_filtered`,
                                        `post_parent`,
                                        `guid`,
                                        `menu_order`,
                                        `post_type`,
                                        `post_mime_type`,
                                        `comment_count`
                                      )
                                    VALUES(
                                      '$imgID',
                                      '1',
                                      '2018-08-21 15:42:11',
                                      '2018-08-21 15:42:11',
                                      '',
                                      '$title_url',
                                      '',
                                      'inherit',
                                      'open',
                                      'closed',
                                      '',
                                      '$title',
                                      '',
                                      '',
                                      '2018-08-21 15:42:11',
                                      '2018-08-21 15:42:11',
                                      '',
                                      '$maxID',
                                      '".BASE_URL."wp-content/uploads/2018/08/$title_url.jpg',
                                      '0',
                                      'attachment',
                                      'image/jpeg',
                                      '0'
                                    );";
                if ($conn->query($insertImg) === TRUE) {
                    // Insert META Coupon
                    $insertMetaCoupon = "INSERT INTO `wp_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES
                                            ($maxID, '_vc_post_settings', 'a:1:{s:10:\"vc_grid_id\";a:0:{}}'),
                                            ($maxID, '_edit_last', '1'),
                                            ($maxID, '_edit_lock', '1534842202:1'),
                                            ($maxID, '_thumbnail_id', '$imgID'),
                                            ($maxID, 'coupon_details_coupon-type', 'Coupon'),
                                            ($maxID, 'coupon_details_coupon-code-text', '$codeCoupon'),
                                            ($maxID, 'coupon_details_second-coupon-code-text', ''),
                                            ($maxID, 'coupon_details_third-coupon-code-text', ''),
                                            ($maxID, 'coupon_details_deal-button-text', ''),
                                            ($maxID, 'coupon_details_link', '$link'),
                                            ($maxID, 'coupon_details_second-link', ''),
                                            ($maxID, 'coupon_details_third-link', ''),
                                            ($maxID, 'coupon_details_discount-text', '$des'),
                                            ($maxID, 'coupon_details_second-discount-text', ''),
                                            ($maxID, 'coupon_details_third-discount-text', ''),
                                            ($maxID, 'coupon_details_description', '<p>$des</p>'),
                                            ($maxID, 'coupon_details_show-expiration', 'Show'),
                                            ($maxID, 'coupon_details_expire-date', '$endDate'),
                                            ($maxID, 'coupon_details_second-expire-date', ''),
                                            ($maxID, 'coupon_details_third-expire-date', ''),
                                            ($maxID, 'coupon_details_expire-time', ''),
                                            ($maxID, 'coupon_details_never-expire-check', '0'),
                                            ($maxID, 'coupon_details_hide-coupon', 'Yes'),
                                            ($maxID, 'coupon_details_template-five-theme', '#18e06e'),
                                            ($maxID, 'coupon_details_template-six-theme', '#18e06e'),
                                            ($maxID, 'coupon_details_coupon-template', 'Template One'),
                                            ($maxID, 'coupon_details_coupon-image-input', ''),
                                            ($maxID, 'coupon_details_coupon-image-print', 'Yes'),
                                            ($maxID, 'coupon_details_coupon-image-width', ''),
                                            ($maxID, 'coupon_details_coupon-image-height', '');";

                    if ($conn->query($insertMetaCoupon) === TRUE) {
                        $inserCategory = "INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES ('$maxID', '$categoryID', '0');";
                        if($insertLazMall != ''){
                            $insertLazMallSql = "INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES ('$maxID', '$categoryLazMall', '0');";
                            $conn->query($insertLazMallSql);
                        }

                        if ($conn->query($inserCategory) === TRUE) {
                            $updateCountCate = "UPDATE `wp_term_taxonomy` SET `count`=count+1  WHERE term_id = $categoryID";
                            if($insertLazMall != ''){
                                $updateCountCateLazMall = "UPDATE `wp_term_taxonomy` SET `count`=count+1  WHERE term_id = $categoryLazMall";
                                $conn->query($updateCountCateLazMall);
                            }

                            if ($conn->query($updateCountCate) === TRUE) {
                                $insertImage = "INSERT INTO `wp_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES ('0', '$maxID', '_wp_attached_file', '2018/08/$title_url.jpg');";
                                
                                if ($conn->query($insertImage) === TRUE) {
                                    
                                }else {
                                    die("Error: " . $insertImage . "<br>" . $conn->error);
                                }
                            }else {
                                die("Error: " . $updateCountCate . "<br>" . $conn->error);
                            }
                        }else {
                            die("Error: " . $inserCategory . "<br>" . $conn->error);
                        }
                    }else {
                        die("Error: " . $insertMetaCoupon . "<br>" . $conn->error);
                    }
                }else {
                    die("Error: " . $insertImg . "<br>" . $conn->error);
                }
            } else {
                die("Error: " . $insertCoupon . "<br>" . $conn->error);
            }
        }
        $conn->close();
    }
    curl_close($curl);
}

function save_image($inPath,$outPath)
{ //Download images from remote server
    $in=    fopen($inPath, "rb");
    $out=   fopen($outPath, "wb");
    while ($chunk = fread($in,8192))
    {
        fwrite($out, $chunk, 8192);
    }
    fclose($in);
    fclose($out);
}

function convert_vi_to_en($str) {
    $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
    $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
    $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
    $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
    $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
    $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
    $str = preg_replace("/(đ)/", 'd', $str);
    $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
    $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
    $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
    $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
    $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
    $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
    $str = preg_replace("/(Đ)/", 'D', $str);
    $str = str_replace(" ", "-", str_replace("&*#39;","",$str));
    
    return clean_special($str);
}

function clean_special($string) {
   //$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}



