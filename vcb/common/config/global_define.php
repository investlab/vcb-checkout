<?php

namespace common\config;

$GLOBALS['OTP_TRANSACTION_LENGTH'] = 6;
$GLOBALS['OTP_TRANSACTION_MAX_FAIL_NUMBER'] = 3;

/*$GLOBALS['FRONTEND_PAGE'] = array(
    'COMPANY' => 'CÔNG TY CỔ PHẦN XPAY',
    'ADDRESS' => array(
        'HN' => 'Tầng 3, Tòa nhà VTC Online, 18 Tam Trinh, Quận Hai Bà Trưng, Hà Nội',
        'HCM' => 'Lầu 6, Toà nhà Sumikura, 18H Cộng Hoà, Phường 4, Quận Tân Bình, TP.HCM'
    ),
    'HOTLINE' => '1900.636.066',
    'PHONE' => '1900.636.066',
    'EMAIL' => 'support@xpay.com',
    'COPYRIGHT' => '@2016 All Right Reserved by XPay',
);*/
$GLOBALS['FRONTEND_PAGE'] = array(
    'COMPANY' => '',
    'ADDRESS' => array(
        'HN' => '',
        'HCM' => ''
    ),
    'HOTLINE' => '',
    'PHONE' => '',
    'EMAIL' => '',
    'COPYRIGHT' => '@2016 All Right Reserved by XPay',
);

$GLOBALS['VERIFY_IMAGE_MAX_LENGTH'] = 3;
$GLOBALS['PAGE_SIZE'] = 15;
$GLOBALS['SESSION_LOGIN_TIMEOUT'] = 3600;//30 phut
$GLOBALS['SIMPLE_PASSWORD_CODE'] = array();
$GLOBALS['EXCEL_TOTAL_ITEM'] = 100000;
$GLOBALS['TOTAL_ITEM_SHEET'] = 50000;

require_once 'notCsrfValidation.php';

$GLOBALS['CURRENCY'] = array(
    'VND' => 'VND',
    'USD' => 'USD',
);

$GLOBALS['CYCLE_DAYS'] = array(
    '1' => '1 ngày',
    '7' => '7 ngày',
    '15' => '15 ngày',
    '30' => '30 ngày',
);

$GLOBALS['ENVIROMENTS'] = array(
    'version_1_0' => 'version_1_0',
    'backend' => 'backend',
    'card-token' => 'card-token',
);

$GLOBALS['REFUND_TYPE'] = array(
    'TOTAL' => 1,
    'PARTIAL' => 2
);
$GLOBALS['REFUND_STATUS'] = array(
    'SUCCESS' => 1,
    'FAIL' => 2,
    'WAIT' => 3
);

$GLOBALS['PREFIX'] = 'VCB_PAYGATE_';

$GLOBALS['exclude_reciver_id_cbs'] = array(
    574558, //kieuhoi@nganluong.vn
    738650, //kithuat@ippay.vn - KH cua QuanDH - 25-09-2018
    //710172, //payments-vn@pouchnation.com - KH cua Thuan Seven trong SG
    767222, //pay@house3d.net - KH cua QuanDH - 16-11-2018
);

$GLOBALS['USERS_PRODUCTION_CODE_DEFAULT'] = 'default';

$GLOBALS['USERS_PRODUCTION_ORDER_CODE'] = array(
    'electronic_good' => 'NEG',
    'shipping_and_handling' => 'NSH',
    'electronic_software' => 'NES',
    'coupon' => 'NCP',
    'default' => '',
);
$GLOBALS['AES_KEY'] = 'ypjyb2zIRLZKlXmTauILPbZvw0tL7ltG';
$GLOBALS['SHA256_KEY'] = 'l0gDzw2i8NU9EGDst1DPsoZSpSjVgAST';

$GLOBALS['MERCHANT_ON_SEAMLESS'] = [
    7
    , 41// UAT of VCB
];

$GLOBALS['MERCHANT_TIME_IN_TIMESTAMP'] = [
//        7,130 // test
    91
];

$GLOBALS['MERCHANT_XNC'] = [
//    7,
    91// UAT of VCB
];
$GLOBALS['MERCHANT_BCA'] = [
    168, 442, 325, 443, 444, 445, 446, 447, 448, 449, 450, 451, 452, 453, 454, 455, 456, 459, 460, 461, 462, 463, 464, 465, 466, 467, 468, 469, 470, 471, 472, 473, 474, 475, 476, 477, 478, 479, 480, 481, 482, 483, 484, 485, 486, 487, 488, 489, 490, 491, 492, 493, 494, 495, 496, 497, 498, 499, 500, 501, 502, 505, 503, 504, 506
];
$GLOBALS['MERCHANT_CLICK_TO_ACCEPT'] = [
//    7,
    91, 168, 442, 325, 443, 444, 445, 446, 447, 448, 449, 450, 451, 452, 453, 454, 455, 456, 459, 460, 461, 462, 463, 464, 465, 466, 467, 468, 469, 470, 471, 472, 473, 474, 475, 476, 477, 478, 479, 480, 481, 482, 483, 484, 485, 486, 487, 488, 489, 490, 491, 492, 493, 494, 495, 496, 497, 498, 499, 500, 501, 502, 505, 503, 504, 506
];

// CẤU HÌNH LẠI TRÊN LIVE
$GLOBALS['MERCHANT_CLICK_TO_ACCEPT_V2'] = [
//    7,
    91, 168, 442, 325, 443, 444, 445, 446, 447, 448, 449, 450, 451, 452, 453, 454, 455, 456, 459, 460, 461, 462, 463, 464, 465, 466, 467, 468, 469, 470, 471, 472, 473, 474, 475, 476, 477, 478, 479, 480, 481, 482, 483, 484, 485, 486, 487, 488, 489, 490, 491, 492, 493, 494, 495, 496, 497, 498, 499, 500, 501, 502, 505, 503, 504, 506
];
$GLOBALS['MERCHANT_BCA_NOTI'] = [
//    7,
    168, 442, 325, 443, 444, 445, 446, 447, 448, 449, 450, 451, 452, 453, 454, 455, 456, 459, 460, 461, 462, 463, 464, 465, 466, 467, 468, 469, 470, 471, 472, 473, 474, 475, 476, 477, 478, 479, 480, 481, 482, 483, 484, 485, 486, 487, 488, 489, 490, 491, 492, 493, 494, 495, 496, 497, 498, 499, 500, 501, 502, 505, 503, 504, 506

];

$GLOBALS['MERCHANT_EMAIL_TEMPLATE_NEW'] = [
    //test
//    7,
    168, 442, 325, 443, 444, 445, 446, 447, 448, 449, 450, 451, 452, 453, 454, 455, 456, 459, 460, 461, 462, 463, 464, 465, 466, 467, 468, 469, 470, 471, 472, 473, 474, 475, 476, 477, 478, 479, 480, 481, 482, 483, 484, 485, 486, 487, 488, 489, 490, 491, 492, 493, 494, 495, 496, 497, 498, 499, 500, 501, 502, 505, 503, 504, 506
];

$GLOBALS['05300003018'] = [
    'MID' => 'ctcpbvdkhoanmysaigon',
    'OrgUnitId' => '5fbb41d17afb3643671327b5',
    'ApiIdentifier' => '606332580adced0d74b7fc25',
    'ApiKey' => 'bd235301-a07b-4e69-bf37-682d1719d8f7',
];
//test
$GLOBALS['00100022607'] = [
    'MID' => 'ctcpbvdkhoanmysaigon',
    'OrgUnitId' => '5fbb41d17afb3643671327b5',
    'ApiIdentifier' => '606332580adced0d74b7fc25',
    'ApiKey' => 'bd235301-a07b-4e69-bf37-682d1719d8f7',
//    'MID' => 'Nganluongjsc',
//    'OrgUnitId' => '5ed9fbceb0268436cad02493',
//    'ApiIdentifier' => '60be11fd8d8e5e1faf97719c',
//    'ApiKey' => 'f4fd8e83-30c8-43f9-9937-3814d8972e31',
];
$GLOBALS['06800002278'] = [
    'MID' => 'ctcpbvdkhoanmysaigon',
    'OrgUnitId' => '5fbb41d17afb3643671327b5',
    'ApiIdentifier' => '606332580adced0d74b7fc25',
    'ApiKey' => 'bd235301-a07b-4e69-bf37-682d1719d8f7',
//    'MID' => 'Nganluongjsc',
//    'OrgUnitId' => '5ed9fbceb0268436cad02493',
//    'ApiIdentifier' => '60be11fd8d8e5e1faf97719c',
//    'ApiKey' => 'f4fd8e83-30c8-43f9-9937-3814d8972e31',
];
$GLOBALS['ENCRYPT_KEY'] = 'lqbkeaf6959f6a77919b24f844f616af6e37e2012b82f9e90daf1f2049271203b070d';
$GLOBALS['LINK_FIX'] = 'https://vietcombank.nganluong.vn';
$GLOBALS['MERCHANT_QNI'] = [119, 1129, 1130];
$GLOBALS['MERCHANT_DONGNAI'] = [50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 65, 67, 68, 171];
$GLOBALS['MERCHANT_FUBON'] = [1387];
$GLOBALS['MERCHANT_BUUDIEN'] = [205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 949, 1263, 1431, 1432, 2345, 2346, 2353, 3315, 3316, 3317, 3461];
$GLOBALS['MERCHANT_XANHPON'] = [176, 154, 178, 179, 180];
$GLOBALS['MERCHANT_XANHPON_NOT_NHA_THUOC'] = [176, 154, 178, 179];
//$GLOBALS['MERCHANT_XANHPON_NOT_NHA_THUOC'] = [176, 154, 178];
$GLOBALS['MERCHANT_VHC'] = [3701,3702,3703,3704,3705,3706,3707,3711,3712,3713,3724,3733,3734,3735,3736,3737,3785,3786];
$GLOBALS['MERCHANT_VHC_QR'] = [7, 3724,3733,3734,3735,3736,3737];


$GLOBALS['BCA_ALL_CITIES'] = [

    7 => ['area' => 'Phòng Quản lý XNC Test', 'address' => '74 Trâu Quỳ, Gia Lâm, Hà Nội', 'phone_number' => '0243.8260922'],
    442 => ['area' => 'Phòng Quản lý XNC - Công an TP Hà Nội', 'address' => '44 Phạm Ngọc Thạch, Đống Đa, Hà Nội', 'phone_number' => '0692.191515 - 0692.197051 (Trong giờ HC) hoặc 0692.1971506 (Sau 17h hằng ngày)'],
    443 => ['area' => 'Phòng Quản lý XNC - Công an TP Hải Phòng', 'address' => 'Số 2 đường Trần Bình Trọng, Phường Lương Khánh Thiện, Quận Ngô Quyền, Thành phố Hải Phòng', 'phone_number' => '0692.785468 - 0225.3921343'],
    168 => ['area' => 'Cục Quản lý XNC tại Hà Nội', 'address' => '44 Trần Phú, Ba Đình, Hà Nội', 'phone_number' => '0243.8260922'],
    325 => ['area' => ' Cục Quản lý XNC tại Thành phố HCM', 'address' => '333-335-337 Nguyễn Trãi,Phường Nguyễn Cư Trinh, Quận 1, TP. Hồ Chí Minh', 'phone_number' => '0283.8386425'],
    447 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh An Giang', 'address' => '51-53 Lý Tự Trọng, phường Mỹ Long, thành phố Long Xuyên, tỉnh An Giang', 'phone_number' => '0693.640255'],
    450 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bắc Kạn', 'address' => 'Thôn Nà Nàng, xã Nông Thượng, TP. Bắc Kạn, tỉnh Bắc Kạn', 'phone_number' => '0692.549123'],
    454 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bình Định', 'address' => '31A Hà Huy Tập, Trần Phú, TP. Quy Nhơn, tỉnh Bình Định', 'phone_number' => '0694.349346'],
    449 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bắc Giang', 'address' => 'Số 2 Hoàng Quốc Việt, Phường Xương Giang, Thành phố Bắc Giang, Bắc Giang', 'phone_number' => '0204.3551987'],
    457 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bình Thuận', 'address' => '139 Mậu Thân, thành phố Phan Thiết, tỉnh Bình Thuận', 'phone_number' => '0252.3428156'],
    459 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bình Thuận', 'address' => '139 Mậu Thân, thành phố Phan Thiết, tỉnh Bình Thuận', 'phone_number' => '0252.3428156'],
    451 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bạc Liêu', 'address' => '81 Lê Duẩn, khóm 7, phường 1, Tp Bạc Liêu', 'phone_number' => '0693.788304'],
    452 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bắc Ninh', 'address' => '14 Lý Thái Tổ, phường Suối Hoa, TP Bắc Ninh, tỉnh Bắc Ninh', 'phone_number' => '0692.609339'],
    456 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bình Phước', 'address' => 'Số 12 Trần Hưng Đạo, Khu phố Phú Cường, Phường Tân Phú, Thành phố Đồng Xoài, tỉnh Bình Phước', 'phone_number' => '0693.467200'],
    453 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bến Tre', 'address' => '404D Đồng Văn Cống, phường 7, thành phố Bến Tre, tỉnh Bến Tre', 'phone_number' => '0693.561033'],
    455 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bình Dương', 'address' => '17 đường N3, phường Chánh Nghĩa, TP Thủ Dầu Một, Bình Dương', 'phone_number' => '0274.3891269'],
    461 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Cao Bằng', 'address' => '54 Kim Đồng, Phường Hợp Giang, TP. Cao Bằng , Tỉnh Cao Bằng', 'phone_number' => '0206.3852940'],
    460 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Cà Mau', 'address' => '66 Nguyễn Hữu Lễ Phường 2, Thành phố Cà Mau', 'phone_number' => '0290.3831877'],
    445 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Cần Thơ', 'address' => '9B Trần Phú, Phường Cái Khế - Quận Ninh Kiều - TP.Cần Thơ', 'phone_number' => '0693.672130'],
    465 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Đồng Nai', 'address' => '161, Đường Phạm Văn Thuận, P Tân Tiến, TP Biên Hòa, tỉnh Đồng Nai.', 'phone_number' => '0693.480874'],
    464 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Điện Biên', 'address' => '402A, Tổ 7, P. Thanh Bình, TP. Điện Biên Phủ, Tỉnh Điện Biên', 'phone_number' => '0215.3827240'],
    462 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Đắk Lắk', 'address' => '01 Lý Thái Tổ, P.Tân Lợi, TP. Buôn Ma Thuột, Đắk Lắk', 'phone_number' => '0262.3955330'],
    463 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Đắk Nông', 'address' => 'Đường 23 tháng 3, TP. Gia Nghĩa, tỉnh Đắk Nông', 'phone_number' => '0261.3545219'],
    467 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Đồng Tháp', 'address' => '16 Võ Trường Toản, Phường 1, TP. Cao Lãnh, Đồng Tháp', 'phone_number' => '0277.3851390'],
    466 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Gia Lai', 'address' => '267A Trần Phú, Tp Pleiku, Gia Lai', 'phone_number' => '0269.3617555'],
    469 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Hà Nam', 'address' => 'Đường Trần Nhật Duật, phường Lê Hồng Phong, TP. Phủ Lý, Hà Nam', 'phone_number' => '0692.729358'],
    473 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Hòa Bình', 'address' => 'Phường Phương Lâm, thành phố Hòa Bình, tỉnh Hòa Bình', 'phone_number' => '0692.709599 - 0692.709110'],
    471 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Hải Dương', 'address' => '132 Nguyễn Trãi, Thành phố Hải Dương, Hải Dương', 'phone_number' => '0220.3857736'],
    468 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Hà Giang', 'address' => 'Số 36, đường 19-5, phường Nguyễn Trãi, thành phố Hà Giang, tỉnh Hà Giang', 'phone_number' => '0868.405.972'],
    470 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Hà Tĩnh', 'address' => 'Ngõ 1, đường Phan Đình Phùng, TP. Hà Tĩnh, tỉnh Hà Tĩnh', 'phone_number' => '0692.928672'],
    472 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Hậu Giang', 'address' => 'Khu vực 2, Phường 5, TP Vị Thanh, Hậu Giang', 'phone_number' => '0693.769172'],
    474 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Hưng Yên', 'address' => 'Số 45 đường Hải Thượng Lãn Ông, thành phố Hưng Yên, tỉnh Hưng Yên', 'phone_number' => '0692.849305'],
    476 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Kiên Giang', 'address' => '120 đường Nguyễn Hùng Sơn, phường Vĩnh Thanh Vân, thành phố Rạch Giá, tỉnh Kiên Giang', 'phone_number' => '0297.3862363'],
    475 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Khánh Hòa', 'address' => '47 Lý Tự Trọng, Phường Lộc Thọ, thành phố Nha Trang, tỉnh Khánh Hòa', 'phone_number' => '0694.401249'],
    477 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Kon Tum', 'address' => 'Số 198 Phan Chu Trinh, TP. Kon Tum, tỉnh Kon Tum', 'phone_number' => '0694.181131'],
    482 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Long An', 'address' => '16 Quốc lộ 1, Phường 5, thành phố Tân An, tỉnh Long An', 'phone_number' => '0272.3827622'],
    481 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Lào Cai', 'address' => '002 Đường Hoàng Sào, phường Duyên Hải, thành phố Lào Cai, tỉnh Lào Cai', 'phone_number' => '0214.3868865'],
    479 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Lâm Đồng', 'address' => '19 Trần Phú, Phường 3, TP. Đà Lạt, tỉnh Lâm Đồng', 'phone_number' => '0693.449058'],
    478 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Lai Châu', 'address' => '256 Trần Hưng Đạo, Phường Đoàn Kết, TP Lai Châu, Tỉnh Lai Châu', 'phone_number' => '0213.3877047'],
    480 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Lạng Sơn', 'address' => '12 Hoàng Văn Thụ, phường Chi Lăng, TP.Lạng Sơn, tỉnh Lạng Sơn', 'phone_number' => '0692.569047'],
    484 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Nghệ An', 'address' => 'Ðuờng Trần Huy Liệu, Khối 4, TP. Vinh, Nghệ An', 'phone_number' => '0692.907207'],
    485 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Ninh Bình', 'address' => 'Đường Đinh Tất Miễn, phường Đông Thành, TP. Ninh Bình, tỉnh Ninh Bình', 'phone_number' => '0692.860558'],
    483 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Nam Định', 'address' => '117 Hoàng Hoa Thám , TP Nam Định', 'phone_number' => '0692.741272'],
    486 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Ninh Thuận', 'address' => '287 đường 21/8, phường Phước Mỹ, thành phố Phan Rang – Tháp Chàm', 'phone_number' => '0259.3668258'],
    487 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Phú Thọ', 'address' => 'Số 51 đường Trần Phú, phường Tân Dân, TP Việt Trì, tỉnh Phú Thọ', 'phone_number' => '0692.645166'],
    504 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Phú Yên', 'address' => '248 Trần Hưng Đạo, phường 4, TP. Tuy Hòa, tỉnh Phú Yên', 'phone_number' => '0257.3824077'],
    489 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Quảng Nam', 'address' => '483 Hai Bà Trưng, phường Tân An, TP. Hội An, tỉnh Quảng Nam', 'phone_number' => '0235.3910093'],
    488 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Quảng Bình', 'address' => '90 đường Trần Hưng Đạo, phường Đồng Phú, TP. Đồng Hới, tỉnh Quảng Bình', 'phone_number' => '0232.3819005 - 0703.293.366'],
    490 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Quảng Ngãi', 'address' => '200 - Trường Chinh, thành phố Quảng Ngãi, tỉnh Quảng Ngãi', 'phone_number' => '0255.3823.575 - 0255.3731.777. Fax: 0255.3823.350'],
    492 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Quảng Ninh', 'address' => 'Trung tâm hành chính công tỉnh- P. Hồng Hà, Hạ Long, Quảng Ninh', 'phone_number' => '0888.293.293'],
    491 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Quảng Trị', 'address' => 'Số 19 Lê Lợi, Phường 5, TP Đông Hà, Quảng Trị', 'phone_number' => '0233.3851675'],
    494 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Sơn La', 'address' => 'Số 53, đường Tô Hiệu, Thành phố Sơn La, tỉnh Sơn La', 'phone_number' => '0692.680377'],
    493 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Sóc Trăng', 'address' => 'Số 1 đường Nguyễn Đình Chiểu, Phường 4, TP. Sóc Trăng, tỉnh Sóc Trăng', 'phone_number' => '0299.3822969'],
    497 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Thái Bình', 'address' => 'Đường Lê Quý Đôn kéo dài - Phường Trần Lãm – Thành phố Thái Bình – Tỉnh Thái Bình', 'phone_number' => '0692.760333'],
    499 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Tiền Giang', 'address' => '152 Đinh Bộ Lĩnh, Phường 9, Thành phố Mỹ Tho, Tiền Giang', 'phone_number' => '0693.599462'],
    446 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Thanh Hóa', 'address' => '01 Đỗ Huy Cư, phường Đông Hải, TP Thanh Hóa', 'phone_number' => '0373.852.697'],
    495 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Tây Ninh', 'address' => 'Số 227, Đường CMT8, KP3, Phường 1, TP. Tây Ninh, Tỉnh Tây Ninh', 'phone_number' => '0693.531257'],
    496 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Thái Nguyên', 'address' => '238/1, đường Bắc Kạn, Tổ 3, phường Hoàng Văn Thụ, TP. Thái Nguyên, Thái Nguyên', 'phone_number' => '0692.669166'],
    501 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Tuyên Quang', 'address' => 'Đường 17/8, Tổ 3, Phường Phan Thiết, Tp Tuyên Quang, tỉnh Tuyên Quang', 'phone_number' => '0207.3816227'],
    500 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Trà Vinh', 'address' => '29 Võ Nguyên Giáp, Khóm 6, P7, Tp.Trà Vinh', 'phone_number' => '0693.729014'],
    502 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Vĩnh Long', 'address' => '71/22A, Phó Cơ Điều, Phường 3, TP.Vĩnh Long', 'phone_number' => '0693.706348'],
    505 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Vĩnh Phúc', 'address' => 'Thôn Vị Trù - xã Thanh Trù - Thành Phố Vĩnh Yên - Vĩnh Phúc', 'phone_number' => '0692.621230 - 0692.621072'],
    503 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Yên Bái', 'address' => 'Tổ 6 phường Yên Ninh, thành phố Yên Bái, tỉnh Yên Bái', 'phone_number' => '0692.509668'],
    506 => ['area' => 'Phòng Quản lý XNC - Công an TP HCM', 'address' => 'Số 196 Nguyễn Thị Minh Khai, Phường 6, Quận 3, TP. Hồ Chí Minh', 'phone_number' => '0283.9333316'],
    448 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Bà Rịa - Vũng Tàu', 'address' => '15 đường Trường Chinh, P.Phước Trung, Thị xã Bà Rịa, Tỉnh Bà Rịa – Vũng Tàu', 'phone_number' => '0693545424 – 0693545181. Fax: 0643852423'],
    444 => ['area' => 'Phòng Quản lý XNC - Công an thành phố Đà Nẵng', 'address' => '78 Lê Lợi, thành phố Đà Nẵng', 'phone_number' => '0694.260193 - 0918.529.007'],
    498 => ['area' => 'Phòng Quản lý XNC - Công an tỉnh Thừa Thiên Huế', 'address' => '50 Trần Cao Vân, phường Phú Hội, thành phố Huế', 'phone_number' => '0234.3933888'],
];