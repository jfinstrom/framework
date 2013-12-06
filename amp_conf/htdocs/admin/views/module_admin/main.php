<?php if($online) { ?>
	<?php if(!empty($announcements)) {?>
		<div class='announcements'><?php echo $announcements?>/div>
	<?php } ?>
	<?php if (!EXTERNAL_PACKAGE_MANAGEMENT) {?>
		<?php echo $repo_select?>
		
		<div class="repo_boxes">
			<a class="btn" href="config.php?display=modules&amp;online=0"><?php echo _("Manage local modules")?></a>
			<input id="show_upgradable_only" type="checkbox" name="ugo" onclick="showhide_upgrades();" />
			<label for="show_upgradable_only"><?php echo _("Show only upgradeable")?></label>
		</div>
	<?php } ?>
<?php } else { ?>
	<?php if (!EXTERNAL_PACKAGE_MANAGEMENT) {?>
		<?php echo $repo_select?>
	<?php } else { ?>
		| <a href='config.php?display=modules&amp;extdisplay=upload'><?php echo _("Upload module")?></a><br />
	<?php } ?>
<?php } ?>

<form name="modulesGUI" action="config.php?display=modules" method="post">
	<input type="hidden" name="display" value="modules" />
	<input type="hidden" name="online" value="<?php echo $online?>" />
	<input type="hidden" name="extdisplay" value="confirm" />
	
	<div class="modulebuttons">
		<?php if ($online) { ?>
			<a class="btn" href="#" onclick="check_download_all()"><?php echo _("Download all")?></a>
			<a class="btn" href="#" onclick="check_upgrade_all()"><?php echo _("Upgrade all") ?></a>
		<?php } ?>
		<input type="reset" value="<?php echo _("Reset")?>" />
		<input type="submit" value="<?php echo _("Process")?>" name="process" />
	</div>

	<div id="modulelist">
		<div id="modulelist-header">
			<span class="modulename"><?php echo _("Module")?></span>
			<span class="moduleversion"><?php echo _("Version")?></span>
			<span class="modulepublisher"><?php echo _("Publisher")?></span>
			<span class="clear">&nbsp;</span>
		</div>
		<?php foreach($module_display as $category) {?>
			<div class="category" id="category_<?php echo prep_id($category['name'])?>">
				<h3><?php echo $category['name']?></h3>
				<ul>
					<?php foreach($category['data'] as $module) {?>
						<li id="module_<?php echo prep_id($module['name'])?>" class="repo_<?php echo $module['repo']?>">
							<div class="<?php echo $module['mclass']?>" onclick="toggleInfoPane('infopane_<?php echo prep_id($module['name'])?>')" >
								<span class="modulename"><a href="javascript:void(null)"><?php echo $module['pretty_name']?></a></span>
								<span class="moduleversion"><?php echo $module['dbversion']?></span>
								<span class="modulepublisher"><?php echo $module['publisher']?></span>
								<span class="modulestatus">
									<?php switch ($module['status']) {
										case MODULE_STATUS_NOTINSTALLED: 
											if (!empty($module['raw']['local'])) { ?>
												<span class="notinstalled"><?php echo _('Not Installed (Locally available)')?></span>
											<?php } else { ?>
												<span class="notinstalled"><?php echo sprintf(_('Not Installed (Available online: %s)'), $module['raw']['online']['version'])?></span>
											<?php }
										break;
										case MODULE_STATUS_NEEDUPGRADE:?>
											<span class="alert"><?php echo sprintf(_('Disabled; Pending upgrade to %s'),$module['raw']['local']['version']);?></span>
										<?php break;
										case MODULE_STATUS_BROKEN:?>
											<span class="alert"><?php echo _('Broken');?></span>
										<?php break;
										case MODULE_STATUS_DISABLED:
										default:
											$disabled = ($module['status'] == MODULE_STATUS_DISABLED) ? 'Disabled; ' : '';
											// check for online upgrade
											if (!empty($module['raw']['online']['version'])) { 
												$vercomp = version_compare_freepbx($module['raw']['local']['version'], $module['raw']['online']['version']);
												if ($vercomp < 0) {?>
													<span class="alert"><?php echo sprintf(_($disabled.'Online upgrade available (%s)'), $modules_online[$name]['version']);?></span>
												<?php } elseif ($vercomp > 0) { ?>
													<?php echo sprintf(_($disabled.'Newer than online version (%s)'), $module['raw']['online']['version']);?>
												<?php } elseif($module['status'] == MODULE_STATUS_DISABLED) { ?>
													<?php echo _('Disabled; up to date');?>
												<?php } else { ?>
													<?php echo  _('Enabled and up to date');?>
												<?php } 
											}
											if (!empty($module['raw']['online']) && $module['status'] != MODULE_STATUS_DISABLED) {?>
												// we're connected to online, but didn't find this module
												<?php echo _('Enabled; Not available online');?>
											<?php } elseif($module['status'] == MODULE_STATUS_DISABLED) { ?>
												<?php echo _('Disabled');?>
											<?php } else { ?>
												<?php echo _('Enabled'); ?>
											<?php } 
										break;
									}?>
								</span>
								<?php if ($module['salert']) { ?>
									<span class="modulevul">
										<a class="modulevul_tag" href="#" data-sec='<?php echo json_encode($module['vulnerabilities']['vul'])?>'>
											<img src="images/notify_security.png" alt="" width="16" height="16" border="0" title="<?php echo sprintf(_("Vulnerable to security issues %s"), implode($module['vulnerabilities']['vul'], ', '))?>" />
											<?php echo sprintf(_("Vulnerable, Requires: %s"), $module['vulnerabilities']['minver']) ?>
										</a>
									</span>
								<?php } ?>
								<span class="clear">&nbsp;</span>
							</div>
							<div class="moduleinfopane" id="infopane_<?php echo prep_id($module['name'])?>">
								<div class="tabber">
									<?php if (!empty($module['attention'])) { ?>
										<div class="tabbertab" title="<?php echo _('Attention')?>">
											<?php echo $module['attention']?>
										</div>
									<?php } ?>
									<div class="tabbertab actiontab" title="<?php echo _("Action")?>">
										<input type="radio" checked="CHECKED" id="noaction_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="0" />
										<label for="noaction_<?php echo prep_id($module['name'])?>"><?php echo _('No Action')?></label> <br />
										<?php switch ($module['status']) {
											case MODULE_STATUS_NOTINSTALLED:
												if (!EXTERNAL_PACKAGE_MANAGEMENT) {
													if (isset($module['raw']['local'][$name])) {?>
														<input type="radio" id="install_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="install" />
														<label for="install_<?php echo prep_id($module['name'])?>"><?php echo _('Install')?></label> <br />
													<?php } else { ?>
														<input type="radio" id="upgrade_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="downloadinstall" />
														<label for="upgrade_<?php echo prep_id($module['name'])?>"><?php echo _('Download and Install')?></label> <br />
													<?php } ?>
											<?php } 
											break;
											case MODULE_STATUS_DISABLED:?>
												<input type="radio" id="enable_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="enable" />
												<label for="enable_<?php echo prep_id($module['name'])?>"><?php echo _('Enable') ?></label> <br />
												<?php if (!EXTERNAL_PACKAGE_MANAGEMENT) { ?>
													<input type="radio" id="uninstall_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="uninstall" />
													<label for="uninstall_<?php echo prep_id($module['name'])?>"><?php echo _('Uninstall')?></label> <br />
													<?php if (isset($module['raw']['online']['version'])) { 
														$vercomp = version_compare_freepbx($module['raw']['local']['version'], $module['raw']['online'][$name]['version']);
														if ($vercomp < 0) { ?>
															<input type="radio" id="upgrade_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="upgrade" />
															<label for="upgrade_<?php echo prep_id($module['name'])?>"><?php echo sprintf(_('Download %s, keep Disabled'),$module['raw']['online']['version'])?></label> <br />
													<?php } ?>
												<?php } ?>
											<?php } 
											break;
											case MODULE_STATUS_NEEDUPGRADE:
												if (!EXTERNAL_PACKAGE_MANAGEMENT) {?>
													<input type="radio" id="install_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="install" />
													<label for="install_<?php echo prep_id($module['name'])?>"><?php echo sprintf(_('Upgrade to %s and Enable'),$module['raw']['local']['version'])?></label> <br />
													
													<?php if (isset($module['raw']['online']['version'])) { 
														$vercomp = version_compare_freepbx($module['raw']['local']['version'], $module['raw']['online']['version']);
														if ($vercomp < 0) {?>
															<input type="radio" id="upgrade_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="upgrade" />
															<label for="upgrade_<?php echo prep_id($module['name'])?>"><?php echo sprintf(_('Download and Upgrade to %s'), $module['raw']['online']['version'])?></label> <br />
														<?php } ?>
													<?php } ?>
													<input type="radio" id="uninstall_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="uninstall" />
													<label for="uninstall_<?php echo prep_id($module['name'])?>"><?php echo _('Uninstall')?></label> <br />
												<?php }
											break;
											case MODULE_STATUS_BROKEN:
												if (!EXTERNAL_PACKAGE_MANAGEMENT) { ?>
													<input type="radio" id="install_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="install" />
													<label for="install_<?php echo prep_id($module['name'])?>"><?php _('Install')?></label> <br />
													<input type="radio" id="uninstall_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="uninstall" />
													<label for="uninstall_<?php echo prep_id($module['name'])?>"><?php _('Uninstall')?></label> <br />
												<?php }
											break;
											default:
												// check for online upgrade
												if (isset($module['raw']['online']['version'])) {
													$vercomp = version_compare_freepbx($module['raw']['local']['version'], $module['raw']['online']['version']);
													if (!EXTERNAL_PACKAGE_MANAGEMENT) {
														if ($vercomp < 0) { ?>
															<input type="radio" id="upgrade_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="upgrade" />
															<label for="upgrade_<?php echo prep_id($module['name'])?>"><?php echo sprintf(_('Download and Upgrade to %s'), $module['raw']['online']['version'])?></label> <br />
														<?php } else { 
															$force_msg = ($vercomp == 0 ? sprintf(_('Force Download and Install %s'), $module['raw']['online']['version']) : sprintf(_('Force Download and Downgrade to %s'), $module['raw']['online']['version'])); ?>
															<input type="radio" id="force_upgrade_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="force_upgrade" />
															<label for="force_upgrade_<?php echo prep_id($module['name'])?>"><?php echo $force_msg ?></label> <br />
														<?php }
													}
												}
												if (enable_option($module['name'],'candisable')) { ?>
													<input type="radio" id="disable_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="disable" />
													<label for="disable_<?php echo prep_id($module['name'])?>"><?php echo _('Disable')?></label> <br />
												<?php } 
												if (!EXTERNAL_PACKAGE_MANAGEMENT && enable_option($module['name'],'canuninstall')) {?>
													<input type="radio" id="uninstall_<?php echo prep_id($module['name'])?>" name="moduleaction[<?php echo prep_id($module['name'])?>]" value="uninstall" />
													<label for="uninstall_<?php echo prep_id($module['name'])?>"><?php echo _('Uninstall')?></label> <br />
												<?php } 
											break;
										}?>
									</div>
									<div class="tabbertab" title="<?php echo _("Description")?>">
										<?php if(!empty($module['publisher'])) {?>
											<h5><?php echo sprintf(_("Publisher: %s"),$module['publisher'])?></h5>
										<?php } ?>
										<?php if(!empty($module['license'])) {?>
											<h5><?php echo sprintf(_("License: %s"),$module['license'])?></h5>
										<?php } ?>
										<?php if(!empty($module['salert'])) {?>
											<h5><?php echo sprintf(_("Fixes Vulnerabilities: %s"), implode($module['vulnerabilities']['vul'], ', '))?></h5>
										<?php } ?>
										<?php if(!empty($module['description'])) {?>
											<h5><?php echo sprintf(_("Description for version %s"),$module['version'])?></h5>
											<?php echo nl2br(modgettext::_($module['description'], $loc_domain));?>
										<?php } else { ?>
											<?php echo _("No description is available.") ?>
										<?php } ?>
										<?php if(!empty($module['info'])) {?>
											<p><?php echo _('More info')?>: <a href="<?php echo $module['info'] ?>" target="_new"><?php echo $module['info'] ?></a></p>
										<?php } else { ?>
											<p><?php echo _('More info')?>: <a href="<?php echo $freepbx_help_url?>&amp;freepbx_module=<?php echo urlencode($module['name'])?>" target="help"><?php echo sprintf(_("Get help for %s"),$module['pretty_name'])?></a></p>
										<?php } ?>
									</div>
									<?php if(!empty($module['changelog'])) { ?>
									<div class="tabbertab" title="<?php echo _("Changelog")?>">
										<h5><?php echo sprintf(_("Change Log for version %s"), $module['version'])?></h5>
										<?php echo $module['changelog']?>
									</div>
									<?php } ?>
									<div class="tabbertab" title="<?php echo _("Debug")?>">
										<h5><?php echo $module['name']?></h5>
										<pre>
											<?php print_r($module['raw']['local'])?>
										</pre>
									<?php if ($module['raw']['online']) { ?>
										<h5><?php echo _('Online info')?></h5>
										<pre>
											<?php print_r($module['raw']['online'])?>
										</pre>
									<?php } ?>
										<h5><?php echo _('Combined')?></h5>
										<pre>
											<?php print_r($module)?>
										</pre>
									</div>
								</div>
							</div>
						</li>
					<?php } ?>
				</ul>
			</div>
		<?php } ?>
		<?php echo $end_msg?>
	<div class="modulebuttons">
		<?php if ($online) { ?>
			<a class="btn" href="#" onclick="check_download_all()"><?php echo _("Download all")?></a>
			<a class="btn" href="#" onclick="check_upgrade_all()"><?php echo _("Upgrade all") ?></a>
		<?php } ?>
		<input type="reset" value="<?php echo _("Reset")?>" />
		<input type="submit" value="<?php echo _("Process")?>" name="process" />
	</div>
</form>