<? defined('C5_EXECUTE') or die("Access Denied."); ?>  
<?
if ($controller->getTask() == 'add') {
	$enablePosting = 1;
	$paginate = 1;
}
?>

<fieldset>
	<legend><?=t('Message List')?></legend>
	<div class="control-group">
		<label class="control-label"><?=t('Paginate Message List')?></label>
		<div class="controls">
			<label class="radio">
				<?=$form->radio('paginate', 0, $paginate)?>
				<span><?=t('No, display all messages.')?></span>
			</label>
			<label class="radio">
				<?=$form->radio('paginate', 1, $paginate)?>
				<span><?=t('Yes, display only a sub-set of messages at a time.')?></span>
			</label>
		</div>
	</div>
	<div class="control-group" data-row="itemsPerPage">
		<label class="control-label"><?=t('Messages Per Page')?></label>
		<div class="controls">
			<?=$form->text('itemsPerPage', $itemsPerPage, array('class' => 'span1'))?>
		</div>
	</div>
</fieldset>

<fieldset>
	<legend><?=t('Posting')?></legend>
	<div class="control-group">
		<label class="control-label"><?=t('Enable Posting')?></label>
		<div class="controls">
			<label class="radio">
				<?=$form->radio('enablePosting', 1, $enablePosting)?>
				<span><?=t('Yes, this conversation accepts messages and replies.')?></span>
			</label>
			<label class="radio">
				<?=$form->radio('enablePosting', 0, $enablePosting)?>
				<span><?=t('No, posting is disabled.')?></span>
			</label>
		</div>
	</div>
</fieldset>

<script type="text/javascript">
$(function() {
	$('input[name=paginate]').on('change', function() {
		var pg = $('input[name=paginate]:checked');
		if (pg.val() == 1) {
			$('div[data-row=itemsPerPage]').show();
		} else {
			$('div[data-row=itemsPerPage]').hide();
		}
	}).trigger('change');
});
</script>
