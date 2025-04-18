<div class="col-sm-10 col-sm-offset-1">
        <div class="panel panel-magenta" style="border: 1px solid gainsboro;">
            <div class="panel-heading text-center">
                <h3>Tool update send Mail</h3>
            </div>
            <div class="panel-body">
                <form method="post" class="form-horizontal" action="<?= $url ?>">
                        <div class="row text-center">
                            <i>Điền dãy token-code vào ô trống</i>
                        </div>
                        <br>

                        <div class="row">
                            <label class="col-sm-3 control-label">Token code:</label>

                            <div class="col-sm-7">
                                <input type="text" class="form-control" name="token_code" value="<?= isset($_POST['token_code'])?$_POST['token_code']:'' ?>"
    />
                            </div>
                        </div>

                        <hr/>
                        <div class="row" style="margin-bottom: 15px">
                            <div class="col-sm-7 col-sm-offset-3">
                                <button class="btn btn-primary" type="submit">Send order</button>
                            </div>
                            <p >Success: <?= json_encode($arr_success) ?></p>
                            <p >Fail: <?= json_encode($arr_fail) ?></p>
                        </div>
                </form>
            </div>
        </div>
    </div>