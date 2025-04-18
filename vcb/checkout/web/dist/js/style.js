if (document.getElementById("demo")) {
    // Set the date we're counting down to
    var countDownDate = new Date("Jan 5, 2021 15:37:25").getTime();

    // Update the count down every 1 second
    var x = setInterval(function () {

        // Get today's date and time
        var now = new Date().getTime();

        // Find the distance between now and the count down date
        var distance = countDownDate - now;

        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Output the result in an element with id="demo"
        document.getElementById("demo").innerHTML = minutes + ":" + seconds + " ";

        // If the count down is over, write some text
        if (distance < 0) {
            clearInterval(x);
            document.getElementById("demo").innerHTML = "EXPIRED";
        }
    }, 1000);
}

if (document.getElementById("demos")) {
    // Set the date we're counting down to
    var countDownDate = new Date("Jan 5, 2021 15:37:25").getTime();

    // Update the count down every 1 second
    var x = setInterval(function () {

        // Get today's date and time
        var now = new Date().getTime();

        // Find the distance between now and the count down date
        var distance = countDownDate - now;

        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Output the result in an element with id="demo"
        document.getElementById("demos").innerHTML = minutes + ":" + seconds + " ";

        // If the count down is over, write some text
        if (distance < 0) {
            clearInterval(x);
            document.getElementById("demos").innerHTML = "EXPIRED";
        }
    }, 1000);
}

$(document).ready(function () {
    // var obj = $('.card-number-input').val;
    console.log(456);
    $(".icon-toggle-show").click(function () {
        $(".tab-all").show('slow');
        $(".icon-toggle-show").hide('slow');
        $(".icon-toggle-hide").show('slow');
    });

    $(".icon-toggle-hide").click(function () {
        $(".tab-all").hide('slow');
        $(".icon-toggle-show").show('slow');
        $(".icon-toggle-hide").hide('slow');
    });

    $('#form-checkout').on('submit', function () {
        console.log(123);
        $('.card-number-input').each(function () {
            var value = $(this).val();
            value = value.replace(/\s+/g, ''); // Loại bỏ tất cả dấu cách
            $(this).val(value);
        });
    });

    // Nhập số thẻ auto show lên thẻ (màn REQUEST)
    $('.card-number-input').on('input', function () {
        // Lấy giá trị nhập vào và loại bỏ các ký tự không phải số
        var cardNumber = $(this).val().replace(/\D/g, '');
        // Chia thành nhóm 4 số và nối lại với dấu cách
        var formattedCardNumber = cardNumber.match(/.{1,4}/g)?.join(' ') || cardNumber;
        $(this).val(formattedCardNumber);

        // Thêm các ký tự 'X' nếu số thẻ chưa đủ 16 ký tự
        var cardNumberPadded = cardNumber.padEnd(16, 'x');

        // Cập nhật từng span với các nhóm ký tự tương ứng
        $('.box-card-custom p.card-number span').each(function (index) {
            var chunk = cardNumberPadded.slice(index * 4, (index + 1) * 4);
            $(this).text(chunk);
        });
    });

    $('.card-expired-input').on('input', function () {
        var cardExpired = $('#expMonth').val() + '/' + $('#expYear').val();
        $('.card-date span').text(convertDateFormat(cardExpired));

    })

    // dùng cho atm - TinBT
    $('.card-fullname-input-atm').on('input', function () {
        var cardFullName = $(this).val();
        $('.box-card-custom span.card-fullname').text(cardFullName);
    });

    // dùng cho visa
    $('.card-fullname-input').on('input', function () {
        var cardFullName = $('#card_first_name').val() + ' ' + $('#card_last_name').val();
        console.log(cardFullName);
        $('.box-card-custom span.card-fullname').text(cardFullName);
    });

    $('.otp-input').on('input', function () {
        var $this = $(this);
        var otpValue = '';
        if ($this.val().length === 1) {
            $this.next('.otp-input').focus();
        }

        // Loop through all the OTP digit inputs and concatenate their values
        $('.otp-input-element').each(function () {
            otpValue += $(this).val();
        });

        // Sử dụng biến className từ file PHP
        console.log(123);
        $('input[name="' + className + '[otp]"]').val(otpValue);
    });

    $('.otp-input').on('keydown', function (e) {
        var $this = $(this);
        if (e.key === "Backspace" && !$this.val()) {
            $this.prev('.otp-input').focus();
        }
    });
});

function splitName(fullName) {
    // Loại bỏ khoảng trắng thừa ở đầu và cuối chuỗi
    fullName = fullName.trim();

    // Tách chuỗi theo khoảng trắng
    var nameParts = fullName.split(' ');

    // Last name là phần tử cuối cùng trong mảng
    var lastName = nameParts.pop();

    // First name là phần còn lại của mảng, nối lại thành chuỗi
    var firstName = nameParts.join(' ');

    return {
        firstName: firstName, lastName: lastName
    };
}

function convertDateFormat(dateString) {
    // Tách chuỗi thành tháng và năm
    let [month, year] = dateString.split('/');

    // Nếu tháng chỉ có 1 chữ số, thêm số 0 ở phía trước
    if (month.length === 1) {
        month = '0' + month;
    }

    // Lấy 2 chữ số cuối của năm
    year = year.slice(-2);

    // Trả về chuỗi định dạng mới
    return `${month}/${year}`;
}

