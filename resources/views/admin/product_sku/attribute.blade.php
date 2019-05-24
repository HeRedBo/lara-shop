<?php if ($data): ?>
    <?php foreach ($data as $k => $v): ?>
        <div class="form-group myshow">
        <label for="price" class="col-sm-2  control-label">
            <?php echo $v['name'] ?>
            <i style="color:red;"> *</i>
        </label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                <input type="text" name="attributes[<?php echo $v['id'] ?>]" value="" data-id="<?php echo $v['id'] ?>" class="form-control product_attributes" placeholder="输入<?php echo $v['name'] ?>">
            </div>
        </div>
</div>
    <?php endforeach ?>
<?php endif ?>
