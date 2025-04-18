<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use common\components\utils\Translate;

$this->title = Translate::get('Thống kê số lượng giao dịch');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Dashbroad thống kê số lượng giao dịch') ?></h1>
                <!-- Start .option-buttons -->
                <div class="option-buttons">

                </div>
                <!-- InstanceEndEditable -->
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <div class="well well-sm fillter">
            <form class="form-horizontal" role="form" id="form-trans-search">
                <div class="row group-input-search" style="padding: 5px;">
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable" id="">
                        <input type="text" class="form-control left-icon"
                               placeholder="<?= Translate::get('TG thống kê') ?>"
                               autocomplete="new-password"
                               name="time_search"
                               value="">
                        <i class="im-calendar s16 left-input-icon"></i><span></span>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable" id="">
                        <select id="merchant_id" class="form-control" name="merchant_id">
                            <?php foreach($merchants as $key => $merchant) {?>
                            <option value="<?= $key ?>"> <?= $merchant?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center ui-sortable">
                        <button class="btn btn-danger" type="button" onclick="searchTransactionDashboard('<?= Yii::$app->urlManager->createUrl('dashboard-transaction/get-data-chart-transaction') ?>')">
                            <?= Translate::get('Tìm kiếm') ?></button>
                        <a href="<?= Yii::$app->urlManager->createUrl('dashboard-transaction/index') ?>"
                           class="btn btn-default">
                            <?= Translate::get('Bỏ lọc') ?>
                        </a>
                    </div>
                </div>

            </form>
        </div>
        <div class="row display-flex-center">
            <div class="col-xs-12 col-sm-12 col-md-11" id="tab-chart">
                <canvas id="chart-trans" width="100%" height="650"></canvas>
            </div>
        </div>
    </div>
</div>

<script !src="">
    drawChart(<?= json_encode($data_search['time']) ?>, <?= json_encode($data_search['dataset']) ?>, <?= json_encode($data_search['label_arr']) ?>);

    var start = moment().subtract(6,'d');
    var end = moment();
    $('input[name="time_search"]').daterangepicker({
        startDate: start,
        endDate: end,
        locale: {
            format: 'DD/MM/YYYY'
        },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    function searchTransactionDashboard(url_search) {
        var start_time = $('input[name="time_search"]').data('daterangepicker').startDate;
        var end_time = $('input[name="time_search"]').data('daterangepicker').endDate;
        var merchant_id = $('#merchant_id').val();

        $.ajax({
            type: 'POST',
            url: url_search,
            data: {
                start_time: start_time.format('H:m:s DD-MM-YYYY'),
                end_time: end_time.format('H:m:s DD-MM-YYYY'),
                merchant_id: merchant_id
            }, success: function (res) {
                var data = JSON.parse(res);
                resetChart();
                drawChart(data.time, data.dataset, data.label_arr);
            }
        });
    }

    function resetChart() {
        $('#chart-trans').remove(); // this is my <canvas> element
        $('#tab-chart').append('<canvas id="chart-trans" width="100%" height="650"></canvas>');
    }

    function drawChart(time = null, dataset = null, label_arr = null) {
        var ctx = document.getElementById('chart-trans').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: label_arr,
                datasets: dataset,
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        stacked: true,
                        ticks: {
                            beginAtZero: true
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Số lượng GD',
                            fontStyle: 'bold',
                            fontSize: 14
                        }
                    }],
                    xAxes: [{
                        stacked: true
                    }]
                },
                tooltips: {
                    mode: 'nearest'
                },
                title: {
                    display: true,
                    text: time,
                    fontSize: 20,
                    fontStyle: 'bold',
                    fontColor: 'gray',
                }
            },
        });
    }

</script>