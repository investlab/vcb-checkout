<?php

use common\components\utils\Translate;

$this->title = Translate::get('Thống kê sản lượng giao dịch');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class=content-wrapper>
    <div class=row>
        <!-- Start .row -->
        <!-- Start .page-header -->
        <div class="col-lg-12 heading">
            <div id="page-heading" class="heading-fixed">
                <!-- InstanceBeginEditable name="EditRegion1" -->
                <h1 class=page-header><?= Translate::get('Thống kê sản lượng giao dịch') ?></h1>
            </div>
        </div>
        <!-- End .page-header -->
    </div>
    <!-- End .row -->
    <div class=outlet>
        <!-- InstanceBeginEditable name="EditRegion2" -->

        <div class="well well-sm fillter">
            <form class="form-horizontal" role=form>
                <div class="row group-input-search">
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left ui-sortable" id="">
                        <input type="text" class="form-control left-icon"
                               placeholder="<?= Translate::get('TG thống kê') ?>"
                               autocomplete="new-password"
                               name="time_search"
                        <i class="im-calendar s16 left-input-icon"></i>&nbsp;
                        <span></span>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left">
                        <select class="form-control" name="merchant_id" id="merchant_id">
                            <?php
                            foreach ($merchant_search_arr as $key => $data) {
                                ?>
                                <option value="<?= $key ?>">
                                    <?= Translate::get($data) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 no-padding-left group-btn-search mobile-flex-middle-center ui-sortable">
                        <!--<button class="btn btn-danger" type="submit"><?= Translate::get('Tìm kiếm') ?></button>-->
                        <a href="<?= Yii::$app->urlManager->createUrl('dashboard-transaction-amount/index') ?>"
                           class="btn btn-default">
                            <?= Translate::get('Bỏ lọc') ?>
                        </a>
                    </div>
                </div>

            </form>
        </div>
        <div class=row>
            <div class="col-md-8 col-md-offset-2">
                <canvas id="myChart" width="300" height="350"></canvas>
            </div>
        </div>
    </div>
</div>
</div>

<script language="javascript" type="text/javascript">
    var ajax_url = "<?php echo 'dashboard-transaction-amount/index'; ?>";
    //select2
    $('#merchant_id').select2();
    $('#merchant_id').on('change', function() {
        getData();
    });
    //daterangepicker
    var start = moment();
    var end = moment();
    $('input[name="time_search"]').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
          format: 'DD/MM/YYYY'
        }
    }, getData);
    
    //ajax getData
    function getData(start, end) {
        var time_search;
        if (start === undefined && end === undefined) {
            time_search = $('input[name="time_search"]').val();
        } else {
            time_search = start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY');
        }
        $.ajax({
            url: ajax_url,
            type: "post",
            data: {
                time_search : time_search,
                merchant_id : $('#merchant_id').val()
            },
            success: function(data) {
                renderChart(data.data, data.labels);
            }
        });
    }

    //render chart
    function renderChart(data, labels) {
        Chart.helpers.each(Chart.instances, function(instance){
            instance.destroy();
        });
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tổng giá trị giao dịch (VND)',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    maxBarThickness: 50
                }]
            },
            options: {
                tooltips: {
                    mode: 'label',
                    label: 'mylabel',
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' VND'; 
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            suggestedMax : 1000000,
                            fontSize: 14,
                            callback: function(label, index, labels) {
                                return label.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Giá trị GD',
                            fontStyle: 'bold',
                            fontSize: 16,
                            fontColor: '#1aa1dc',
                            padding: 20
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            maxTicksLimit: 8,
                            fontSize: 14,
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Thời gian',
                            fontStyle: 'bold',
                            fontSize: 16,
                            fontColor: '#1aa1dc',
                            padding: 20
                        }
                    }]
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    
    getData();
</script>