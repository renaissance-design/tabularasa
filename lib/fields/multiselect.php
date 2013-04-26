<select multiple="multiple" name="<?php echo $id?>[]" id="<?php echo $id?>" style="height: auto;">
	<?php foreach ($options as $opt_value=>$opt_name): ?>
	    <?php if(in_array($opt_value, $value)): ?>
		<option selected="selected" value="<?php echo $opt_value?>"><?php echo $opt_name?></option>
	    <?php else: ?>
		<option value="<?php echo $opt_value?>"><?php echo $opt_name?></option>
	    <?php endif; ?>
	<?php endforeach ?>
</select>