<? defined('C5_EXECUTE') or die("Access Denied."); ?>

<? if ($this->controller->getTask() == 'view_details') { 
	$sv = CollectionVersion::get($stack, 'ACTIVE');
	?>

	<script type="text/javascript">
	
	ccm_stacksAddBlock = function() {
		ccm_openAreaAddBlock("<?=STACKS_AREA_NAME?>", true, <?=$stack->getCollectionID()?>);
	}
	
	ccm_parseBlockResponsePost = function(r) {
		if (r.task != 'update_groups') {
			$(".ccm-main-nav-edit-option").fadeIn(300);
		}
	}
	
	$(function() {
		CCM_EDIT_MODE = true; // override header_required
		ccm_editInit();
		$("#stackPermissions").dialog();
		$("#stackVersions").dialog();
		$("#stackAddClipboard").dialog();
		// Make sure the dropdown menu closes after clicking one of these
		$("#stackAddClipboard").click(function() {
			$(this).parents('.btn-group').removeClass('open');
		});
	});
	
	</script>
	
	<style type="text/css">
	div.ccm-block {border: 2px dotted #efefef; clear: both; overflow: hidden; margin: 0px 0px 4px 0px; padding: 2px}
	div#ccm-stack-status-bar div#ccm-page-status-bar {position: static; height: auto; margin-bottom: 20px;}
	div#ccm-stack-status-bar div#ccm-page-status-bar div.ccm-page-status-bar-buttons {display: block; margin-top: 10px; position: static;}
	</style>
	
	<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper($sv->getVersionName(), false, 'span10 offset1', false)?>
	<div class="ccm-pane-options">
		<div class="btn-group" style="float:left; margin-right: 4px;">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><?=t('Add')?> <span class="caret"></span></a>
			<ul class="dropdown-menu">
				<li><a href="javascript:void(0)" onclick="ccm_stacksAddBlock()"><?=t('Block')?></a></li>
				<li><a dialog-modal="false" dialog-width="550" dialog-height="380" dialog-title="<?=t('Paste From Clipboard')?>" id="stackAddClipboard" href="<?=REL_DIR_FILES_TOOLS_REQUIRED?>/edit_area_popup.php?cID=<?=$stack->getCollectionID()?>&arHandle=<?=STACKS_AREA_NAME?>&atask=paste&addOnly=0"><?=t('Paste From Clipboard')?></a></li>
			</ul>
		</div>
		
		<? $cpc = new Permissions($stack); ?>
		
		<? if ($cpc->canEditPageProperties()) { ?>
			<a class="btn small ccm-main-nav-edit-option"href="<?=$this->action('rename', $stack->getCollectionID())?>"><?=t('Rename')?></a>
		<? } ?>
		
		<a class="btn small ccm-main-nav-edit-option" dialog-width="640" dialog-height="340" id="stackVersions" dialog-title="<?=t('Version History')?>" href="<?=REL_DIR_FILES_TOOLS_REQUIRED?>/versions.php?rel=SITEMAP&cID=<?=$stack->getCollectionID()?>"><?=t('Version History')?></a>

		<? $cpc = new Permissions($stack); ?>
		
		<? if ($cpc->canEditPagePermissions() && PERMISSIONS_MODEL == 'advanced') { ?>
			<a class="btn small ccm-main-nav-edit-option" dialog-width="580" dialog-append-buttons="true" dialog-height="420" dialog-title="<?=t('Stack Permissions')?>" id="stackPermissions" href="<?=REL_DIR_FILES_TOOLS_REQUIRED?>/edit_area_popup.php?cID=<?=$stack->getCollectionID()?>&arHandle=<?=STACKS_AREA_NAME?>&atask=groups"><?=t('Permissions')?></a>
		<? } ?>

		<? if ($cpc->canDeletePage()) { ?>
			<a class="btn ccm-button-v2-right small ccm-main-nav-edit-option error" href="javascript:void(0)" onclick="if (confirm('<?=t('Are you sure you want to remove this stack?')?>')) { window.location.href='<?=$this->url('/dashboard/blocks/stacks/', 'delete', $stack->getCollectionID(), Loader::helper('validation/token')->generate('delete'))?>' }"><?=t('Delete Stack')?></a>
		<? } ?>
		
		<? if ($cpc->canMoveOrCopyPage()) { ?>
			<a class="btn ccm-button-v2-right small ccm-main-nav-edit-option" href="<?=$this->action('duplicate', $stack->getCollectionID())?>" style="margin-right: 4px;"><?=t('Duplicate Stack')?></a>
		<? } ?>
		
		<?
		$hasPendingPageApproval = false;
		$workflowList = PageWorkflowProgress::getList($stack);
		foreach($workflowList as $wl) {
			$wr = $wl->getWorkflowRequestObject(); 
			$wrk = $wr->getWorkflowRequestPermissionKeyObject(); 
			if ($wrk->getPermissionKeyHandle() == 'approve_page_versions') {
				$hasPendingPageApproval = true;
				break;
			}
		}

		if (!$hasPendingPageApproval) { 
			$vo = $stack->getVersionObject();
			if ($cpc->canApprovePageVersions()) {
				$publishTitle = t('Approve Changes');
				$pk = PermissionKey::getByHandle('approve_page_versions');
				$pk->setPermissionObject($stack);
				$pa = $pk->getPermissionAccessObject();
				if (is_object($pa) && count($pa->getWorkflows()) > 0) {
					$publishTitle = t('Submit to Workflow');
				}
			
				$token = '&' . Loader::helper('validation/token')->getParameter(); ?>
				<a style="margin-right: 8px; <? if ($vo->isApproved()) { ?> display: none; <? } ?>" href="javascript:void(0)" onclick="window.location.href='<?=DIR_REL . "/" . DISPATCHER_FILENAME . "?cID=" . $stack->getCollectionID() . "&ctask=approve-recent" . $token?>'" class="btn btn-success small ccm-main-nav-edit-option ccm-button-v2-right"><?=$publishTitle?></a>
			<?
			}		
		}
		?>
	</div>
	<div class="ccm-pane-body ccm-pane-body-footer clearfix" id="ccm-stack-container">
		<?
			if (count($workflowList) > 0) { ?>
			<div id="ccm-stack-status-bar"></div>

			<script type="text/javascript">
			$(function() {

			<? foreach($workflowList as $wl) { ?>
				<? $wr = $wl->getWorkflowRequestObject(); 
				$wrk = $wr->getWorkflowRequestPermissionKeyObject(); 
				if ($wrk->getPermissionKeyHandle() == 'approve_page_versions') {
					$hasPendingPageApproval = true;
				}
				?>
				<? $wf = $wl->getWorkflowObject(); ?>
				sbitem = new ccm_statusBarItem();
				sbitem.setCSSClass('<?=$wr->getWorkflowRequestStyleClass()?>');
				sbitem.setDescription('<?=$wf->getWorkflowProgressCurrentDescription($wl)?>');
				sbitem.setAction('<?=$wl->getWorkflowProgressFormAction()?>');
				sbitem.enableAjaxForm();
				<? $actions = $wl->getWorkflowProgressActions(); ?>
				<? foreach($actions as $act) { ?>
					btn = new ccm_statusBarItemButton();
					btn.setLabel('<?=$act->getWorkflowProgressActionLabel()?>');
					btn.setCSSClass('<?=$act->getWorkflowProgressActionStyleClass()?>');
					btn.setInnerButtonLeftHTML('<?=$act->getWorkflowProgressActionStyleInnerButtonLeftHTML()?>');
					btn.setInnerButtonRightHTML('<?=$act->getWorkflowProgressActionStyleInnerButtonRightHTML()?>');
					<? if ($act->getWorkflowProgressActionURL() != '') { ?>
						btn.setURL('<?=$act->getWorkflowProgressActionURL()?>');
					<? } else { ?>
						btn.setAction('<?=$act->getWorkflowProgressActionTask()?>');
					<? } ?>
					<? if (count($act->getWorkflowProgressActionExtraButtonParameters()) > 0) { ?>
						<? foreach($act->getWorkflowProgressActionExtraButtonParameters() as $key => $value) { ?>
							btn.addAttribute('<?=$key?>', '<?=$value?>');
						<? } ?>
					<? } ?>
					sbitem.addButton(btn);
				<? } ?>
				ccm_statusBar.addItem(sbitem);
			<? } ?>
				ccm_statusBar.activate('ccm-stack-status-bar');
			});
			</script>

			<? }  


		$a = Area::get($stack, STACKS_AREA_NAME);
		$bv = new BlockView();
		$bv->renderElement('block_area_header', array('a' => $a));	
		$bv->renderElement('block_area_header_view', array('a' => $a));	

		foreach($blocks as $b) {
			$bv = new BlockView();
			$bv->setAreaObject($a); 
			$p = new Permissions($b);
			if ($p->canViewBlock()) {
				$bv->renderElement('block_controls', array( 'a' => $a, 'b' => $b, 'p' => $p ));
				$bv->renderElement('block_header', array( 'a' => $a, 'b' => $b, 'p' => $p ));
				$bv->render($b);
				$bv->renderElement('block_footer');
			}
		}
		$bv->renderElement('block_area_footer_view', array('a' => $a));	
		print '</div>'; // instead  of loading block area footer view
	?>	
	</div>
	<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false); ?>

<? } else if ($this->controller->getTask() == 'duplicate') {
	$sv = CollectionVersion::get($stack, 'ACTIVE');
	?>

	<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t("Duplicate %s", $sv->getVersionName()), false, 'span10 offset1', false)?>
	<form name="duplicate_form" action="<?=$this->action('duplicate', $stack->getCollectionID())?>" method="POST">
		<?=Loader::helper("validation/token")->output('duplicate_stack')?>
		<div class="ccm-pane-body ccm-pane-body">
			<div class="clearfix">
				<?=$form->label('stackName', t("Name"))?>
				<div class="input">
					<?=$form->text('stackName')?>
				</div>
			</div>
		</div>
		<div class="ccm-pane-footer">
			<?=$interface->button(t("Cancel"), $this->action('view_details', $stack->getCollectionID()), 'left')?>
			<?=$interface->submit(t("Duplicate Stack"), 'duplicate_form', 'right', 'primary')?>
		</div>
	</form>
	<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);?>

<? } else if ($this->controller->getTask() == 'rename') {
	$sv = CollectionVersion::get($stack, 'ACTIVE');
	?>

	<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t("Rename %s", $sv->getVersionName()), false, 'span10 offset1', false)?>
	<form name="duplicate_form" action="<?=$this->action('rename', $stack->getCollectionID())?>" method="POST">
		<?=Loader::helper("validation/token")->output('rename_stack')?>
		<div class="ccm-pane-body ccm-pane-body">
			<div class="clearfix">
				<?=$form->label('stackName', t("Name"))?>
				<div class="input">
					<?=$form->text('stackName', $stack->getStackName())?>
				</div>
			</div>
		</div>
		<div class="ccm-pane-footer">
			<?=$interface->button(t("Cancel"), $this->action('view_details', $stack->getCollectionID()), 'left')?>
			<?=$interface->submit(t("Rename"), 'duplicate_form', 'right', 'primary')?>
		</div>
	</form>
	<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false);?>

<? } else { ?>

	<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Stacks'), t('Stacks give you a central place to stash blocks, where you can control their order, permissions, and even version them.<br><br>Add stacks to your site and you can update them in one place.'), 'span10 offset1');?>
		
	<h4><?=t('Global Areas')?></h4>
	<div class="ccm-stack-content-wrapper">
	
	<?
	if (count($globalareas) > 0) { 
		foreach($globalareas as $st) {
			$sv = CollectionVersion::get($st, 'ACTIVE');
			?>

			<div class="ccm-stack ccm-group" id="stID_<?=$st->getCollectionID()?>">
				<? if ($canMoveStacks) { ?><img class="ccm-group-sort" src="<?php  echo ASSETS_URL_IMAGES?>/icons/up_down.png" width="14" height="14" /><? } ?>
				<a href="<?=$this->url('/dashboard/blocks/stacks/view_details', $st->getCollectionID())?>"><?=$sv->getVersionName()?></a>
			</div>
		
		<?
		}
	} else {
		print '<p>';
		print t('No global areas created yet.');
		print '</p>';	
	}
	?>
	
	</div>
		
	<h4><?=t('Other Stacks')?></h4>
	<div class="ccm-stack-content-wrapper">
	<?
	if (count($useradded) > 0) { 
		foreach($useradded as $st) { 
			$sv = CollectionVersion::get($st, 'ACTIVE');
			?>

			<div class="ccm-stack ccm-group" id="stID_<?=$st->getCollectionID()?>">
				<? if ($canMoveStacks) { ?><img class="ccm-group-sort" src="<?php  echo ASSETS_URL_IMAGES?>/icons/up_down.png" width="14" height="14" /><? } ?>
				<a href="<?=$this->url('/dashboard/blocks/stacks/view_details', $st->getCollectionID())?>"><?=$sv->getVersionName()?></a>
			</div>
		
		<?
		}
	} else {
		print '<p>';
		print t('No stacks have been added.');
		print '</p>';
	}
	?>
	</div>
		<h3><?=t('Add Stack')?></h3>
		<form method="post" class="form-stacked" style="padding-left: 0px" action="<?=$this->action('add_stack')?>">
		<?=Loader::helper("validation/token")->output('add_stack')?>
		<div class="clearfix">
			<?=Loader::helper("form")->label('stackName', t('Name'))?>
			<div class="input">
				<?=Loader::helper('form')->text('stackName')?>
				<?=Loader::helper("form")->submit('add', t('Add'))?>
		</div>
		</div>
		
		</form>
		
	<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper()?>

<? } ?>